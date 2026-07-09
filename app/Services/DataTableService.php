<?php

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Facades\DataTables;

class DataTableService
{
    /**
     * @param  Builder  $query  Pre-scoped query (joins, role filtering, request filters already applied)
     * @param  callable(EloquentDataTable): void  $configure  addColumn/editColumn/orderColumn/rawColumns for this listing
     * @param  string  $context  used in log message + generic error message, e.g. 'load delivery datatable'
     */
    public function handle(Builder $query, callable $configure, string $context = 'load data'): JsonResponse
    {
        try {
            $dt = DataTables::of($query)->addIndexColumn();
            $configure($dt);

            return $dt->make(true);
        } catch (Exception $e) {
            Log::error("Failed to {$context}: ".$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'success' => false,
                'message' => "Failed to {$context}.",
            ], 500);
        }
    }
}
