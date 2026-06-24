<div class="d-flex flex-wrap gap-2">
    <a href="{{ route('deliveries.show', $delivery) }}"
       class="btn btn-sm btn-primary rounded"
       data-bs-toggle="tooltip"
       title="View Delivery">
        <i class="bi bi-eye"></i>
    </a>

    @can('update', $delivery)
        <a href="{{ route('deliveries.edit', $delivery) }}"
           class="btn btn-sm btn-warning rounded"
           data-bs-toggle="tooltip"
           title="Edit Delivery">
            <i class="bi bi-pencil-square"></i>
        </a>
    @endcan

    @can('delete', $delivery)
        <form action="{{ route('deliveries.destroy', $delivery) }}"
              method="POST"
              class="d-inline delete-form">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="btn btn-sm btn-danger rounded"
                    data-bs-toggle="tooltip"
                    title="Delete Delivery">
                <i class="bi bi-trash"></i>
            </button>
        </form>
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
</div>
