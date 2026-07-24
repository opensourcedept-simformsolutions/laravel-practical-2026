@extends('layouts.app')

@section('title', 'System Permissions Management')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-key-fill text-primary"></i> System Permissions Management
            </h4>
            <p class="text-muted small mb-0">
                Super Admin Module: Define, edit, and delete system permission definitions.
            </p>
        </div>

        <a href="{{ route('admin.system-permissions.create') }}" class="btn btn-primary btn-sm rounded-3 px-3 shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Create Permission
        </a>
    </div>

    {{-- Main Card --}}
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-body">
            {{-- Filter Form --}}
            <form method="GET" action="{{ route('admin.system-permissions.index') }}" class="row g-3 mb-4 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-semibold text-secondary small">Search Permission</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control form-control-sm border-start-0" placeholder="Search by name, slug, or category..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold text-secondary small">Category / Group</label>
                    <select name="group" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach ($groups as $group)
                            <option value="{{ $group }}" @selected(request('group') == $group)>
                                {{ $group }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.system-permissions.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Permission Name</th>
                            <th>Slug (Key)</th>
                            <th>Category</th>
                            <th>Active Usage</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($permissions as $permission)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $permission->name }}</div>
                                    <div class="text-muted fs-8">{{ $permission->description ?? 'No description provided' }}</div>
                                </td>
                                <td>
                                    <code class="px-2 py-1 bg-light border rounded text-dark fs-8">{{ $permission->slug }}</code>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1">
                                        <i class="bi bi-folder2 me-1"></i> {{ $permission->group }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap fs-8">
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
                                            {{ $permission->roles_count }} {{ Str::plural('Role', $permission->roles_count) }}
                                        </span>
                                        <span class="badge bg-purple bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                                            {{ $permission->users_count }} Direct {{ Str::plural('User', $permission->users_count) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <a href="{{ route('admin.system-permissions.edit', $permission) }}" class="btn btn-sm btn-outline-primary" title="Edit Permission">
                                            <i class="bi bi-pencil-square"></i> Edit
                                        </a>

                                        <form action="{{ route('admin.system-permissions.destroy', $permission) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete permission \'{{ $permission->name }}\'?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Permission">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No system permissions found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $permissions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
