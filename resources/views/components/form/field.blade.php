@props([
    'name' => null,
    'label' => null,
    'for' => null,
    'help' => null,
    'required' => false,
])

@php
    $fieldName = $name;
    $fieldFor = $for ?? $name;
    $fieldErrorText = $fieldName ? $errors->first($fieldName) : null;
@endphp

<div {{ $attributes->merge(['class' => 'form-field']) }}>
    @if ($label)
        <x-form.label :for="$fieldFor" :required="$required">
            {{ $label }}
        </x-form.label>
    @endif

    {{ $slot }}

</div>
