@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'placeholder' => 'Search...',
    'error' => false,
])

<x-form.input-group {{ $attributes->only('class') }} prepend="Search">
    <x-form.input
        type="search"
        :name="$name"
        :id="$id"
        :value="$value"
        :placeholder="$placeholder"
        :error="$error"
        {{ $attributes->except('class') }} />
</x-form.input-group>
