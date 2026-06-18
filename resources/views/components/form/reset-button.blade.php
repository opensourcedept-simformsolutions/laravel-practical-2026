<button
    type="reset"
    {{ $attributes->merge(['class' => 'form-button form-button-secondary']) }}>
    {{ $slot }}
</button>
