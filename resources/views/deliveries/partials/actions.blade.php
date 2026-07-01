<div class="d-flex justify-content-center gap-2">
    <a href="{{ route('deliveries.show', $delivery) }}"
        class="btn btn-sm btn-primary rounded"
        data-bs-toggle="tooltip"
        title="View Delivery">
        <i class="bi bi-eye"></i>
    </a>
    @if ($delivery->trashed())
        @can('restore', $delivery)
            <button
                class="btn btn-info btn-action"
                data-url="{{ route('deliveries.restore', $delivery->id) }}"
                data-method="PATCH"
                data-title="Restore Delivery?"
                data-text="This delivery will be restored."
                data-confirm="Yes, Restore"
                title="Restore Delivery">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
        @endcan
    @else
        @can('update', $delivery)
            <a href="{{ route('deliveries.edit', $delivery) }}"
            class="btn btn-sm btn-warning rounded"
            data-bs-toggle="tooltip"
            title="Edit Delivery">
                <i class="bi bi-pencil-square"></i>
            </a>
        @endcan
        @can('delete', $delivery)
            <button
                class="btn btn-danger btn-action"
                data-url="{{ route('deliveries.destroy', $delivery) }}"
                data-method="DELETE"
                data-title="Delete Delivery?"
                data-text="This action cannot be undone."
                data-confirm="Yes, Delete"
                title="Delete Delivery">
                <i class="bi bi-trash"></i>
            </button>
        @endcan
        @can('markDelivered', $delivery)
            @if ($delivery->status === 'received')
                <form action="{{ route('deliveries.deliver', $delivery) }}"
                    method="POST"
                    class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="btn btn-sm btn-success rounded"
                            data-bs-toggle="tooltip"
                            title="Mark Delivered">
                        <i class="bi bi-check-circle"></i>
                    </button>
                </form>
            @endif
        @endcan
    @endif
</div>
