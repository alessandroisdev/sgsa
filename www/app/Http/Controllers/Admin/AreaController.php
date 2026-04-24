<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Unit;
use App\DataTables\AreaDataTable;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $dataTable = new AreaDataTable($request);
            return $dataTable->process();
        }

        $dataTable = new AreaDataTable($request);
        $columnsJson = $dataTable->getColumnsJson();
        $units = Unit::where('active', true)->get();

        return view('admin.areas.index', compact('columnsJson', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        Area::create([
            'unit_id' => $request->unit_id,
            'name' => $request->name,
            'description' => $request->description,
            'active' => $request->has('active') ? 1 : 0,
        ]);

        return response()->json(['message' => 'Área criada com sucesso!']);
    }

    public function edit(Area $area)
    {
        return response()->json($area);
    }

    public function update(Request $request, Area $area)
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $area->update([
            'unit_id' => $request->unit_id,
            'name' => $request->name,
            'description' => $request->description,
            'active' => $request->has('active') ? 1 : 0,
        ]);

        return response()->json(['message' => 'Área atualizada com sucesso!']);
    }

    public function destroy(Area $area)
    {
        $area->delete();
        return response()->json(['message' => 'Área removida com sucesso!']);
    }
}
