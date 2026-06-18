@props([
    'name' => null,
    'id' => null,
    'value' => 1,
    'label' => null,
    'checked' => false,
    'error' => false,
])

@php
    $fieldId = $id ?? $name;
    $isChecked = old($name, $checked) == $value || (is_bool(old($name, $checked)) && old($name, $checked));
    $hasError = is_bool($error) ? $error : ! empty($error);
@endphp

<label {{ $attributes->class(['form-check']) }}>
    <input
        class="form-check-input"
        type="checkbox"
        @if ($name) name="{{ $name }}" @endif
        @if ($fieldId) id="{{ $fieldId }}" @endif
        value="{{ $value }}"
        @checked($isChecked)>

    <span class="form-check-label">
        {{ $label ?? $slot }}
    </span>
</label>
