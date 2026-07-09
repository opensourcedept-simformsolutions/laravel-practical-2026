<?php

namespace App\Services\Export;

use App\Traits\AppliesDataTableFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    use AppliesDataTableFilters;

    /**
     * @param  array<string>  $searchColumns  columns eligible for the DataTables search box
     * @param  array<string>  $headers  CSV header row
     * @param  callable(object): array  $rowMapper  maps one query row to a CSV row array
     * @param  array<string, callable>  $customOrderColumns  same shape AppliesDataTableFilters expects
     */
    public function export(
        Request $request,
        Builder $query,
        array $searchColumns,
        array $headers,
        callable $rowMapper,
        string $filename,
        array $customOrderColumns = []
    ): StreamedResponse {
        $query = $this->applyDataTableFilters($query, $request, $searchColumns, $customOrderColumns);

        return response()->streamDownload(function () use ($query, $headers, $rowMapper) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);

            foreach ($query->cursor() as $row) {
                fputcsv($handle, $rowMapper($row));
            }

            fclose($handle);
        }, $filename);
    }
}
