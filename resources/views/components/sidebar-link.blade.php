@props(['href' => '#','active' => false,])

<a href="{{ $href }}"
    {{ $attributes->merge(['class' => 'sidebar-link ' . ($active ? 'active' : '')]) }}>
    {{ $slot }}
</a>
