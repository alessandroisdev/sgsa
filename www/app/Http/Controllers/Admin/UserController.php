<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Unit;
use App\Models\Area;
use App\DataTables\UserDataTable;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $dataTable = new UserDataTable($request);
            return $dataTable->process();
        }

        $dataTable = new UserDataTable($request);
        $columnsJson = $dataTable->getColumnsJson();
        
        $units = Unit::where('active', 1)->get();
        $areas = Area::with('unit')->where('active', 1)->get();

        return view('admin.users.index', compact('columnsJson', 'units', 'areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,attendant',
            'unit_ids' => 'array',
            'area_ids' => 'array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
        ]);

        if ($request->role === 'attendant') {
            if ($request->has('unit_ids')) {
                $user->units()->sync($request->unit_ids);
            }
            if ($request->has('area_ids')) {
                $user->areas()->sync($request->area_ids);
            }
        }

        return response()->json(['message' => 'Usuário criado com sucesso!']);
    }

    public function edit(User $user)
    {
        $user->load(['units', 'areas']);
        return response()->json([
            'user' => $user,
            'unit_ids' => $user->units->pluck('id'),
            'area_ids' => $user->areas->pluck('id'),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,attendant',
            'unit_ids' => 'array',
            'area_ids' => 'array',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        if ($request->role === 'attendant') {
            $user->units()->sync($request->unit_ids ?? []);
            $user->areas()->sync($request->area_ids ?? []);
        } else {
            $user->units()->sync([]);
            $user->areas()->sync([]);
        }

        return response()->json(['message' => 'Usuário atualizado com sucesso!']);
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return response()->json(['message' => 'Você não pode excluir a si mesmo.'], 403);
        }
        
        $user->delete();
        return response()->json(['message' => 'Usuário removido com sucesso!']);
    }
}
