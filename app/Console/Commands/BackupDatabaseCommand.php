<?php

namespace App\Console\Commands;

use App\Services\ActivityLogger;
use App\Services\DatabaseBackupService;
use Exception;
use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup {--table= : Specific database table to backup} {--no-cloud : Skip Cloudinary upload}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Take a backup of the full database or a specific table and upload to Cloudinary';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $tableName = $this->option('table');
        $skipCloud = $this->option('no-cloud');

        $this->info("Starting database backup process...");
        if ($tableName) {
            $this->info("Target Table: {$tableName}");
        } else {
            $this->info("Target: Full Database Dump");
        }

        try {
            $backup = $backupService->createBackup(
                tableName: $tableName,
                uploadCloud: ! $skipCloud
            );

            $targetText = $tableName ? "table '{$tableName}'" : 'full database';
            $cloudStatus = $backup->cloudinary_url ? ' with Cloudinary upload' : '';
            ActivityLogger::log('database_backup_created', null, "CLI backup created for {$targetText}{$cloudStatus}: {$backup->filename}");

            $this->info("✓ Backup created successfully!");
            $this->line("  Filename: {$backup->filename}");
            $this->line("  Size: {$backup->formatted_size}");
            $this->line("  Storage Disk: {$backup->disk}");

            if ($backup->cloudinary_url) {
                $this->info("✓ Cloudinary URL: {$backup->cloudinary_url}");
            }

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("Backup failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
