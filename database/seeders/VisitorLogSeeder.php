<?php

namespace Database\Seeders;

use App\Models\VisitorLog;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class VisitorLogSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            VisitorLog::create([
                'visitor_id' => $i,
                'flat_id' => rand(1, 10),
                'gatekeeper_id' => 2,
                'purpose' => 'Personal Visit',
                'entry_time' => Carbon::now()->subHours(rand(1, 10)),
                'exit_time' => null,
                'status' => 'entered',
                'photo_path' => null,
            ]);
        }
    }
}
