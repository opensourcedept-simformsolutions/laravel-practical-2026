@extends('layouts.app')

@section('title', 'Manage Permissions')

@section('content')
<div class="container-fluid px-4 py-3">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-shield-lock-fill text-primary"></i> User Permissions Management
            </h4>
            <p class="text-muted small mb-0">
                @if (auth()->user()->isSuperAdmin())
                    Super Admin Control: Assign & override specific permissions for users across all societies.
                @else
                    Society Admin Control: Manage permissions for users within your society.
                @endif
            </p>
        </div>
    </div>

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            {{-- Filter Form --}}
            <form method="GET" action="{{ route('admin.permissions.index') }}" class="row g-3 mb-4 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary small">Search User</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control form-control-sm border-start-0" placeholder="Search by name or email..." value="{{ request('search') }}">
                    </div>
                </div>

                @if (auth()->user()->isSuperAdmin())
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-secondary small">Society</label>
                        <select name="society_id" class="form-select form-select-sm">
                            <option value="">All Societies</option>
                            @foreach ($societies as $society)
                                <option value="{{ $society->id }}" @selected(request('society_id') == $society->id)>
                                    {{ $society->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-3">
                    <label class="form-label fw-semibold text-secondary small">Role</label>
                    <select name="role" class="form-select form-select-sm">
                        <option value="">All Roles</option>
                        <option value="admin" @selected(request('role') == 'admin')>Admin</option>
                        <option value="resident" @selected(request('role') == 'resident')>Resident</option>
                        <option value="gatekeeper" @selected(request('role') == 'gatekeeper')>Gatekeeper</option>
                        @if (auth()->user()->isSuperAdmin())
                            <option value="super_admin" @selected(request('role') == 'super_admin')>Super Admin</option>
                        @endif
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>

            {{-- Users Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            @if (auth()->user()->isSuperAdmin())
                                <th>Society</th>
                            @endif
                            <th>Permission Overrides</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            @php
                                $directGrants = $user->permissions->filter(fn($p) => $p->pivot->is_granted)->count();
                                $directRevocations = $user->permissions->filter(fn($p) => !$p->pivot->is_granted)->count();
                            @endphp
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $user->name }}</div>
                                            <div class="text-muted small">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $roleBadge = match($user->role?->name) {
                                            'super_admin' => 'bg-danger',
                                            'admin' => 'bg-primary',
                                            'resident' => 'bg-info text-dark',
                                            'gatekeeper' => 'bg-warning text-dark',
                                            default => 'bg-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $roleBadge }} px-2 py-1 fs-7">
                                        {{ ucfirst($user->role?->name ?? 'None') }}
                                    </span>
                                </td>
                                @if (auth()->user()->isSuperAdmin())
                                    <td>
                                        <span class="text-secondary small">
                                            {{ $user->society?->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                @endif
                                <td>
                                    @if ($user->isSuperAdmin())
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                            <i class="bi bi-check-all me-1"></i> Full Super Admin Access
                                        </span>
                                    @elseif ($directGrants == 0 && $directRevocations == 0)
                                        <span class="badge bg-light text-muted border px-2 py-1">
                                            <i class="bi bi-shield-check me-1"></i> Using Role Defaults
                                        </span>
                                    @else
                                        <div class="d-flex gap-1 flex-wrap">
                                            @if ($directGrants > 0)
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                                    +{{ $directGrants }} Custom {{ Str::plural('Grant', $directGrants) }}
                                                </span>
                                            @endif
                                            @if ($directRevocations > 0)
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                                                    -{{ $directRevocations }} Explicit {{ Str::plural('Revocation', $directRevocations) }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($user->isSuperAdmin() && !auth()->user()->isSuperAdmin())
                                        <span class="text-muted small">N/A</span>
                                    @else
                                        <a href="{{ route('admin.permissions.edit', $user) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-sliders"></i> Manage
                                        </a>
                                        @if ($directGrants > 0 || $directRevocations > 0)
                                            <form action="{{ route('admin.permissions.reset', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Reset {{ $user->name }}\'s permissions to role defaults?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-secondary ms-1" title="Reset to Role Defaults">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No users found matching the selected criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
