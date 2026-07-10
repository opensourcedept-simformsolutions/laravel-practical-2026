@props([
    'name' => null,
    'label' => null,
    'for' => null,
    'help' => null,
    'required' => false,
])

@php
    $fieldFor = $for ?? $name;
@endphp

<div {{ $attributes->class(['mb-3']) }}>
    @if ($label)
        <x-form.label :for="$fieldFor" :required="$required" class="mb-2">
            {{ $label }}
        </x-form.label>
    @endif

    {{ $slot }}
    <x-form.error :name="$name" />
</div>
