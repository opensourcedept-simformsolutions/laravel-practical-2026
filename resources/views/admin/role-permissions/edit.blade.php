@extends('layouts.app')

@section('title', 'Edit Role Permissions - ' . ucfirst($role->name))

@push('styles')
<style>
.perm-role-item {
    transition: all 0.15s ease-in-out;
}
.perm-role-item:hover {
    background-color: #f8fafc !important;
    border-color: #cbd5e1 !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Breadcrumb & Back --}}
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('admin.role-permissions.index') }}">Role Defaults</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ ucfirst($role->name) }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-primary"></i> Edit Default Permissions for Role: {{ ucfirst($role->name) }}
            </h4>
        </div>
        <a href="{{ route('admin.role-permissions.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Roles List
        </a>
    </div>

    {{-- Master Form --}}
    <form action="{{ route('admin.role-permissions.update', $role) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Toolbar --}}
        <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white">
            <div class="card-body py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-2 text-muted small">
                    <i class="bi bi-info-circle text-primary"></i>
                    <span>Permissions checked here will be granted by default to all users assigned to the <strong>{{ ucfirst($role->name) }}</strong> role.</span>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" id="btnSelectAll">
                        <i class="bi bi-check-all me-1"></i> Select All
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnDeselectAll">
                        <i class="bi bi-x me-1"></i> Deselect All
                    </button>
                </div>
            </div>
        </div>

        {{-- Grouped Permissions Grid --}}
        <div class="row g-4 mb-4">
            @foreach ($groupedPermissions as $group => $permissions)
                @php
                    $groupId = 'group_' . Str::slug($group);
                @endphp
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 rounded-3 h-100">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-folder2-open text-primary"></i> {{ $group }}
                            </h6>
                            <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 select-group-btn" data-target="{{ $groupId }}">
                                Toggle Group
                            </button>
                        </div>

                        <div class="card-body p-3 {{ $groupId }}">
                            <div class="row g-3">
                                @foreach ($permissions as $permission)
                                    @php
                                        $isAssigned = in_array($permission->id, $assignedPermissionIds);
                                    @endphp
                                    <div class="col-12">
                                        <div class="p-3 border rounded-3 bg-white shadow-sm d-flex align-items-center justify-content-between gap-3 perm-role-item">
                                            <label class="mb-0 cursor-pointer flex-grow-1" for="perm_switch_{{ $permission->id }}">
                                                <div class="fw-bold text-dark mb-0">{{ $permission->name }}</div>
                                                <div class="text-muted small fs-8">{{ $permission->description ?? $permission->slug }}</div>
                                            </label>
                                            <div class="form-check form-switch m-0 p-0 flex-shrink-0">
                                                <input class="form-check-input perm-switch cursor-pointer m-0"
                                                    type="checkbox"
                                                    role="switch"
                                                    name="permissions[]"
                                                    value="{{ $permission->id }}"
                                                    id="perm_switch_{{ $permission->id }}"
                                                    style="width: 2.6em; height: 1.3em;"
                                                    @checked($isAssigned)>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Save Bar --}}
        <div class="card shadow-sm border-0 rounded-3 sticky-bottom bg-white py-3 px-4 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.role-permissions.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                    <i class="bi bi-check-lg me-1"></i> Save Role Permissions
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#btnSelectAll').click(function() {
        $('.perm-switch').prop('checked', true);
    });

    $('#btnDeselectAll').click(function() {
        $('.perm-switch').prop('checked', false);
    });

    $('.select-group-btn').click(function() {
        let targetClass = '.' + $(this).data('target');
        let switches = $(targetClass).find('.perm-switch');
        let allChecked = switches.filter(':checked').length === switches.length;
        switches.prop('checked', !allChecked);
    });
});
</script>
@endpush
