@extends('layouts.app')

@section('title', 'Default Role Permissions')

@section('content')
<div class="container-fluid px-4 py-3">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-primary"></i> Default Role Permissions
            </h4>
            <p class="text-muted small mb-0">
                Super Admin Configuration: Define default permissions inherited by users assigned to each role.
            </p>
        </div>
    </div>

    {{-- Roles Grid --}}
    <div class="row g-4">
        @foreach ($roles as $role)
            @php
                $assignedCount = $role->permissions->count();
                $percentage = $totalPermissionsCount > 0 ? round(($assignedCount / $totalPermissionsCount) * 100) : 0;

                $roleIcon = match($role->name) {
                    'super_admin' => 'bi-shield-shaded text-danger',
                    'admin' => 'bi-building-fill-gear text-primary',
                    'resident' => 'bi-house-heart-fill text-info',
                    'gatekeeper' => 'bi-person-badge-fill text-warning',
                    default => 'bi-shield-lock-fill text-secondary'
                };
            @endphp
            <div class="col-md-6 col-lg-3">
                <div class="card shadow-sm border-0 rounded-3 h-100 position-relative overflow-hidden">
                    <div class="card-body p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="avatar bg-light rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi {{ $roleIcon }} fs-3"></i>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1 fs-7">
                                    {{ $assignedCount }} / {{ $totalPermissionsCount }} Perms
                                </span>
                            </div>

                            <h5 class="fw-bold text-dark mb-1">{{ ucfirst($role->name) }}</h5>
                            <p class="text-muted small mb-3">
                                System Role Default Configuration
                            </p>

                            {{-- Progress Bar --}}
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $percentage }}%" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>

                            {{-- Sample permissions tags --}}
                            <div class="d-flex flex-wrap gap-1 mb-4">
                                @forelse ($role->permissions->take(4) as $perm)
                                    <span class="badge bg-light text-secondary border fs-8">
                                        {{ $perm->name }}
                                    </span>
                                @empty
                                    <span class="text-muted small italic">No default permissions</span>
                                @endforelse
                                @if ($role->permissions->count() > 4)
                                    <span class="badge bg-light text-muted border fs-8">
                                        +{{ $role->permissions->count() - 4 }} more
                                    </span>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('admin.role-permissions.edit', $role) }}" class="btn btn-outline-primary btn-sm w-100 fw-semibold d-flex align-items-center justify-content-center gap-1">
                            <i class="bi bi-sliders me-1"></i> Configure Role
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
