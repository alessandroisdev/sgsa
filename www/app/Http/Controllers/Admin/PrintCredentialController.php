<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Unit;
use App\Models\Totem;
use App\Models\Tv;
use App\Models\Counter;

class PrintCredentialController extends Controller
{
    public function index()
    {
        $units = Unit::with(['areas.totems', 'areas.tvs', 'areas.counters'])->where('active', 1)->get();

        $devices = [];
        foreach ($units as $unit) {
            foreach ($unit->areas as $area) {
                foreach ($area->totems as $totem) {
                    $devices[] = [
                        'type' => 'Totem',
                        'name' => $totem->name,
                        'identifier' => $totem->device_identifier,
                        'unit' => $unit->name,
                        'area' => $area->name,
                    ];
                }
                foreach ($area->tvs as $tv) {
                    $devices[] = [
                        'type' => 'TV',
                        'name' => $tv->name,
                        'identifier' => $tv->device_identifier,
                        'unit' => $unit->name,
                        'area' => $area->name,
                    ];
                }
                foreach ($area->counters as $counter) {
                    $devices[] = [
                        'type' => 'Guichê',
                        'name' => $counter->name,
                        'identifier' => $counter->id,
                        'unit' => $unit->name,
                        'area' => $area->name,
                    ];
                }
            }
        }

        // Order by Unit, Area, Type
        usort($devices, function($a, $b) {
            $cmpUnit = strcmp($a['unit'], $b['unit']);
            if ($cmpUnit !== 0) return $cmpUnit;
            $cmpArea = strcmp($a['area'], $b['area']);
            if ($cmpArea !== 0) return $cmpArea;
            return strcmp($a['type'], $b['type']);
        });

        return view('admin.credentials.index', compact('devices'));
    }

    public function printBatch(Request $request)
    {
        $identifiers = $request->input('identifiers', []);
        
        if (empty($identifiers)) {
            return redirect()->back()->with('error', 'Nenhum dispositivo selecionado para impressão.');
        }

        $totems = Totem::whereIn('device_identifier', $identifiers)->with('area.unit')->get();
        $tvs = Tv::whereIn('device_identifier', $identifiers)->with('area.unit')->get();
        $counters = Counter::whereIn('id', $identifiers)->with('area.unit')->get();

        $devicesToPrint = collect();

        foreach ($totems as $totem) {
            $devicesToPrint->push([
                'type' => 'Totem',
                'name' => $totem->name,
                'identifier' => $totem->device_identifier,
                'location' => ($totem->area->unit->name ?? 'Sem Unidade') . ' - ' . ($totem->area->name ?? 'Sem Área'),
            ]);
        }
        foreach ($tvs as $tv) {
            $devicesToPrint->push([
                'type' => 'TV',
                'name' => $tv->name,
                'identifier' => $tv->device_identifier,
                'location' => ($tv->area->unit->name ?? 'Sem Unidade') . ' - ' . ($tv->area->name ?? 'Sem Área'),
            ]);
        }
        foreach ($counters as $counter) {
            $devicesToPrint->push([
                'type' => 'Guichê',
                'name' => $counter->name,
                'identifier' => $counter->id,
                'location' => ($counter->area->unit->name ?? 'Sem Unidade') . ' - ' . ($counter->area->name ?? 'Sem Área'),
            ]);
        }

        return view('admin.credentials.print', ['devicesToPrint' => $devicesToPrint]);
    }
}
