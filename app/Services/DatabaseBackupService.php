<?php

namespace App\Services;

use App\Models\DatabaseBackup;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class DatabaseBackupService
{
    protected CloudinaryService $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Get list of all available tables in the database.
     */
    public function getAvailableTables(): array
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%';");
            return collect($tables)->pluck('name')->reject(fn ($name) => $name === 'migrations')->values()->toArray();
        }

        // MySQL / MariaDB
        $tables = DB::select('SHOW TABLES');
        $dbNameKey = 'Tables_in_' . DB::getDatabaseName();

        return collect($tables)
            ->map(function ($table) use ($dbNameKey) {
                return $table->$dbNameKey ?? array_values((array) $table)[0] ?? null;
            })
            ->filter()
            ->reject(fn ($name) => $name === 'migrations')
            ->values()
            ->toArray();
    }

    /**
     * Create a backup for the full database or a specific table.
     */
    public function createBackup(?string $tableName = null, bool $uploadCloud = true, ?int $userId = null): DatabaseBackup
    {
        $timestamp = now()->format('Y_m_d_His');
        $prefix = $tableName ? "backup_table_{$tableName}" : "backup_full_db";
        $fileName = "{$prefix}_{$timestamp}.sql";

        $sqlContent = $this->generateSqlDump($tableName);

        // Ensure storage directory exists
        if (! Storage::disk('local')->exists('backups')) {
            Storage::disk('local')->makeDirectory('backups');
        }

        $relativePath = "backups/{$fileName}";
        Storage::disk('local')->put($relativePath, $sqlContent);

        $localAbsolutePath = Storage::disk('local')->path($relativePath);
        $fileSize = filesize($localAbsolutePath);

        $cloudinaryUrl = null;
        $cloudinaryPublicId = null;
        $disk = 'local';

        if ($uploadCloud && $this->cloudinaryService->isConfigured()) {
            $cloudResult = $this->cloudinaryService->uploadRaw($localAbsolutePath, 'database_backups');
            if ($cloudResult) {
                $cloudinaryUrl = $cloudResult['url'];
                $cloudinaryPublicId = $cloudResult['public_id'];
                $disk = 'cloudinary';
            }
        }

        return DatabaseBackup::create([
            'filename' => $fileName,
            'table_name' => $tableName,
            'file_size' => $fileSize,
            'disk' => $disk,
            'cloudinary_url' => $cloudinaryUrl,
            'cloudinary_public_id' => $cloudinaryPublicId,
            'created_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Restore a database or single table from a backup record.
     */
    public function restoreBackup(DatabaseBackup $backup): bool
    {
        $relativePath = "backups/{$backup->filename}";
        $sqlContent = null;

        // Try local storage first
        if (Storage::disk('local')->exists($relativePath)) {
            $sqlContent = Storage::disk('local')->get($relativePath);
        } elseif (! empty($backup->cloudinary_url)) {
            // Download from Cloudinary
            $response = Http::get($backup->cloudinary_url);
            if ($response->successful()) {
                $sqlContent = $response->body();
                // Cache locally for convenience
                Storage::disk('local')->put($relativePath, $sqlContent);
            }
        }

        if (empty($sqlContent)) {
            throw new Exception("SQL dump content for backup ID {$backup->id} could not be loaded.");
        }

        DB::beginTransaction();
        try {
            $driver = DB::getDriverName();

            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            }

            DB::unprepared($sqlContent);

            if ($driver === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } elseif ($driver === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            }

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Backup restoration failed for ID {$backup->id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a backup file and database record.
     */
    public function deleteBackup(DatabaseBackup $backup): bool
    {
        // Delete local file
        $relativePath = "backups/{$backup->filename}";
        if (Storage::disk('local')->exists($relativePath)) {
            Storage::disk('local')->delete($relativePath);
        }

        // Delete from Cloudinary if public ID exists
        if (! empty($backup->cloudinary_public_id)) {
            $this->cloudinaryService->deleteRaw($backup->cloudinary_public_id);
        }

        return (bool) $backup->delete();
    }

    /**
     * Generate SQL dump content for full database or single table using mysqldump or PHP fallback.
     */
    protected function generateSqlDump(?string $targetTable = null): string
    {
        $defaultConn = DB::getDefaultConnection();
        $connConfig = config("database.connections.{$defaultConn}", []);
        $driver = $connConfig['driver'] ?? DB::getDriverName();

        // Use mysqldump CLI binary when running on MySQL / MariaDB connection (skip during PHPUnit testing)
        if ($driver === 'mysql' && ! app()->environment('testing')) {
            $host = $connConfig['host'] ?? '127.0.0.1';
            $port = $connConfig['port'] ?? '3306';
            $user = $connConfig['username'] ?? 'root';
            $pass = $connConfig['password'] ?? '';
            $database = $connConfig['database'] ?? '';

            $passArg = ($pass !== '' && $pass !== null) ? '--password=' . escapeshellarg($pass) : '';
            $tableArg = $targetTable ? escapeshellarg($targetTable) : '';

            $cmd = sprintf(
                'mysqldump --host=%s --port=%s --user=%s %s %s %s',
                escapeshellarg($host),
                escapeshellarg($port),
                escapeshellarg($user),
                $passArg,
                escapeshellarg($database),
                $tableArg
            );

            try {
                $process = Process::timeout(5)->run($cmd);
                if ($process->successful() && ! empty($process->output())) {
                    return $process->output();
                }
            } catch (Exception $e) {
                Log::warning('mysqldump CLI command failed, falling back to PHP generator: ' . $e->getMessage());
            }
        }

        // PHP Pure Fallback Generator (works on SQLite, MySQL, and environment without mysqldump binary)
        $tables = $targetTable ? [$targetTable] : $this->getAvailableTables();
        $output = "-- Database Backup Dump\n";
        $output .= "-- Generated At: " . now()->toDateTimeString() . "\n";
        $output .= "-- Target: " . ($targetTable ?: 'Full Database') . "\n\n";

        if ($driver === 'mysql') {
            $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
        } elseif ($driver === 'sqlite') {
            $output .= "PRAGMA foreign_keys = OFF;\n\n";
        }

        foreach ($tables as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $output .= "-- --------------------------------------------------------\n";
            $output .= "-- Table structure for `{$table}`\n";
            $output .= "-- --------------------------------------------------------\n\n";

            if ($driver === 'sqlite') {
                $createSql = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name=?", [$table])[0]->sql ?? null;
                if ($createSql) {
                    $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
                    $output .= $createSql . ";\n\n";
                }
            } else {
                $showCreate = DB::select("SHOW CREATE TABLE `{$table}`");
                $createSqlKey = 'Create Table';
                $createSql = $showCreate[0]->$createSqlKey ?? array_values((array) $showCreate[0])[1] ?? null;

                if ($createSql) {
                    $output .= "DROP TABLE IF EXISTS `{$table}`;\n";
                    $output .= $createSql . ";\n\n";
                }
            }

            $output .= "-- Dumping data for table `{$table}`\n\n";

            DB::table($table)->orderBy($this->getPrimaryKey($table))->chunk(500, function ($rows) use (&$output, $table) {
                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $values = array_map(function ($val) {
                        if ($val === null) {
                            return 'NULL';
                        }
                        $escaped = addslashes((string) $val);
                        $escaped = str_replace("\n", "\\n", $escaped);
                        $escaped = str_replace("\r", "\\r", $escaped);
                        return "'{$escaped}'";
                    }, array_values($rowArray));

                    $columns = array_map(fn ($col) => "`{$col}`", array_keys($rowArray));

                    $output .= "INSERT INTO `{$table}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
            });

            $output .= "\n";
        }

        if ($driver === 'mysql') {
            $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
        } elseif ($driver === 'sqlite') {
            $output .= "PRAGMA foreign_keys = ON;\n";
        }

        return $output;
    }

    /**
     * Helper to get primary key column for chunk ordering.
     */
    protected function getPrimaryKey(string $table): string
    {
        try {
            $columns = Schema::getColumnListing($table);
            if (in_array('id', $columns)) {
                return 'id';
            }
            return $columns[0] ?? 'created_at';
        } catch (Exception $e) {
            return 'id';
        }
    }
}
