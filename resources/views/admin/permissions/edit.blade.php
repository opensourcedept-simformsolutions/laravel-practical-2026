@extends('layouts.app')

@section('title', 'Edit User Permissions - ' . $user->name)

@push('styles')
<style>
.perm-btn-group .btn-perm-opt {
    font-size: 0.8125rem;
    font-weight: 600;
    transition: all 0.15s ease-in-out;
}
.btn-check:checked + .btn-outline-secondary {
    background-color: #475569 !important;
    border-color: #475569 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 4px rgba(71, 85, 105, 0.25);
}
.btn-check:checked + .btn-outline-success {
    background-color: #10b981 !important;
    border-color: #10b981 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
}
.btn-check:checked + .btn-outline-danger {
    background-color: #ef4444 !important;
    border-color: #ef4444 !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3);
}
.perm-item-row {
    transition: background-color 0.15s ease-in-out;
}
.perm-item-row:hover {
    background-color: #f8fafc;
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
                    <li class="breadcrumb-item"><a href="{{ route('admin.permissions.index') }}">User Permissions</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
                </ol>
            </nav>
            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock-fill text-primary"></i> Manage User Permissions for {{ $user->name }}
            </h4>
        </div>
        <div class="d-flex gap-2">
            @if (auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.role-permissions.index') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-shield-check me-1"></i> Role Defaults
                </a>
            @endif
            <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    {{-- User Profile Header --}}
    <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-md-auto">
                    <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-3" style="width: 58px; height: 58px;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </div>
                <div class="col-md">
                    <h5 class="fw-bold mb-1 text-dark">{{ $user->name }}</h5>
                    <div class="d-flex flex-wrap gap-3 text-muted small">
                        <span><i class="bi bi-envelope me-1"></i>{{ $user->email }}</span>
                        <span><i class="bi bi-shield me-1"></i>Role: <strong class="text-dark">{{ ucfirst($user->role?->name ?? 'None') }}</strong></span>
                        @if ($user->society)
                            <span><i class="bi bi-building me-1"></i>Society: <strong class="text-dark">{{ $user->society->name }}</strong></span>
                        @endif
                    </div>
                </div>
                <div class="col-md-auto text-end">
                    <form action="{{ route('admin.permissions.reset', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Reset all custom permissions for {{ $user->name }} back to role defaults?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset to Role Defaults
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <form action="{{ route('admin.permissions.update', $user) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Toolbar & Mode Legend --}}
        <div class="card border-0 bg-white shadow-sm rounded-3 mb-4">
            <div class="card-body py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3 text-muted small">
                    <span class="fw-bold text-dark"><i class="bi bi-info-circle text-primary me-1"></i> Mode Legend:</span>
                    <span class="d-flex align-items-center gap-1"><span class="badge bg-secondary">Inherited</span> Use Role Default</span>
                    <span class="d-flex align-items-center gap-1"><span class="badge bg-success">Grant</span> Force Allow</span>
                    <span class="d-flex align-items-center gap-1"><span class="badge bg-danger">Revoke</span> Force Deny</span>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnResetAllInherited">
                        <i class="bi bi-arrow-repeat me-1"></i> Set All to Inherited
                    </button>
                </div>
            </div>
        </div>

        {{-- Grouped Permissions Cards --}}
        <div class="row g-4 mb-4">
            @foreach ($groupedPermissions as $group => $permissions)
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 rounded-3 h-100">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="bi bi-folder2-open text-primary"></i> {{ $group }}
                            </h6>
                            <span class="badge bg-light text-muted border">{{ $permissions->count() }} {{ Str::plural('permission', $permissions->count()) }}</span>
                        </div>

                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                @foreach ($permissions as $permission)
                                    @php
                                        $isRoleDefaultAllowed = in_array($permission->id, $rolePermissionIds);
                                        $directRecord = $userDirectPermissions->get($permission->id);
                                        $isDelegatedAllowed = in_array($permission->id, $allowedPermissionIds);

                                        $currentSetting = 'inherited';
                                        if ($directRecord !== null) {
                                            $currentSetting = $directRecord->pivot->is_granted ? 'granted' : 'revoked';
                                        }
                                    @endphp
                                    <li class="list-group-item p-3 border-bottom perm-item-row @if(!$isDelegatedAllowed) opacity-75 bg-light @endif">
                                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3">
                                            <div class="me-2">
                                                <div class="fw-bold text-dark d-flex align-items-center gap-2 flex-wrap">
                                                    {{ $permission->name }}
                                                    @if ($isRoleDefaultAllowed)
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 fs-8" title="Role Default: Allowed">
                                                            <i class="bi bi-check-circle-fill me-1"></i> Role Allowed
                                                        </span>
                                                    @else
                                                        <span class="badge bg-light text-muted border fs-8" title="Role Default: Denied">
                                                            <i class="bi bi-dash-circle me-1"></i> Role Denied
                                                        </span>
                                                    @endif

                                                    @if (!$isDelegatedAllowed)
                                                        <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-50 fs-8" title="You cannot delegate permissions you do not possess">
                                                            <i class="bi bi-lock-fill me-1"></i> Beyond Your Access
                                                        </span>
                                                    @endif
                                                </div>
                                                <div class="text-muted small mt-1">
                                                    {{ $permission->description ?? $permission->slug }}
                                                </div>
                                            </div>

                                            {{-- Clean Bootstrap 5 Button Group --}}
                                            <div class="btn-group btn-group-sm perm-btn-group flex-shrink-0" role="group" aria-label="Permission state">
                                                <input type="radio"
                                                    class="btn-check perm-radio-inherited"
                                                    name="permissions[{{ $permission->id }}]"
                                                    id="perm_{{ $permission->id }}_inherited"
                                                    value="inherited"
                                                    autocomplete="off"
                                                    @checked($currentSetting === 'inherited')
                                                    @disabled(!$isDelegatedAllowed)>
                                                <label class="btn btn-outline-secondary btn-perm-opt px-3 py-1.5" for="perm_{{ $permission->id }}_inherited">
                                                    <i class="bi bi-arrow-repeat me-1"></i> Inherited
                                                </label>

                                                <input type="radio"
                                                    class="btn-check perm-radio-granted"
                                                    name="permissions[{{ $permission->id }}]"
                                                    id="perm_{{ $permission->id }}_granted"
                                                    value="granted"
                                                    autocomplete="off"
                                                    @checked($currentSetting === 'granted')
                                                    @disabled(!$isDelegatedAllowed)>
                                                <label class="btn btn-outline-success btn-perm-opt px-3 py-1.5" for="perm_{{ $permission->id }}_granted">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Grant
                                                </label>

                                                <input type="radio"
                                                    class="btn-check perm-radio-revoked"
                                                    name="permissions[{{ $permission->id }}]"
                                                    id="perm_{{ $permission->id }}_revoked"
                                                    value="revoked"
                                                    autocomplete="off"
                                                    @checked($currentSetting === 'revoked')
                                                    @disabled(!$isDelegatedAllowed)>
                                                <label class="btn btn-outline-danger btn-perm-opt px-3 py-1.5" for="perm_{{ $permission->id }}_revoked">
                                                    <i class="bi bi-x-circle-fill me-1"></i> Revoke
                                                </label>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Save Bar --}}
        <div class="card shadow-sm border-0 rounded-3 sticky-bottom bg-white py-3 px-4 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary px-4 fw-semibold shadow-sm">
                    <i class="bi bi-check-lg me-1"></i> Save Permission Changes
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#btnResetAllInherited').click(function() {
        $('.perm-radio-inherited').prop('checked', true);
    });
});
</script>
@endpush
