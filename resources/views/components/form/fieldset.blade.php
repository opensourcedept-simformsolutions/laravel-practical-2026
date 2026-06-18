@props([
    'legend' => null,
    'description' => null,
])

<fieldset {{ $attributes->merge(['class' => 'form-fieldset']) }}>
    @if ($legend || $description)
        <div class="form-fieldset-head">
            @if ($legend)
                <legend class="form-fieldset-legend">{{ $legend }}</legend>
            @endif

            @if ($description)
                <p class="form-fieldset-description">{{ $description }}</p>
            @endif
        </div>
    @endif

    <div class="form-fieldset-body">
        {{ $slot }}
    </div>
</fieldset>
