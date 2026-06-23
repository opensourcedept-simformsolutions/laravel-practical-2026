<?php

namespace Database\Seeders;

use App\Models\Resident;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorLog;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class VisitorLogSeeder extends Seeder
{
    public function run(): void
    {
        $gatekeepers = User::whereHas('role', fn ($q) =>
            $q->where('name', 'gatekeeper')
        )->get();

        foreach (range(1, 40) as $i) {

            $resident = Resident::inRandomOrder()->first();

            VisitorLog::create([
                'visitor_id' => Visitor::inRandomOrder()->first()->id,
                'flat_id' => $resident->flat_id,
                'created_by' => $resident->user_id,
                'gatekeeper_id' => $gatekeepers->random()->id,
                'purpose' => fake()->sentence(3),
                'visit_date' => fake()->dateTimeBetween('-15 days', '+5 days'),
                'status' => fake()->randomElement([
                    'pending',
                    'accepted',
                    'entered',
                    'exited',
                    'cancelled',
                ]),
            ]);
        }
    }
}
