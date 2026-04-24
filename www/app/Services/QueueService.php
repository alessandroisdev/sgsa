<?php

namespace App\Services;

use App\Models\Counter;
use App\Models\Priority;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\Totem;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class QueueService
{
    /**
     * Generate a new ticket for a specific service and priority.
     */
    public function generateTicket(Service $service, Priority $priority, Totem $totem): Ticket
    {
        $date = Carbon::today()->format('Y-m-d');
        $redisKey = "ticket_seq:service_{$service->id}:date_{$date}";
        
        $number = Redis::incr($redisKey);
        
        // Ensure expiration so we don't leak memory (e.g., expire in 2 days)
        if ($number === 1) {
            Redis::expire($redisKey, 60 * 60 * 48);
        }

        $formattedNumber = sprintf("%s-%04d", $service->prefix, $number);

        return Ticket::create([
            'service_id' => $service->id,
            'priority_id' => $priority->id,
            'totem_id' => $totem->id,
            'number' => $number,
            'formatted_number' => $formattedNumber,
            'status' => 'pending',
            'absence_count' => 0,
        ]);
    }

    /**
     * Call the next ticket for a counter based on fair queuing weights and atomic locks.
     */
    public function callNextTicket(Counter $counter, User $attendant): ?Ticket
    {
        $lockKey = "call_next_area_{$counter->area_id}";
        $lock = Cache::lock($lockKey, 5); // 5 seconds lock

        if ($lock->get()) {
            try {
                return DB::transaction(function () use ($counter, $attendant) {
                    $nextTicket = $this->determineNextTicket($counter->area_id);

                    if ($nextTicket) {
                        $nextTicket->status = 'called';
                        $nextTicket->counter_id = $counter->id;
                        $nextTicket->user_id = $attendant->id;
                        $nextTicket->called_at = now();
                        $nextTicket->save();

                        // Fire SSE Event Here (TODO)
                        
                        return $nextTicket;
                    }

                    return null;
                });
            } finally {
                $lock->release();
            }
        }

        return null;
    }

    /**
     * Determine which ticket should be called next based on weights.
     */
    protected function determineNextTicket(string $areaId): ?Ticket
    {
        $servicesInArea = Service::where('area_id', $areaId)->pluck('id');

        if ($servicesInArea->isEmpty()) {
            return null;
        }

        // 1. Recalls (Absentees with 1 absence count) have highest priority
        $recallTicket = Ticket::whereIn('service_id', $servicesInArea)
            ->where('status', 'absent')
            ->where('absence_count', 1)
            ->lockForUpdate()
            ->orderBy('updated_at', 'asc')
            ->first();

        if ($recallTicket) {
            return $recallTicket;
        }

        // 2. Load ratios from Settings
        $ratioRegular = Setting::where('context', "area_{$areaId}")->where('key', 'ratio_regular')->value('value') ?? 1;
        $ratioPriority = Setting::where('context', "area_{$areaId}")->where('key', 'ratio_priority')->value('value') ?? 1;

        $cycleKey = "call_cycle_area_{$areaId}";
        $cycle = Redis::get($cycleKey);
        $cycle = $cycle ? json_decode($cycle, true) : ['regular_called' => 0, 'priority_called' => 0];

        // 3. Decide which queue to pull from
        $fetchPriority = false;
        if ($cycle['regular_called'] >= $ratioRegular && $cycle['priority_called'] < $ratioPriority) {
            $fetchPriority = true;
        } elseif ($cycle['regular_called'] < $ratioRegular) {
            $fetchPriority = false;
        } else {
            // Cycle complete, reset
            $cycle = ['regular_called' => 0, 'priority_called' => 0];
            // After reset, we start according to ratios. If ratio_regular > 0, start with regular.
            $fetchPriority = $ratioRegular > 0 ? false : true;
        }

        $ticket = null;
        if ($fetchPriority) {
            $ticket = $this->getPriorityTicket($servicesInArea);
            if ($ticket) {
                $cycle['priority_called']++;
            } else {
                // Fallback to regular
                $ticket = $this->getRegularTicket($servicesInArea);
                if ($ticket) $cycle['regular_called']++;
            }
        } else {
            $ticket = $this->getRegularTicket($servicesInArea);
            if ($ticket) {
                $cycle['regular_called']++;
            } else {
                // Fallback to priority
                $ticket = $this->getPriorityTicket($servicesInArea);
                if ($ticket) $cycle['priority_called']++;
            }
        }

        // Save cycle state
        if ($ticket) {
            Redis::set($cycleKey, json_encode($cycle));
        }

        return $ticket;
    }

    protected function getPriorityTicket($servicesInArea): ?Ticket
    {
        return Ticket::select('tickets.*')
            ->join('priorities', 'tickets.priority_id', '=', 'priorities.id')
            ->whereIn('tickets.service_id', $servicesInArea)
            ->where('tickets.status', 'pending')
            ->where('priorities.weight', '>', 0)
            ->lockForUpdate()
            ->orderBy('priorities.weight', 'desc')
            ->orderBy('tickets.created_at', 'asc')
            ->first();
    }

    protected function getRegularTicket($servicesInArea): ?Ticket
    {
        return Ticket::select('tickets.*')
            ->join('priorities', 'tickets.priority_id', '=', 'priorities.id')
            ->whereIn('tickets.service_id', $servicesInArea)
            ->where('tickets.status', 'pending')
            ->where('priorities.weight', '=', 0)
            ->lockForUpdate()
            ->orderBy('tickets.created_at', 'asc')
            ->first();
    }

    /**
     * Recall the same ticket again.
     */
    public function recallTicket(Ticket $ticket): void
    {
        // Emit SSE event for TVs
        // No DB state changes required, just update timestamp for UI
        $ticket->touch(); 
    }

    /**
     * Start the service.
     */
    public function startService(Ticket $ticket): void
    {
        if ($ticket->status === 'called' || $ticket->status === 'absent') {
            $ticket->status = 'in_progress';
            $ticket->started_at = now();
            $ticket->save();
        }
    }

    /**
     * Mark the ticket as absent.
     */
    public function markAbsent(Ticket $ticket): void
    {
        if ($ticket->status === 'called') {
            if ($ticket->absence_count == 0) {
                $ticket->status = 'absent';
                $ticket->absence_count = 1;
            } else {
                $ticket->status = 'cancelled'; // Final cancellation due to second absence
            }
            $ticket->save();
        }
    }

    /**
     * Complete the service.
     */
    public function finishService(Ticket $ticket): void
    {
        if ($ticket->status === 'in_progress') {
            $ticket->status = 'completed';
            $ticket->finished_at = now();
            $ticket->save();
        }
    }
}
