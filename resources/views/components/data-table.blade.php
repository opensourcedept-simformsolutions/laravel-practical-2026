<div class="table-responsive">
    <table id="{{ $id }}" class="table table-bordered table-striped">
        {{ $slot }}
    </table>
</div>

@push('scripts')
<script>
$(function () {
    $('#{{ $id }}').DataTable({
        searching: {{ $searching ? 'true' : 'false' }},
        paging: {{ $paging ? 'true' : 'false' }},
        ordering: {{ $ordering ? 'true' : 'false' }},
        pageLength: {{ $pageLength }}
    });
});
</script>
@endpush