<?php

namespace App\DataTables;

use App\Models\Priority;
use Illuminate\Database\Eloquent\Builder;

class PriorityDataTable extends BaseDataTable
{
    protected function query(): Builder
    {
        return Priority::query();
    }

    protected function columns(): array
    {
        return [
            'name' => ['title' => 'Nome', 'searchable' => true, 'orderable' => true],
            'weight' => ['title' => 'Peso', 'searchable' => false, 'orderable' => true],
            'active' => ['title' => 'Status', 'searchable' => false, 'orderable' => true],
            'actions' => ['title' => 'Ações', 'searchable' => false, 'orderable' => false],
        ];
    }

    protected function mapRow($row): array
    {
        $status = $row->active 
            ? '<span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill">Ativo</span>' 
            : '<span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-pill">Inativo</span>';

        $actions = '
            <button class="btn btn-sm btn-light text-primary me-1" onclick="editEntity(\''.$row->id.'\')"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-light text-danger" onclick="deleteEntity(\''.$row->id.'\')"><i class="bi bi-trash"></i></button>
        ';

        return [
            'id' => $row->id,
            'name' => '<div class="fw-medium">'.$row->name.'</div>',
            'weight' => '<span class="badge bg-secondary">'.$row->weight.'</span>',
            'active' => $status,
            'actions' => $actions,
        ];
    }
}
