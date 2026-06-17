@props([
    'prepend' => null,
    'append' => null,
])

<div {{ $attributes->merge(['class' => 'form-input-group']) }}>
    @if ($prepend)
        <span class="form-input-group-text">{{ $prepend }}</span>
    @endif

    <div class="form-input-group-control">
        {{ $slot }}
    </div>

    @if ($append)
        <span class="form-input-group-text">{{ $append }}</span>
    @endif
</div>
