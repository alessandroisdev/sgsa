<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Counter;
use App\Models\Ticket;
use App\Services\QueueService;

class AttendantController extends Controller
{
    /**
     * List available counters.
     */
    public function counters()
    {
        // For simplicity, returning all active counters with their areas.
        // In a real scenario, this might be filtered by the user's unit/permissions.
        return Counter::with(['area.unit'])->where('active', true)->get();
    }

    /**
     * Get the current state of a counter (current active ticket and stats).
     */
    public function state(Request $request, Counter $counter)
    {
        $currentTicket = Ticket::with(['service', 'priority'])
            ->where('counter_id', $counter->id)
            ->whereIn('status', ['called', 'in_progress'])
            ->orderBy('called_at', 'desc')
            ->first();

        $pendingCount = Ticket::whereHas('service', function($q) use ($counter) {
            $q->where('area_id', $counter->area_id);
        })->where('status', 'pending')->count();

        return response()->json([
            'current_ticket' => $currentTicket,
            'pending_count' => $pendingCount
        ]);
    }

    /**
     * Call the next ticket.
     */
    public function callNext(Request $request, Counter $counter)
    {
        $queueService = app(QueueService::class);
        $ticket = $queueService->callNextTicket($counter, $request->user());

        if (!$ticket) {
            return response()->json(['message' => 'Fila vazia. Nenhum cliente aguardando.'], 404);
        }

        return response()->json(['ticket' => $ticket->load(['service', 'priority'])]);
    }

    /**
     * Recall current ticket.
     */
    public function recall(Request $request, Ticket $ticket)
    {
        app(QueueService::class)->recallTicket($ticket);
        return response()->json(['message' => 'Senha rechamada na TV']);
    }

    /**
     * Start service.
     */
    public function start(Request $request, Ticket $ticket)
    {
        app(QueueService::class)->startService($ticket);
        return response()->json(['message' => 'Atendimento iniciado', 'ticket' => $ticket]);
    }

    /**
     * Finish service.
     */
    public function finish(Request $request, Ticket $ticket)
    {
        app(QueueService::class)->finishService($ticket);
        return response()->json(['message' => 'Atendimento finalizado', 'ticket' => $ticket]);
    }

    /**
     * Mark as absent.
     */
    public function absent(Request $request, Ticket $ticket)
    {
        app(QueueService::class)->markAbsent($ticket);
        return response()->json(['message' => 'Marcado como ausente', 'ticket' => $ticket]);
    }
}
