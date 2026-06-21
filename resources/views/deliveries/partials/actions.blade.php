<div class="d-flex flex-wrap gap-2">
    <a href="{{ route('deliveries.show', $delivery) }}"
        class="btn btn-sm btn-primary rounded">
        <i class="bi bi-eye"></i> View
    </a>
    @cannot('is-resident')
        <a href="{{ route('deliveries.edit', $delivery) }}"
            class="btn btn-sm btn-outline-warning rounded">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <form action="{{ route('deliveries.destroy', $delivery) }}"
            method="POST"
            class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="btn btn-sm btn-outline-danger rounded">
                <i class="bi bi-trash"></i> Delete
            </button>
        </form>
        @if ($delivery->status === 'received')
            <form action="{{ route('deliveries.deliver', $delivery) }}"
                method="POST"
                class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit"
                    class="btn btn-sm btn-outline-success rounded">
                    <i class="bi bi-check-circle"></i>
                    Mark Delivered
                </button>
            </form>
        @endif
    @endcannot
</div>
