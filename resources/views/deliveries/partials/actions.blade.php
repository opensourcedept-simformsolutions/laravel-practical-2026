<div class="d-flex flex-wrap gap-2">
    <a href="{{ route('deliveries.show', $delivery) }}" class="btn btn-sm btn-primary rounded">
        <i class="bi bi-eye"></i> View
    </a>
    @canany(['is-admin', 'is-gatekeeper'])
        <a href="{{ route('deliveries.edit', $delivery) }}" class="btn btn-sm btn-warning rounded">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <form action="{{ route('deliveries.destroy', $delivery) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-sm btn-danger rounded">
                <i class="bi bi-trash"></i> Delete
            </button>
        </form>
    @endcanany

    @canany(['is-resident', 'is-admin'])
        @if ($delivery->status === 'received')
            <form action="{{ route('deliveries.deliver', $delivery) }}" method="POST" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-sm btn-success rounded">
                    <i class="bi bi-check-circle"></i>
                    Mark Delivered
                </button>
            </form>
        @endif
    @endcanany
</div>
