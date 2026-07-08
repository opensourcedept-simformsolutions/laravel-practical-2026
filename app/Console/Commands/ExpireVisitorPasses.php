<?php

namespace App\Console\Commands;

use App\Enums\VisitorStatus;
use App\Models\VisitorLog;
use Illuminate\Console\Command;

class ExpireVisitorPasses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:expire-visitor-passes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire visitor passes where the visit date is in the past';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = VisitorLog::whereIn('status', [
            VisitorStatus::ACCEPTED->value,
            VisitorStatus::PENDING->value,
            VisitorStatus::PENDING_APPROVAL->value,
            VisitorStatus::APPROVED->value,
        ])
            ->whereDate('visit_date', '<', today())
            ->update([
                'status' => VisitorStatus::EXPIRED->value,
            ]);

        $this->info("Expired {$count} visitor passes.");
    }
}
