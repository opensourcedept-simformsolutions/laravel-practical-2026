<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backup\CreateBackupRequest;
use App\Models\DatabaseBackup;
use App\Services\ActivityLogger;
use App\Services\CloudinaryService;
use App\Services\DatabaseBackupService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    protected DatabaseBackupService $backupService;
    protected CloudinaryService $cloudinaryService;

    public function __construct(DatabaseBackupService $backupService, CloudinaryService $cloudinaryService)
    {
        $this->backupService = $backupService;
        $this->cloudinaryService = $cloudinaryService;
    }

    public function index(Request $request)
    {
        $this->authorize('is-super-admin');

        $availableTables = $this->backupService->getAvailableTables();
        $isCloudinaryConfigured = $this->cloudinaryService->isConfigured();

        $query = DatabaseBackup::with('creator')->latest();

        if ($request->filled('type')) {
            if ($request->type === 'full') {
                $query->whereNull('table_name');
            } elseif ($request->type === 'single') {
                $query->whereNotNull('table_name');
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('filename', 'like', "%{$search}%")
                  ->orWhere('table_name', 'like', "%{$search}%");
            });
        }

        $backups = $query->paginate(15)->withQueryString();

        $stats = [
            'total_backups' => DatabaseBackup::count(),
            'full_backups' => DatabaseBackup::whereNull('table_name')->count(),
            'table_backups' => DatabaseBackup::whereNotNull('table_name')->count(),
            'cloudinary_count' => DatabaseBackup::where('disk', 'cloudinary')->count(),
            'total_size' => DatabaseBackup::sum('file_size'),
        ];

        return view('admin.backups.index', compact(
            'availableTables',
            'isCloudinaryConfigured',
            'backups',
            'stats'
        ));
    }

    public function store(CreateBackupRequest $request)
    {
        $this->authorize('is-super-admin');

        $tableName = $request->input('table_name') ?: null;
        $uploadCloud = $request->boolean('upload_cloud', true);

        try {
            $backup = $this->backupService->createBackup(
                tableName: $tableName,
                uploadCloud: $uploadCloud,
                userId: auth()->id()
            );

            $targetText = $tableName ? "Table '{$tableName}'" : 'Full Database';
            $cloudStatus = $backup->cloudinary_url ? ' and uploaded to Cloudinary' : '';

            ActivityLogger::log('database_backup_created', null, "Created backup for {$targetText}{$cloudStatus}");

            return redirect()->route('admin.backups.index')->with([
                'message' => "Backup for {$targetText} created successfully{$cloudStatus}.",
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            return redirect()->back()->with([
                'message' => 'Failed to create database backup: ' . $e->getMessage(),
                'status' => 'error',
            ]);
        }
    }

    public function restore(DatabaseBackup $backup)
    {
        $this->authorize('is-super-admin');

        try {
            $this->backupService->restoreBackup($backup);

            $targetText = $backup->table_name ? "table '{$backup->table_name}'" : 'full database';
            ActivityLogger::log('database_restored', null, "Restored {$targetText} from backup '{$backup->filename}'");

            return redirect()->route('admin.backups.index')->with([
                'message' => "Successfully restored {$targetText} from backup '{$backup->filename}'.",
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            return redirect()->back()->with([
                'message' => 'Database restoration failed: ' . $e->getMessage(),
                'status' => 'error',
            ]);
        }
    }

    public function download(DatabaseBackup $backup)
    {
        $this->authorize('is-super-admin');

        ActivityLogger::log('database_backup_downloaded', null, "Downloaded database backup '{$backup->filename}'");

        $relativePath = "backups/{$backup->filename}";

        if (Storage::disk('local')->exists($relativePath)) {
            return Storage::disk('local')->download($relativePath);
        }

        if ($backup->cloudinary_url) {
            return redirect()->away($backup->cloudinary_url);
        }

        return redirect()->back()->with([
            'message' => 'Backup file is no longer available for download.',
            'status' => 'error',
        ]);
    }

    public function destroy(DatabaseBackup $backup)
    {
        $this->authorize('is-super-admin');

        try {
            $filename = $backup->filename;
            $this->backupService->deleteBackup($backup);

            ActivityLogger::log('database_backup_deleted', null, "Deleted backup '{$filename}'");

            return redirect()->route('admin.backups.index')->with([
                'message' => "Backup '{$filename}' deleted successfully.",
                'status' => 'success',
            ]);
        } catch (Exception $e) {
            return redirect()->back()->with([
                'message' => 'Failed to delete backup: ' . $e->getMessage(),
                'status' => 'error',
            ]);
        }
    }
}
