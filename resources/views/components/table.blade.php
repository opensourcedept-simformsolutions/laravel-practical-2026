@props([
    'id' => null,
    'searching' => true,
    'paging' => true,
    'ordering' => true,
    'pageLength' => 10,
])

@php
    $tableId = $id ?? 'datatable-' . \Illuminate\Support\Str::uuid();
@endphp

<div class="table-responsive app-datatable-wrapper">
    <table
        id="{{ $tableId }}"
        data-datatable
        data-searching="{{ $searching ? 'true' : 'false' }}"
        data-paging="{{ $paging ? 'true' : 'false' }}"
        data-ordering="{{ $ordering ? 'true' : 'false' }}"
        data-page-length="{{ $pageLength }}"
        {{ $attributes->merge(['class' => 'table table-bordered table-striped app-datatable']) }}>
        {{ $slot }}
    </table>
</div>
