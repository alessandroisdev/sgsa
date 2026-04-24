<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Priority;
use App\DataTables\PriorityDataTable;
use Illuminate\Http\Request;

class PriorityController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $dataTable = new PriorityDataTable($request);
            return $dataTable->process();
        }

        $dataTable = new PriorityDataTable($request);
        $columnsJson = $dataTable->getColumnsJson();

        return view('admin.priorities.index', compact('columnsJson'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'weight' => 'required|integer|min:0|max:10',
            'active' => 'boolean',
        ]);

        Priority::create([
            'name' => $request->name,
            'weight' => $request->weight,
            'active' => $request->has('active') ? 1 : 0,
        ]);

        return response()->json(['message' => 'Prioridade criada com sucesso!']);
    }

    public function edit(Priority $priority)
    {
        return response()->json($priority);
    }

    public function update(Request $request, Priority $priority)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'weight' => 'required|integer|min:0|max:10',
            'active' => 'boolean',
        ]);

        $priority->update([
            'name' => $request->name,
            'weight' => $request->weight,
            'active' => $request->has('active') ? 1 : 0,
        ]);

        return response()->json(['message' => 'Prioridade atualizada com sucesso!']);
    }

    public function destroy(Priority $priority)
    {
        $priority->delete();
        return response()->json(['message' => 'Prioridade removida com sucesso!']);
    }
}
