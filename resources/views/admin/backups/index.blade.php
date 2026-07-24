@extends('layouts.app')

@section('title', 'Database Backups & Cloud Storage')

@section('content')
<div class="container-fluid px-4 py-3">

    {{-- Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <i class="bi bi-database-fill-gear text-primary"></i> Database Backups & Cloud Storage
            </h4>
            <p class="text-muted small mb-0">
                Super Admin Utility: Create, restore, and manage full database or single-table dumps stored locally or in Cloudinary.
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if ($isCloudinaryConfigured)
                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill d-flex align-items-center gap-1 fs-8">
                    <i class="bi bi-cloud-check-fill"></i> Cloudinary Connected
                </span>
            @else
                <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-50 px-3 py-2 rounded-pill d-flex align-items-center gap-1 fs-8" title="CLOUDINARY_URL environment variable is not configured">
                    <i class="bi bi-hdd-fill"></i> Local Storage Only
                </span>
            @endif
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Backups</div>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['total_backups'] }}</h3>
                    </div>
                    <div class="avatar bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-database fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Full DB Dumps</div>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['full_backups'] }}</h3>
                    </div>
                    <div class="avatar bg-info bg-opacity-10 text-info rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-stack fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Single Table Dumps</div>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['table_backups'] }}</h3>
                    </div>
                    <div class="avatar bg-purple bg-opacity-10 text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-table fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Cloudinary Uploads</div>
                        <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['cloudinary_count'] }}</h3>
                    </div>
                    <div class="avatar bg-success bg-opacity-10 text-success rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-cloud-arrow-up-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create Backup Control Card --}}
    <div class="card shadow-sm border-0 rounded-3 mb-4 bg-white">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="bi bi-plus-circle-fill text-primary"></i> Create New Backup
            </h6>
        </div>

        <div class="card-body p-4">
            <form action="{{ route('admin.backups.store') }}" method="POST" id="createBackupForm">
                @csrf

                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold text-dark">Backup Target (Full Database or Single Table)</label>
                        <select name="table_name" class="form-select">
                            <option value="">Full Database (All Tables)</option>
                            <optgroup label="Available Tables">
                                @foreach ($availableTables as $table)
                                    <option value="{{ $table }}">Single Table: {{ $table }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" role="switch" name="upload_cloud" id="upload_cloud_switch" value="1" @checked($isCloudinaryConfigured) @disabled(!$isCloudinaryConfigured)>
                            <label class="form-check-label fw-semibold text-dark cursor-pointer" for="upload_cloud_switch">
                                Upload to Cloudinary Storage
                            </label>
                        </div>
                        <div class="text-muted fs-8">
                            @if ($isCloudinaryConfigured)
                                Backs up file locally and syncs to Cloudinary cloud URL.
                            @else
                                Cloudinary is disabled. Set <code>CLOUDINARY_URL</code> in <code>.env</code> to enable.
                            @endif
                        </div>
                    </div>

                    <div class="col-md-3 text-end">
                        <button type="submit" class="btn btn-primary w-100 fw-semibold shadow-sm py-2" id="btnCreateBackup">
                            <i class="bi bi-database-fill-down me-1"></i> Generate Backup
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Backups List Table --}}
    <div class="card shadow-sm border-0 rounded-3 bg-white">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="bi bi-clock-history text-primary"></i> Backup History Logs
            </h6>

            {{-- Filter Form --}}
            <form method="GET" action="{{ route('admin.backups.index') }}" class="d-flex gap-2 align-items-center">
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="full" @selected(request('type') == 'full')>Full Database</option>
                    <option value="single" @selected(request('type') == 'single')>Single Table</option>
                </select>

                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Search filename or table..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i></button>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Backup Target</th>
                            <th>Filename</th>
                            <th>File Size</th>
                            <th>Storage Disk</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($backups as $backup)
                            <tr>
                                <td>
                                    @if ($backup->isFullBackup())
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1.5">
                                            <i class="bi bi-database me-1"></i> Full Database
                                        </span>
                                    @else
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1.5">
                                            <i class="bi bi-table me-1"></i> Table: <strong>{{ $backup->table_name }}</strong>
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <code class="px-2 py-1 bg-light border rounded text-dark fs-8">{{ $backup->filename }}</code>
                                </td>
                                <td class="fw-semibold text-secondary fs-8">
                                    {{ $backup->formatted_size }}
                                </td>
                                <td>
                                    @if ($backup->cloudinary_url)
                                        <a href="{{ $backup->cloudinary_url }}" target="_blank" class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 text-decoration-none px-2.5 py-1.5" title="View/Download raw asset on Cloudinary">
                                            <i class="bi bi-cloud-check-fill me-1"></i> Cloudinary <i class="bi bi-box-arrow-up-right ms-1 fs-9"></i>
                                        </a>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1.5">
                                            <i class="bi bi-hdd-fill me-1"></i> Local Disk
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="small text-dark fw-semibold">{{ $backup->creator?->name ?? 'System / Artisan' }}</span>
                                </td>
                                <td class="small text-muted">
                                    {{ $backup->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        {{-- Download --}}
                                        <a href="{{ route('admin.backups.download', $backup) }}" class="btn btn-sm btn-outline-primary" title="Download SQL Dump">
                                            <i class="bi bi-download"></i> Download
                                        </a>

                                        {{-- Restore --}}
                                        <form action="{{ route('admin.backups.restore', $backup) }}" method="POST" class="d-inline" onsubmit="return confirm('WARNING: Are you sure you want to restore {{ $backup->table_name ? 'table \''.$backup->table_name.'\'' : 'the FULL DATABASE' }} from \'{{ $backup->filename }}\'? Existing data will be overwritten!');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning text-dark" title="Restore database/table from backup">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </form>

                                        {{-- Delete --}}
                                        <form action="{{ route('admin.backups.destroy', $backup) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete backup \'{{ $backup->filename }}\'?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete backup file">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-database-fill-x fs-2 d-block mb-2"></i>
                                    No database backups found. Click "Generate Backup" above or run <code>php artisan db:backup</code>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                {{ $backups->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#createBackupForm').on('submit', function() {
        $('#btnCreateBackup').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Generating...');
    });
});
</script>
@endpush
