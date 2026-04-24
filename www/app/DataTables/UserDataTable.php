<?php

namespace App\DataTables;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserDataTable extends BaseDataTable
{
    protected function query(): Builder
    {
        return User::query();
    }

    protected function columns(): array
    {
        return [
            'name' => ['title' => 'Nome', 'searchable' => true, 'orderable' => true],
            'email' => ['title' => 'E-mail', 'searchable' => true, 'orderable' => true],
            'role' => ['title' => 'Nível de Acesso', 'searchable' => false, 'orderable' => true],
            'actions' => ['title' => 'Ações', 'searchable' => false, 'orderable' => false],
        ];
    }

    protected function mapRow($row): array
    {
        $roleBadge = $row->role === 'admin' 
            ? '<span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-pill"><i class="bi bi-shield-lock me-1"></i>Admin</span>' 
            : '<span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 rounded-pill"><i class="bi bi-person-workspace me-1"></i>Atendente</span>';

        $actions = '
            <button class="btn btn-sm btn-light text-primary me-1" onclick="editEntity(\''.$row->id.'\')"><i class="bi bi-pencil"></i></button>
        ';

        if (auth()->id() !== $row->id) {
            $actions .= '<button class="btn btn-sm btn-light text-danger" onclick="deleteEntity(\''.$row->id.'\')"><i class="bi bi-trash"></i></button>';
        }

        return [
            'id' => $row->id,
            'name' => '<div class="fw-medium">'.$row->name.'</div>',
            'email' => $row->email,
            'role' => $roleBadge,
            'actions' => $actions,
        ];
    }
}
