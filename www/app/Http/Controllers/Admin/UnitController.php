<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\DataTables\UnitDataTable;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $dataTable = new UnitDataTable($request);
            return $dataTable->process();
        }

        $dataTable = new UnitDataTable($request);
        $columnsJson = $dataTable->getColumnsJson();

        return view('admin.units.index', compact('columnsJson'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'boolean',
        ]);

        Unit::create([
            'name' => $request->name,
            'active' => $request->has('active') ? 1 : 0,
        ]);

        return response()->json(['message' => 'Unidade criada com sucesso!']);
    }

    public function edit(Unit $unit)
    {
        return response()->json($unit);
    }

    public function update(Request $request, Unit $unit)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'active' => 'boolean',
        ]);

        $unit->update([
            'name' => $request->name,
            'active' => $request->has('active') ? 1 : 0,
        ]);

        return response()->json(['message' => 'Unidade atualizada com sucesso!']);
    }

    public function destroy(Unit $unit)
    {
        $unit->delete();
        return response()->json(['message' => 'Unidade removida com sucesso!']);
    }
}
