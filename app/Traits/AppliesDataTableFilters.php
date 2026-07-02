<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait AppliesDataTableFilters
{
    protected function applyDataTableFilters(Builder $query, Request $request, array $searchColumns, array $customOrderColumns = []): Builder
    {
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search, $searchColumns) {
                foreach ($searchColumns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }

        if ($request->filled('order')) {
            $columnIndex = $request->input('order.0.column');
            $direction = $request->input('order.0.dir');

            $column = $request->input("columns.$columnIndex.name");

            if ($column && $column !== 'DT_RowIndex') {

                if (isset($customOrderColumns[$column])) {
                    $customOrderColumns[$column]($query, $direction);
                } else {
                    $query->orderBy($column, $direction);
                }
            }
        }

        return $query;
    }
}
