<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Area;
use App\DataTables\ServiceDataTable;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $dataTable = new ServiceDataTable($request);
            return $dataTable->process();
        }

        $dataTable = new ServiceDataTable($request);
        $columnsJson = $dataTable->getColumnsJson();
        $areas = Area::with('unit')->where('active', true)->get();

        return view('admin.services.index', compact('columnsJson', 'areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('services')->where(function ($query) use ($request) {
                    return $query->where('area_id', $request->area_id);
                })
            ],
            'prefix' => [
                'required',
                'string',
                'max:5',
                \Illuminate\Validation\Rule::unique('services')->where(function ($query) use ($request) {
                    return $query->where('area_id', $request->area_id);
                })
            ],
            'active' => 'boolean',
        ]);

        Service::create([
            'area_id' => $request->area_id,
            'name' => $request->name,
            'prefix' => strtoupper($request->prefix),
            'active' => $request->has('active') ? 1 : 0,
        ]);

        return response()->json(['message' => 'Serviço criado com sucesso!']);
    }

    public function edit(Service $service)
    {
        return response()->json($service);
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'area_id' => 'required|exists:areas,id',
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('services')->where(function ($query) use ($request) {
                    return $query->where('area_id', $request->area_id);
                })->ignore($service->id)
            ],
            'prefix' => [
                'required',
                'string',
                'max:5',
                \Illuminate\Validation\Rule::unique('services')->where(function ($query) use ($request) {
                    return $query->where('area_id', $request->area_id);
                })->ignore($service->id)
            ],
            'active' => 'boolean',
        ]);

        $service->update([
            'area_id' => $request->area_id,
            'name' => $request->name,
            'prefix' => strtoupper($request->prefix),
            'active' => $request->has('active') ? 1 : 0,
        ]);

        return response()->json(['message' => 'Serviço atualizado com sucesso!']);
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return response()->json(['message' => 'Serviço removido com sucesso!']);
    }
}
