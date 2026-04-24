<?php

namespace App\DataTables;

use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;

class ServiceDataTable extends BaseDataTable
{
    protected function query(): Builder
    {
        return Service::with('area.unit')->select('services.*');
    }

    protected function columns(): array
    {
        return [
            'name' => ['title' => 'Nome', 'searchable' => true, 'orderable' => true],
            'prefix' => ['title' => 'Prefixo', 'searchable' => true, 'orderable' => true],
            'area_name' => ['title' => 'Área / Unidade', 'searchable' => false, 'orderable' => false],
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

        $areaStr = $row->area ? $row->area->name . ' <small class="text-muted">('.($row->area->unit->name ?? '').')</small>' : '-';

        return [
            'id' => $row->id,
            'name' => '<div class="fw-medium">'.$row->name.'</div>',
            'prefix' => '<span class="fw-bold">'.$row->prefix.'</span>',
            'area_name' => $areaStr,
            'active' => $status,
            'actions' => $actions,
        ];
    }
}
