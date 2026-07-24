<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use App\Models\ApiKeyLog;
use Illuminate\Console\Command;

class CleanExpiredApiKeys extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'api-keys:clean {--days=30 : Number of days to retain request logs}';

    /**
     * The console command description.
     */
    protected $description = 'Clean up expired API keys and old request logs';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting API Key maintenance cleanup...');

        // 1. Revoke keys that have expired past their grace period
        $expiredGraceKeys = ApiKey::where('status', 'active')
            ->whereNotNull('rotation_grace_expires_at')
            ->where('rotation_grace_expires_at', '<=', now())
            ->get();

        foreach ($expiredGraceKeys as $key) {
            $key->update(['status' => 'revoked']);
            $this->line("Revoked expired rotation key ID: {$key->id} (Key: {$key->key})");
        }

        // 2. Revoke simple expired keys (status active, expires_at in past, no active grace period)
        $expiredKeys = ApiKey::where('status', 'active')
            ->whereNull('rotation_grace_expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expiredKeys as $key) {
            $key->update(['status' => 'revoked']);
            $this->line("Revoked expired key ID: {$key->id} (Key: {$key->key})");
        }

        // 3. Clean up request logs older than X days
        $days = (int)$this->option('days');
        $cutoff = now()->subDays($days);

        $deletedLogsCount = ApiKeyLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Successfully deleted {$deletedLogsCount} API request log(s) older than {$days} days.");
        $this->info('API Key maintenance cleanup completed.');

        return 0;
    }
}
