<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Totem;
use App\Models\Area;
use App\DataTables\TotemDataTable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TotemController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $dataTable = new TotemDataTable($request);
            return $dataTable->process();
        }

        $dataTable = new TotemDataTable($request);
        $columnsJson = $dataTable->getColumnsJson();
        $areas = Area::with('unit')->where('active', true)->get();

        return view('admin.totems.index', compact('columnsJson', 'areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'name' => 'required|string|max:255',
            'device_identifier' => 'required|string|unique:totems,device_identifier',
            'active' => 'boolean',
        ]);

        Totem::create([
            'area_id' => $request->area_id,
            'name' => $request->name,
            'device_identifier' => $request->device_identifier,
            'active' => $request->has('active') ? 1 : 0,
        ]);

        return response()->json(['message' => 'Totem criado com sucesso!']);
    }

    public function edit(Totem $totem)
    {
        return response()->json($totem);
    }

    public function update(Request $request, Totem $totem)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'name' => 'required|string|max:255',
            'device_identifier' => ['required', 'string', Rule::unique('totems')->ignore($totem->id)],
            'active' => 'boolean',
        ]);

        $totem->update([
            'area_id' => $request->area_id,
            'name' => $request->name,
            'device_identifier' => $request->device_identifier,
            'active' => $request->has('active') ? 1 : 0,
        ]);

        return response()->json(['message' => 'Totem atualizado com sucesso!']);
    }

    public function destroy(Totem $totem)
    {
        $totem->delete();
        return response()->json(['message' => 'Totem removido com sucesso!']);
    }
}
