<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Service;
use App\Models\Priority;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard.index');
    }

    public function metrics()
    {
        $today = Carbon::today();
        
        // Global Metrics
        $totalTicketsToday = Ticket::whereDate('created_at', $today)->count();
        $totalTicketsMonth = Ticket::whereMonth('created_at', $today->month)->whereYear('created_at', $today->year)->count();
        
        $pendingTickets = Ticket::whereIn('status', ['pending', 'called'])->count();
        $completedTickets = Ticket::whereDate('created_at', $today)->where('status', 'completed')->count();

        // Top Attendant (Hoje)
        $topUser = User::withCount(['tickets' => function($q) use ($today) {
            $q->whereDate('created_at', $today)->where('status', 'completed');
        }])->having('tickets_count', '>', 0)->orderByDesc('tickets_count')->first();

        // Top Service (Hoje)
        $topService = Service::withCount(['tickets' => function($q) use ($today) {
            $q->whereDate('created_at', $today);
        }])->having('tickets_count', '>', 0)->orderByDesc('tickets_count')->first();

        // Top Priority (Hoje)
        $topPriority = Priority::withCount(['tickets' => function($q) use ($today) {
            $q->whereDate('created_at', $today);
        }])->having('tickets_count', '>', 0)->orderByDesc('tickets_count')->first();

        // Average Service Time (Today)
        $avgServiceTimeSec = Ticket::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, started_at, finished_at)) as avg_time')
            ->value('avg_time');
            
        $avgServiceTime = $avgServiceTimeSec ? gmdate("H:i:s", (int)$avgServiceTimeSec) : '00:00:00';

        // Average Waiting Time (Today)
        $avgWaitTimeSec = Ticket::whereDate('created_at', $today)
            ->whereNotNull('called_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, called_at)) as avg_wait')
            ->value('avg_wait');
            
        $avgWaitTime = $avgWaitTimeSec ? gmdate("H:i:s", (int)$avgWaitTimeSec) : '00:00:00';

        // Active Units overview
        $units = Unit::where('active', 1)->get()->map(function($unit) {
            $areasIds = $unit->areas()->pluck('id');
            // get tickets for this unit
            $pending = Ticket::whereIn('status', ['pending'])
                ->whereHas('service', function($q) use ($areasIds) {
                    $q->whereIn('area_id', $areasIds);
                })->count();
            
            $inProgress = Ticket::whereIn('status', ['called', 'in_progress'])
                ->whereHas('service', function($q) use ($areasIds) {
                    $q->whereIn('area_id', $areasIds);
                })->count();

            return [
                'id' => $unit->id,
                'name' => $unit->name,
                'pending' => $pending,
                'in_progress' => $inProgress
            ];
        });

        return response()->json([
            'total_today' => $totalTicketsToday,
            'total_month' => $totalTicketsMonth,
            'pending' => $pendingTickets,
            'completed' => $completedTickets,
            'top_user' => $topUser ? ['name' => $topUser->name, 'count' => $topUser->tickets_count] : null,
            'top_service' => $topService ? ['name' => $topService->name, 'count' => $topService->tickets_count] : null,
            'top_priority' => $topPriority ? ['name' => $topPriority->name, 'count' => $topPriority->tickets_count] : null,
            'avg_service_time' => $avgServiceTime,
            'avg_wait_time' => $avgWaitTime,
            'units' => $units
        ]);
    }

    public function unitMonitor(Unit $unit)
    {
        $unit->load(['areas.services', 'areas.totems', 'areas.tvs', 'areas.counters']);
        
        $areas = $unit->areas->map(function($area) {
            $serviceIds = $area->services->pluck('id');
            
            $pending = Ticket::whereIn('status', ['pending'])->whereIn('service_id', $serviceIds)->count();
            $inProgress = Ticket::whereIn('status', ['called', 'in_progress'])->whereIn('service_id', $serviceIds)->count();
            $completed = Ticket::whereDate('created_at', Carbon::today())->where('status', 'completed')->whereIn('service_id', $serviceIds)->count();

            return [
                'id' => $area->id,
                'name' => $area->name,
                'totems_count' => $area->totems->where('active', 1)->count(),
                'tvs_count' => $area->tvs->where('active', 1)->count(),
                'counters_count' => $area->counters->where('active', 1)->count(),
                'tickets_pending' => $pending,
                'tickets_in_progress' => $inProgress,
                'tickets_completed_today' => $completed,
            ];
        });

        return response()->json([
            'unit' => [
                'id' => $unit->id,
                'name' => $unit->name
            ],
            'areas' => $areas
        ]);
    }
}
