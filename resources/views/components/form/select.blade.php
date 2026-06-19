@props([
    'name' => null,
    'id' => null,
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'error' => false,
])

@php
    $fieldId = $id ?? $name;
    $selectedValue = old($name, $value);
    $hasError = is_bool($error) ? $error : ! empty($error);
@endphp

<select
    @if ($name) name="{{ $name }}" @endif
    @if ($fieldId) id="{{ $fieldId }}" @endif
    {{ $attributes->merge(['class' => 'form-select' . ($hasError ? ' is-invalid' : '')]) }}>
    @if ($placeholder)
        <option value="">{{ $placeholder }}</option>
    @endif

    @foreach ($options as $optionValue => $optionLabel)
        <option value="{{ $optionValue }}" @selected((string) $selectedValue === (string) $optionValue)>
            {{ $optionLabel }}
        </option>
    @endforeach

    {{ $slot }}
</select>
