@extends('layouts.app')

@section('title', 'Create System Permission')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Breadcrumb --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.system-permissions.index') }}">System Permissions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create</li>
                </ol>
            </nav>
            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle-fill text-primary"></i> Create System Permission
            </h4>
        </div>
        <a href="{{ route('admin.system-permissions.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark">New Permission Details</h6>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('admin.system-permissions.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Category / Group <span class="text-danger">*</span></label>
                            <select id="group_select" class="form-select @error('group') is-invalid @enderror" required>
                                <option value="">-- Select Existing Category --</option>
                                @foreach ($existingGroups as $groupName)
                                    <option value="{{ $groupName }}" @selected(old('group') == $groupName)>{{ $groupName }}</option>
                                @endforeach
                                <option value="__new__" @selected(old('group') && !in_array(old('group'), $existingGroups->toArray()))>+ Create New Category...</option>
                            </select>

                            <div id="new_group_container" class="mt-2 d-none">
                                <input type="text" id="new_group_input" class="form-control" placeholder="Type new category name (e.g. Maintenance)">
                            </div>
                            <input type="hidden" name="group" id="final_group_input" value="{{ old('group') }}">

                            @error('group')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Permission Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="perm_name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Export Resident Data" required autocomplete="off">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark">Permission Slug (Key) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted font-monospace fs-8" id="slug_prefix">key:</span>
                                <input type="text" name="slug" id="perm_slug" class="form-control font-monospace @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="e.g. residents.export" required>
                            </div>
                            <div class="form-text small">Unique slug key automatically generated as you type. You can also customize it manually.</div>
                            @error('slug')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="Describe what actions this permission grants to users...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <a href="{{ route('admin.system-permissions.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                                <i class="bi bi-check-lg me-1"></i> Save System Permission
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let isSlugUserEdited = false;

    function syncGroupInput() {
        let val = $('#group_select').val();
        if (val === '__new__') {
            $('#new_group_container').removeClass('d-none');
            $('#final_group_input').val($('#new_group_input').val().trim());
        } else {
            $('#new_group_container').addClass('d-none');
            $('#final_group_input').val(val);
        }
        autoGenerateSlug();
    }

    $('#group_select').on('change', syncGroupInput);
    $('#new_group_input').on('keyup input', function() {
        $('#final_group_input').val($(this).val().trim());
        autoGenerateSlug();
    });

    // Auto generate slug as user types permission name
    function autoGenerateSlug() {
        if (isSlugUserEdited) return;

        let name = $('#perm_name').val().trim().toLowerCase();
        if (!name) {
            $('#perm_slug').val('');
            return;
        }

        let group = $('#final_group_input').val().trim().toLowerCase();
        let groupPrefix = '';
        if (group) {
            groupPrefix = group.replace(/[^a-z0-9]/g, '_').replace(/_+/g, '_').replace(/^_|_$/g, '');
        }

        let nameSlug = name.replace(/[^a-z0-9\s._-]/g, '').replace(/\s+/g, '_');

        if (groupPrefix && !nameSlug.startsWith(groupPrefix)) {
            $('#perm_slug').val(groupPrefix + '.' + nameSlug);
        } else {
            $('#perm_slug').val(nameSlug);
        }
    }

    $('#perm_name').on('keyup input change', autoGenerateSlug);

    $('#perm_slug').on('keyup input', function() {
        isSlugUserEdited = true;
    });

    syncGroupInput();
});
</script>
@endpush
