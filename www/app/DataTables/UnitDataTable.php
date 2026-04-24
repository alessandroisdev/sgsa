<?php

namespace App\DataTables;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;

class UnitDataTable extends BaseDataTable
{
    protected function query(): Builder
    {
        return Unit::query();
    }

    protected function columns(): array
    {
        return [
            'name' => ['title' => 'Nome da Unidade', 'searchable' => true, 'orderable' => true],
            'active' => ['title' => 'Status', 'searchable' => false, 'orderable' => true],
            'created_at' => ['title' => 'Criado em', 'searchable' => false, 'orderable' => true],
            'actions' => ['title' => 'Ações', 'searchable' => false, 'orderable' => false],
        ];
    }

    protected function mapRow($row): array
    {
        $status = $row->active 
            ? '<span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill">Ativo</span>' 
            : '<span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-pill">Inativo</span>';

        $actions = '
            <button class="btn btn-sm btn-light text-primary me-1" onclick="editUnit(\''.$row->id.'\')"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-light text-danger" onclick="deleteUnit(\''.$row->id.'\')"><i class="bi bi-trash"></i></button>
        ';

        return [
            'id' => $row->id,
            'name' => '<div class="fw-medium">'.$row->name.'</div>',
            'active' => $status,
            'created_at' => $row->created_at->format('d/m/Y H:i'),
            'actions' => $actions,
        ];
    }
}
