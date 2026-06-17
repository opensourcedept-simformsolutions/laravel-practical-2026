@props([
    'title' => null,
    'description' => null,
    'actions' => null,
])

<section {{ $attributes->merge(['class' => 'form-section']) }}>
    @if ($title || $description || $actions)
        <div class="form-section-head">
            <div>
                @if ($title)
                    <h2 class="form-section-title">{{ $title }}</h2>
                @endif

                @if ($description)
                    <p class="form-section-description">{{ $description }}</p>
                @endif
            </div>

            @if ($actions)
                <div class="form-section-actions">
                    {{ $actions }}
                </div>
            @endif
        </div>
    @endif

    <div class="form-section-body">
        {{ $slot }}
    </div>
</section>
