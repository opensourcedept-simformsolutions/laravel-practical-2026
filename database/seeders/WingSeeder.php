<?php

namespace Database\Seeders;

use App\Models\Wing;
use App\Models\Society;
use Illuminate\Database\Seeder;

class WingSeeder extends Seeder
{
    public function run(): void
    {
        // create a simple set of wings per society to match legacy flat data
        foreach (Society::all() as $society) {
            // Wings A and B: 2 floors, 2 flats per floor
            foreach (['A', 'B'] as $name) {
                Wing::create([
                    'society_id' => $society->id,
                    'name' => $name,
                    'total_floors' => 2,
                    'flats_per_floor' => 2,
                ]);
            }

            // Wing C: 1 floor, 2 flats per floor
            Wing::create([
                'society_id' => $society->id,
                'name' => 'C',
                'total_floors' => 1,
                'flats_per_floor' => 2,
            ]);
        }
    }
}
