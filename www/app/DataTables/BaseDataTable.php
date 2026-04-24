<?php

namespace App\DataTables;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

abstract class BaseDataTable
{
    protected Request $request;
    protected Builder $query;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Define the base query for the table.
     */
    abstract protected function query(): Builder;

    /**
     * Define the columns and their configurations.
     * Returns an array like:
     * [
     *     'id' => ['title' => 'ID', 'searchable' => true, 'orderable' => true],
     *     'name' => ['title' => 'Name', 'searchable' => true, 'orderable' => true],
     * ]
     */
    abstract protected function columns(): array;

    /**
     * Transform a single row before sending to client.
     * Useful for formatting dates, adding action buttons, etc.
     */
    protected function mapRow($row): array
    {
        return $row->toArray();
    }

    /**
     * Handle the server-side processing request.
     */
    public function process(): JsonResponse
    {
        $this->query = $this->query();
        $columnsDef = $this->columns();
        $columnKeys = array_keys($columnsDef);

        $totalRecords = $this->query->count();
        $filteredRecords = $totalRecords;

        // Searching
        if ($this->request->has('search') && !empty($this->request->input('search.value'))) {
            $searchValue = $this->request->input('search.value');
            $this->query->where(function ($q) use ($columnsDef, $searchValue) {
                $first = true;
                foreach ($columnsDef as $key => $config) {
                    if ($config['searchable'] ?? false) {
                        if ($first) {
                            $q->where($key, 'like', "%{$searchValue}%");
                            $first = false;
                        } else {
                            $q->orWhere($key, 'like', "%{$searchValue}%");
                        }
                    }
                }
            });
            $filteredRecords = $this->query->count();
        }

        // Sorting
        if ($this->request->has('order')) {
            $orderConfig = $this->request->input('order.0');
            $columnIndex = $orderConfig['column'];
            $orderDirection = $orderConfig['dir'];

            if (isset($columnKeys[$columnIndex])) {
                $columnKey = $columnKeys[$columnIndex];
                if ($columnsDef[$columnKey]['orderable'] ?? false) {
                    $this->query->orderBy($columnKey, $orderDirection);
                }
            }
        }

        // Pagination
        $start = $this->request->input('start', 0);
        $length = $this->request->input('length', 10);
        
        if ($length != -1) {
            $this->query->skip($start)->take($length);
        }

        $data = $this->query->get()->map(function ($item) {
            return $this->mapRow($item);
        });

        return response()->json([
            'draw' => intval($this->request->input('draw', 1)),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    /**
     * Returns the column definitions as JSON for the frontend initialization.
     */
    public function getColumnsJson(): string
    {
        $columns = [];
        foreach ($this->columns() as $dataKey => $config) {
            $columns[] = [
                'data' => $dataKey,
                'title' => $config['title'] ?? ucfirst($dataKey),
                'orderable' => $config['orderable'] ?? true,
                'searchable' => $config['searchable'] ?? true,
            ];
        }
        return json_encode($columns);
    }
}
