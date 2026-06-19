@props([
    'name' => null,
    'value' => null,
])

<input
    type="hidden"
    @if ($name) name="{{ $name }}" @endif
    value="{{ old($name, $value) }}"
    {{ $attributes }}>
