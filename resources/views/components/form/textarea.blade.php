@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'rows' => 4,
    'error' => false,
])

@php
    $fieldId = $id ?? $name;
    $hasError = is_bool($error) ? $error : ! empty($error);
@endphp

<textarea
    @if ($name) name="{{ $name }}" @endif
    @if ($fieldId) id="{{ $fieldId }}" @endif
    rows="{{ $rows }}"
    {{ $attributes->merge(['class' => 'form-control' . ($hasError ? ' is-invalid' : '')]) }}>{{ old($name, $value) }}</textarea>
