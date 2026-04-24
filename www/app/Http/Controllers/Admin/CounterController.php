<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Counter;
use App\Models\Area;
use App\DataTables\CounterDataTable;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $dataTable = new CounterDataTable($request);
            return $dataTable->process();
        }

        $dataTable = new CounterDataTable($request);
        $columnsJson = $dataTable->getColumnsJson();
        $areas = Area::with('unit')->where('active', true)->get();

        return view('admin.counters.index', compact('columnsJson', 'areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'name' => 'required|string|max:255',
            'active' => 'boolean',
        ]);

        Counter::create([
            'area_id' => $request->area_id,
            'name' => $request->name,
            'active' => $request->has('active') ? 1 : 0,
        ]);

        return response()->json(['message' => 'Guichê criado com sucesso!']);
    }

    public function edit(Counter $counter)
    {
        return response()->json($counter);
    }

    public function update(Request $request, Counter $counter)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'name' => 'required|string|max:255',
            'active' => 'boolean',
        ]);

        $counter->update([
            'area_id' => $request->area_id,
            'name' => $request->name,
            'active' => $request->has('active') ? 1 : 0,
        ]);

        return response()->json(['message' => 'Guichê atualizado com sucesso!']);
    }

    public function destroy(Counter $counter)
    {
        $counter->delete();
        return response()->json(['message' => 'Guichê removido com sucesso!']);
    }
}
