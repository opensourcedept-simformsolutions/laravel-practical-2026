<button
    type="submit"
    {{ $attributes->merge(['class' => 'form-button form-button-primary']) }}>
    {{ $slot }}
</button>
