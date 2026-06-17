@props([
    'name' => null,
    'options' => [],
    'values' => [],
    'legend' => null,
    'error' => false,
])

@php
    $selectedValues = collect(old($name, $values))->map(fn ($value) => (string) $value)->all();
    $hasError = is_bool($error) ? $error : ! empty($error);
@endphp

<div {{ $attributes->merge(['class' => 'form-choice-group' . ($hasError ? ' has-error' : '' )]) }}>
    @if ($legend)
        <div class="form-group-label">{{ $legend }}</div>
    @endif

    <div class="form-choice-list">
        @foreach ($options as $optionValue => $optionLabel)
            <label class="form-check">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="{{ $name }}[]"
                    value="{{ $optionValue }}"
                    @checked(in_array((string) $optionValue, $selectedValues, true))>
                <span class="form-check-label">{{ $optionLabel }}</span>
            </label>
        @endforeach
    </div>
</div>
