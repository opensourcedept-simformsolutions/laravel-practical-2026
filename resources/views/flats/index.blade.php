@extends('layouts.app')

@section('title', 'Flats')

@section('content')

    <div class="card shadow-sm border-0 rounded-3">

        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Flats List</span>

            <a href="{{ route('flats.create') }}" type="button" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-square me-1"></i> Add Flat
            </a>
        </div>

        <div class="card-body">
            @if (auth()->user()->isSuperAdmin())
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Society</label>
                        <select id="society_filter" name="society_id" class="form-select">
                            <option value="">Select Society</option>

                            @foreach ($societies as $society)
                                <option value="{{ $society->id }}"
                                    {{ old('society_id') == $society->id ? 'selected' : '' }}>
                                    {{ $society->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('society_id')
                            <div class="text-danger pt-1">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            @endif

            <div class="table-responsive">
                <table id="flatsTable" class="table table-hover table-striped align-middle w-100 app-datatable">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Wing</th>
                            <th>Floor</th>
                            <th>Flat Number</th>
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
        $(document).ready(function() {

            console.log('script loaded');

            table = $('#flatsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('flats.index') }}",
                    data: function(d) {
                        d.society_id = $('#society_filter').val();
                    }
                },
                layout: {
                    topStart: {
                        buttons: ['csv', 'excel']
                    },
                    topEnd: {
                        search: true,
                        pageLength: true
                    }
                },
                columns: [{
                        data: 'society',
                        name: 'society',
                        visible: "{{ auth()->user()->isSuperAdmin() ? 'true' : 'false' }}"
                    },
                    {
                        data: 'wing',
                        name: 'wing'
                    },
                    {
                        data: 'floor',
                        name: 'floor'
                    },
                    {
                        data: 'flat_number',
                        name: 'flat_number'
                    },
                    {
                        data: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $(document).on('change', '#society_filter', function() {
                console.log('Selected Society:', $(this).val());
                rd();
            });

        });
    </script>
@endpush
