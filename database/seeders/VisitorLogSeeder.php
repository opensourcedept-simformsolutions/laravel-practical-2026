<?php

namespace Database\Seeders;

use App\Models\Resident;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorLog;
use Illuminate\Database\Seeder;

class VisitorLogSeeder extends Seeder
{
    public function run(): void
    {
        $gatekeepers = User::whereHas('role', fn ($q) => $q->where('name', 'gatekeeper'))->get();
        $visitors = Visitor::all();
        $residents = Resident::all();

        if ($visitors->count() >= 7 && $residents->count() > 0) {
            $gk = $gatekeepers->first();

            // 1. Expired pass
            $res = $residents[0];
            VisitorLog::create([
                'visitor_id' => $visitors[0]->id,
                'flat_id' => $res->flat_id,
                'created_by' => $res->user_id,
                'purpose' => 'Delivery Check',
                'visit_date' => today()->subDays(5)->toDateString(),
                'status' => 'expired',
            ]);

            // 2. Pending pass
            $res = $residents[min(1, $residents->count() - 1)];
            VisitorLog::create([
                'visitor_id' => $visitors[1]->id,
                'flat_id' => $res->flat_id,
                'created_by' => $res->user_id,
                'purpose' => 'Guest Visit',
                'visit_date' => today()->toDateString(),
                'status' => 'pending',
            ]);

            // 3. Pending approval pass
            $res = $residents[min(2, $residents->count() - 1)];
            VisitorLog::create([
                'visitor_id' => $visitors[2]->id,
                'flat_id' => $res->flat_id,
                'created_by' => $res->user_id,
                'gatekeeper_id' => $gk->id,
                'purpose' => 'Plumber Service',
                'visit_date' => today()->toDateString(),
                'status' => 'pending_approval',
            ]);

            // 4. Entered pass
            $res = $residents[min(3, $residents->count() - 1)];
            VisitorLog::create([
                'visitor_id' => $visitors[3]->id,
                'flat_id' => $res->flat_id,
                'created_by' => $res->user_id,
                'gatekeeper_id' => $gk->id,
                'purpose' => 'Food Delivery',
                'visit_date' => today()->toDateString(),
                'entry_time' => now(),
                'status' => 'entered',
            ]);

            // 5. Exited pass
            $res = $residents[min(4, $residents->count() - 1)];
            VisitorLog::create([
                'visitor_id' => $visitors[4]->id,
                'flat_id' => $res->flat_id,
                'created_by' => $res->user_id,
                'gatekeeper_id' => $gk->id,
                'purpose' => 'Electrician Work',
                'visit_date' => today()->subDay()->toDateString(),
                'entry_time' => now()->subDay()->subHours(2),
                'exit_time' => now()->subDay()->subHours(1),
                'status' => 'exited',
            ]);

            // 6. Cancelled pass
            $res = $residents[min(5, $residents->count() - 1)];
            VisitorLog::create([
                'visitor_id' => $visitors[5]->id,
                'flat_id' => $res->flat_id,
                'created_by' => $res->user_id,
                'purpose' => 'Maintenance',
                'visit_date' => today()->toDateString(),
                'status' => 'cancelled',
            ]);

            // 7. Rejected pass
            $res = $residents[min(6, $residents->count() - 1)];
            VisitorLog::create([
                'visitor_id' => $visitors[6]->id,
                'flat_id' => $res->flat_id,
                'created_by' => $res->user_id,
                'gatekeeper_id' => $gk->id,
                'purpose' => 'Sales Person',
                'visit_date' => today()->toDateString(),
                'status' => 'rejected',
            ]);
        }
    }
}
