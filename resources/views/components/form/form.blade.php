@props([
    'action' => null,
    'method' => 'POST',
    'hasFiles' => false,
])

@php
    $httpMethod = strtoupper($method);
    $formMethod = in_array($httpMethod, ['GET', 'POST'], true) ? $httpMethod : 'POST';
@endphp

<form method="{{ $formMethod }}" @if ($action) action="{{ $action }}" @endif
    @if ($hasFiles) enctype="multipart/form-data" @endif
    {{ $attributes->merge(['class' => 'form-base']) }}>
    @if ($formMethod !== 'GET')
        @csrf
    @endif

    @if (!in_array($httpMethod, ['GET', 'POST'], true))
        @method($httpMethod)
    @endif

    {{ $slot }}
</form>
