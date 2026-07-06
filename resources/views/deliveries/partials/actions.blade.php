<div class="d-flex justify-content-center gap-2">
    <a href="{{ route('deliveries.show', $delivery) }}"
        class="btn btn-sm btn-info text-white rounded"
        data-bs-toggle="tooltip"
        title="View Delivery">
        <i class="bi bi-eye-fill"></i>
    </a>
    @if ($delivery->trashed())
        @can('restore', $delivery)
            <button
                class="btn btn-secondary text-white btn-action btn-sm"
                data-url="{{ route('deliveries.restore', $delivery->id) }}"
                data-method="PATCH"
                data-title="Restore Delivery?"
                data-text="This delivery will be restored."
                data-confirm="Yes, Restore"
                title="Restore Delivery">
                <i class="bi bi-arrow-up-left-circle-fill"></i>
            </button>
        @endcan
    @else
        @can('update', $delivery)
            <a href="{{ route('deliveries.edit', $delivery) }}"
            class="btn btn-sm btn-primary text-white rounded"
            data-bs-toggle="tooltip"
            title="Edit Delivery">
                <i class="bi bi-pencil-fill"></i>
            </a>
        @endcan
        @can('delete', $delivery)
            <button
                class="btn btn-danger text-white btn-action btn-sm"
                data-url="{{ route('deliveries.destroy', $delivery) }}"
                data-method="DELETE"
                data-title="Delete Delivery?"
                data-text="This action cannot be undone."
                data-confirm="Yes, Delete"
                title="Delete Delivery">
                <i class="bi bi-trash-fill"></i>
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
                            class="btn btn-sm btn-success text-white rounded"
                            data-bs-toggle="tooltip"
                            title="Mark Delivered">
                        <i class="bi bi-check-circle-fill"></i>
                    </button>
                </form>
            @endif
        @endcan
    @endif
</div>
