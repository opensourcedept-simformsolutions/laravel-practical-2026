@props(['name'])

@if ($errors->has($name))
    <div {{ $attributes->merge(['class' => 'invalid-feedback d-block']) }}>
        {{ $errors->first($name) }}
    </div>
@endif


