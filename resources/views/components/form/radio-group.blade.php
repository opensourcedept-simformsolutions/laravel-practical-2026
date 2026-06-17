@props([
    'name' => null,
    'options' => [],
    'value' => null,
    'legend' => null,
    'error' => false,
])

@php
    $selectedValue = (string) old($name, $value);
    $hasError = is_bool($error) ? $error : ! empty($error);
@endphp

<div {{ $attributes->merge(['class' => 'form-choice-group' . ($hasError ? ' has-error' : '')]) }}>
    @if ($legend)
        <div class="form-group-label">{{ $legend }}</div>
    @endif

    <div class="form-choice-list">
        @foreach ($options as $optionValue => $optionLabel)
            <label class="form-check">
                <input
                    class="form-check-input"
                    type="radio"
                    name="{{ $name }}"
                    value="{{ $optionValue }}"
                    @checked($selectedValue === (string) $optionValue)>
                <span class="form-check-label">{{ $optionLabel }}</span>
            </label>
        @endforeach
    </div>
</div>
