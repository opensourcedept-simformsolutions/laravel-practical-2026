@props([
    'for' => null,
    'required' => false,
])

<label
    @if ($for) for="{{ $for }}" @endif
    {{ $attributes->merge(['class' => 'form-label']) }}>
    <span>{{ $slot }}</span>

    @if ($required)
        <span class="form-label-required">*</span>
    @endif
</label>
