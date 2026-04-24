<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tv;
use App\Models\Area;
use App\DataTables\TvDataTable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TvController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $dataTable = new TvDataTable($request);
            return $dataTable->process();
        }

        $dataTable = new TvDataTable($request);
        $columnsJson = $dataTable->getColumnsJson();
        $areas = Area::with('unit')->where('active', true)->get();

        return view('admin.tvs.index', compact('columnsJson', 'areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'name' => 'required|string|max:255',
            'device_identifier' => 'required|string|unique:tvs,device_identifier',
            'active' => 'boolean',
        ]);

        Tv::create([
            'area_id' => $request->area_id,
            'name' => $request->name,
            'device_identifier' => $request->device_identifier,
            'active' => $request->has('active') ? 1 : 0,
        ]);

        return response()->json(['message' => 'TV criada com sucesso!']);
    }

    public function edit(Tv $tv)
    {
        return response()->json($tv);
    }

    public function update(Request $request, Tv $tv)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'name' => 'required|string|max:255',
            'device_identifier' => ['required', 'string', Rule::unique('tvs')->ignore($tv->id)],
            'active' => 'boolean',
        ]);

        $tv->update([
            'area_id' => $request->area_id,
            'name' => $request->name,
            'device_identifier' => $request->device_identifier,
            'active' => $request->has('active') ? 1 : 0,
        ]);

        return response()->json(['message' => 'TV atualizada com sucesso!']);
    }

    public function destroy(Tv $tv)
    {
        $tv->delete();
        return response()->json(['message' => 'TV removida com sucesso!']);
    }
}
