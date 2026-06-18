@extends('layouts.app')

@section('title', 'Residents')

@section('content')

  <div class="card shadow-sm border-0 rounded-3">

    <div class="card-header bg-white d-flex justify-content-between align-items-center">
      <span class="fw-semibold">Residents List</span>

      <a href="{{ route('residents.create') }}" type="button" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-square me-1"></i> Add Resident
      </a>
    </div>

    <div class="card-body">

      <div class="table-responsive">
        <table id="residentsTable" class="table table-hover table-striped align-middle w-100 app-datatable">

          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>Phone</th>
              <th>Flat</th>
              <th>Wing</th>
              <th>Type</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>

          <tbody></tbody>

        </table>
      </div>

    </div>
  </div>

@endsection

@push('scripts')
<script>
  $(function () {

    $('#residentsTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: "{{ route('residents.index') }}",

      columns: [
        { data: 'id', name: 'id' },
        { data: 'name', name: 'user.name' },
        { data: 'email', name: 'user.email' },
        { data: 'phone', name: 'user.phone' },
        { data: 'flat', name: 'flat.flat_number' },
        { data: 'wing', name: 'flat.wing' },
        { data: 'type', name: 'resident_type', orderable: false, searchable: false },
        { data: 'actions', orderable: false, searchable: false }
      ]
    });

  });
</script>
@endpush