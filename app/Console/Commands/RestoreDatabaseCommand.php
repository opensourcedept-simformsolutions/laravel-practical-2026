<?php

namespace App\Console\Commands;

use App\Models\DatabaseBackup;
use App\Services\ActivityLogger;
use App\Services\DatabaseBackupService;
use Exception;
use Illuminate\Console\Command;

class RestoreDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:restore {id : ID of the backup record to restore}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore database or specific table from a backup record ID';

    /**
     * Execute the console command.
     */
    public function handle(DatabaseBackupService $backupService): int
    {
        $backupId = $this->argument('id');
        $backup = DatabaseBackup::find($backupId);

        if (! $backup) {
            $this->error("Backup record with ID {$backupId} not found.");
            return Command::FAILURE;
        }

        $targetText = $backup->table_name ? "table '{$backup->table_name}'" : "full database";
        if (! $this->confirm("Are you sure you want to restore {$targetText} from backup '{$backup->filename}'? Current data will be overwritten.")) {
            $this->info("Restoration cancelled.");
            return Command::SUCCESS;
        }

        try {
            $this->info("Restoring {$targetText}...");
            $backupService->restoreBackup($backup);
            ActivityLogger::log('database_restored', null, "CLI restored {$targetText} from backup '{$backup->filename}'");

            $this->info("✓ Database restoration completed successfully!");

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->error("Restoration failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
