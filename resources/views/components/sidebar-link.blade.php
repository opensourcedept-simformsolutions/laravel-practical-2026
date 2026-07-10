@props(['href' => '#', 'active' => false])

<a href="{{ $href }}"
    @if ($active)
        aria-current="page"
    @endif
    data-sidebar-link
    data-active="{{ $active ? 'true' : 'false' }}"
    {{ $attributes->merge(['class' => 'sidebar-link' . ($active ? ' active' : '')]) }}>
    {{ $slot }}
</a>
