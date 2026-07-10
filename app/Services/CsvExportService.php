<?php

namespace App\Services\Export;

use App\Traits\AppliesDataTableFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    use AppliesDataTableFilters;

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
