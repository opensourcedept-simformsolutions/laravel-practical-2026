@props([
    'title' => null,
    'description' => null,
    'actions' => null,
])

<section {{ $attributes->class(['card shadow-sm border-0']) }}>
    @if ($title || $description || $actions)
        <div class="card-header bg-transparent px-4 py-3">
            <div class="d-flex flex-column flex-md-row align-items-md-start justify-content-md-between gap-3">
                <div>
                @if ($title)
                    <h2 class="h5 mb-1">{{ $title }}</h2>
                @endif

                @if ($description)
                    <p class="text-muted mb-0">{{ $description }}</p>
                @endif
                </div>

                @if ($actions)
                    <div class="d-flex flex-wrap gap-2">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="card-body p-4">
        {{ $slot }}
    </div>
</section>
