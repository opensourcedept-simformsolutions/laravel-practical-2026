<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\Resident;
use Illuminate\Database\Seeder;

class ComplaintSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 15) as $i) {

            $resident = Resident::inRandomOrder()->first();

            Complaint::create([
                'user_id' => $resident->user_id,
                'category' => fake()->randomElement([
                    'security',
                    'cleaning',
                    'water',
                    'parking',
                ]),
                'description' => fake()->paragraph(),
                'status' => fake()->randomElement([
                    'open',
                    'in_progress',
                    'resolved',
                ]),
            ]);
        }
    }
}
