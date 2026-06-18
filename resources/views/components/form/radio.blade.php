@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'label' => null,
    'checked' => false,
    'error' => false,
])

@php
    $fieldId = $id ?? ($name ? $name . '-' . $value : null);
    $isChecked = (string) old($name, $checked ? $value : null) === (string) $value;
    $hasError = is_bool($error) ? $error : ! empty($error);
@endphp

<label {{ $attributes->merge(['class' => 'form-check' . ($hasError ? ' has-error' : '')]) }}>
    <input
        class="form-check-input"
        type="radio"
        @if ($name) name="{{ $name }}" @endif
        @if ($fieldId) id="{{ $fieldId }}" @endif
        value="{{ $value }}"
        @checked($isChecked)>

    <span class="form-check-label">
        {{ $label ?? $slot }}
    </span>
</label>
