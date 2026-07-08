<?php

namespace App\Jobs;

use App\Models\BulkImport;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Notifications\BulkImportStatusNotification;
use App\Notifications\ResidentWelcomeNotification;
use App\Services\ActivityLogger;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessBulkImport implements ShouldQueue
{
    use Queueable;

    public $timeout = 600; // 10 minutes timeout for larger imports

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $bulkImportId,
        public int $societyId,
        public int $operatorId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $bulkImport = BulkImport::findOrFail($this->bulkImportId);
        $jsonFilename = 'validated_'.$bulkImport->id.'.json';
        $jsonPath = 'temp_bulk_uploads/'.$jsonFilename;

        if (! Storage::exists($jsonPath)) {
            Log::error("Bulk import data file not found during background processing: {$jsonPath}");
            $this->notifyFailure($bulkImport, 'Validated data file not found.');

            return;
        }

        $bulkImport->update(['status' => 'processing']);
        $validatedRows = json_decode(Storage::get($jsonPath), true);
        $residentRoleId = Role::where('name', 'resident')->value('id');

        $operator = User::findOrFail($this->operatorId);
        $superAdmins = User::whereHas('role', function ($q) {
            $q->where('name', 'super_admin');
        })->get();

        $chunks = array_chunk($validatedRows, 100);
        $importedCount = 0;
        $createdUsers = [];

        try {
            foreach ($chunks as $chunkIndex => $chunk) {
                DB::beginTransaction();
                try {
                    foreach ($chunk as $row) {
                        if (User::where('email', $row['email'])->exists()) {
                            throw new Exception("Email '{$row['email']}' was registered during background processing.");
                        }

                        $user = User::create([
                            'name' => $row['name'],
                            'email' => $row['email'],
                            'phone' => $row['phone'],
                            'password' => Str::password(32),
                            'role_id' => $residentRoleId,
                            'society_id' => $this->societyId,
                        ]);

                        $resident = Resident::create([
                            'user_id' => $user->id,
                            'flat_id' => $row['flat_id'],
                            'resident_type' => $row['resident_type'],
                        ]);

                        ActivityLogger::log('create', $resident, "Resident {$row['name']} was imported into flat {$row['wing']}-{$row['flat_number']}.");
                        $createdUsers[] = $user;
                        $importedCount++;
                    }

                    DB::commit();

                    // Update progress periodically
                    $bulkImport->update(['imported_rows' => $importedCount]);

                } catch (\Throwable $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            $bulkImport->update([
                'status' => 'completed',
            ]);

            // Send notifications to new residents
            foreach ($createdUsers as $u) {
                try {
                    $u->notify(new ResidentWelcomeNotification($u));
                } catch (\Throwable $e) {
                    Log::error("Failed to notify imported resident user {$u->id}: ".$e->getMessage());
                }
            }

            // Cleanup temp files
            Storage::delete($jsonPath);
            Storage::delete('temp_bulk_uploads/'.$bulkImport->filename);

            // Notify Operator and SuperAdmins of Success
            $notification = new BulkImportStatusNotification(
                $bulkImport->original_filename,
                'completed',
                $bulkImport->total_rows,
                $importedCount
            );

            $operator->notify($notification);
            foreach ($superAdmins as $sa) {
                $sa->notify($notification);
            }

        } catch (\Throwable $e) {
            Log::error('Background bulk import error: '.$e->getMessage(), ['exception' => $e]);

            $this->notifyFailure($bulkImport, $e->getMessage());
        }
    }

    /**
     * Notify failure to operator and super admins.
     */
    protected function notifyFailure(BulkImport $bulkImport, string $errorMessage): void
    {
        $bulkImport->update(['status' => 'failed']);

        $operator = User::find($this->operatorId);
        $superAdmins = User::whereHas('role', function ($q) {
            $q->where('name', 'super_admin');
        })->get();

        $notification = new BulkImportStatusNotification(
            $bulkImport->original_filename,
            'failed',
            $bulkImport->total_rows,
            0,
            $errorMessage
        );

        if ($operator) {
            $operator->notify($notification);
        }
        foreach ($superAdmins as $sa) {
            $sa->notify($notification);
        }
    }
}
