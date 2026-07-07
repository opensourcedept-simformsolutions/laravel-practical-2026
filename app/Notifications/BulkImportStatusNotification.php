<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Channels\CustomDatabaseChannel;

class BulkImportStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $filename,
        public string $status,
        public int $totalRows,
        public int $importedRows,
        public ?string $errorMessage = null
    ) {}

    public function via(object $notifiable): array
    {
        return [CustomDatabaseChannel::class];
    }

    public function toTitle(object $notifiable): string
    {
        return $this->status === 'completed' ? 'Bulk Import Completed' : 'Bulk Import Failed';
    }

    public function toMessage(object $notifiable): string
    {
        if ($this->status === 'completed') {
            return "The bulk import for file '{$this->filename}' completed successfully. Imported {$this->importedRows} out of {$this->totalRows} residents.";
        }
        
        $errSuffix = $this->errorMessage ? " Error: {$this->errorMessage}" : "";
        return "The bulk import for file '{$this->filename}' failed.{$errSuffix}";
    }

    public function toArray(object $notifiable): array
    {
        return [
            'filename' => $this->filename,
            'status' => $this->status,
            'total_rows' => $this->totalRows,
            'imported_rows' => $this->importedRows,
            'error_message' => $this->errorMessage,
        ];
    }
}
