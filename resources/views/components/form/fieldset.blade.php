@props([
    'legend' => null,
    'description' => null,
])

<fieldset {{ $attributes->class(['border rounded-3 p-3 p-md-4']) }}>
    @if ($legend || $description)
        <div class="mb-3">
            @if ($legend)
                <legend class="fs-6 fw-semibold mb-1">{{ $legend }}</legend>
            @endif

            @if ($description)
                <p class="text-muted mb-0">{{ $description }}</p>
            @endif
        </div>
    @endif

    <div class="d-grid gap-3">
        {{ $slot }}
    </div>
</fieldset>
