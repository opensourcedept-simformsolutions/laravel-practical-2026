<?php

namespace App\Console\Commands;

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
        $count = \App\Models\VisitorLog::whereIn('status', [
            \App\Enums\VisitorStatus::ACCEPTED->value,
            \App\Enums\VisitorStatus::PENDING->value,
            \App\Enums\VisitorStatus::PENDING_APPROVAL->value,
            \App\Enums\VisitorStatus::APPROVED->value,
        ])
        ->whereDate('visit_date', '<', today())
        ->update([
            'status' => \App\Enums\VisitorStatus::EXPIRED->value,
        ]);

        $this->info("Expired {$count} visitor passes.");
    }
}
