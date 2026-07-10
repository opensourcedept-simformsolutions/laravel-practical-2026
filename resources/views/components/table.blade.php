@props([
    'id' => null,
    'title' => null,
    'paging' => true,
    'ordering' => true,
    'pageLength' => 10,
])

@php
    $tableId = $id ?? 'datatable-' . \Illuminate\Support\Str::uuid();
@endphp
<div class="card shadow-sm border-0">
    @if($title)
        <div class="card-header bg-transparent py-3">
            <h2 class="h3 fw-bold mb-0">
                {{ $title }}
            </h2>
        </div>
    @endif

    <div class="card-body">
        <div class="table-responsive">
            <table
                id="{{ $tableId }}"
                data-datatable data-searching="true"
                data-paging="{{ $paging ? 'true' : 'false' }}"
                data-ordering="{{ $ordering ? 'true' : 'false' }}"
                data-page-length="{{ $pageLength }}"
                {{ $attributes->merge([
                    'class' => 'table table-hover align-middle mb-0',
                ]) }}>
                {{ $slot }}
            </table>
        </div>
    </div>

    @isset($footer)
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endisset
</div>
