@props([
    'type' => 'button',
    'variant' => 'primary',
])

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => 'form-button form-button-' . $variant]) }}>
    {{ $slot }}
</button>
