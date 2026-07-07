<?php

namespace Database\Seeders;

use App\Models\Wing;
use App\Models\Society;
use Illuminate\Database\Seeder;

class WingSeeder extends Seeder
{
    public function run(): void
    {
        $societies = Society::all();

        if ($societies->count() >= 2) {
            // Society 1
            Wing::create([
                'society_id' => $societies[0]->id,
                'name' => 'A',
                'total_floors' => 2,
                'flats_per_floor' => 2,
            ]);
            Wing::create([
                'society_id' => $societies[0]->id,
                'name' => 'B',
                'total_floors' => 3,
                'flats_per_floor' => 3,
            ]);

            // Society 2
            Wing::create([
                'society_id' => $societies[1]->id,
                'name' => 'A',
                'total_floors' => 4,
                'flats_per_floor' => 2,
            ]);
            Wing::create([
                'society_id' => $societies[1]->id,
                'name' => 'B',
                'total_floors' => 2,
                'flats_per_floor' => 4,
            ]);
        }
    }
}
