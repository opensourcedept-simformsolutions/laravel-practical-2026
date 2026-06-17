@props([
    'name' => null,
    'id' => null,
    'error' => false,
])

@php
    $fieldId = $id ?? $name;
    $hasError = is_bool($error) ? $error : ! empty($error);
@endphp

<input
    type="file"
    @if ($name) name="{{ $name }}" @endif
    @if ($fieldId) id="{{ $fieldId }}" @endif
    {{ $attributes->merge(['class' => 'form-control form-file' . ($hasError ? ' is-invalid' : '')]) }}>
