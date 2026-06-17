@props([
    'name' => null,
    'id' => null,
    'type' => 'text',
    'value' => null,
    'error' => false,
])

@php
    $fieldId = $id ?? $name;
    $hasError = is_bool($error) ? $error : ! empty($error);
@endphp

<input
    type="{{ $type }}"
    @if ($name) name="{{ $name }}" @endif
    @if ($fieldId) id="{{ $fieldId }}" @endif
    value="{{ old($name, $value) }}"
    {{ $attributes->merge(['class' => 'form-control' . ($hasError ? ' is-invalid' : '')]) }}>
