<?php

namespace Database\Seeders;

use App\Models\Flat;
use App\Models\Society;
use App\Models\Wing;
use Illuminate\Database\Seeder;

class FlatSeeder extends Seeder
{
    public function run(): void
    {
        // Generate flats based on wings created for each society.
        foreach (Society::all() as $society) {
            $wings = Wing::where('society_id', $society->id)->get();

            foreach ($wings as $wing) {
                for ($f = 1; $f <= $wing->total_floors; $f++) {
                    for ($n = 1; $n <= $wing->flats_per_floor; $n++) {
                        // Use numeric flat_number like 101, 102, 201 etc. when possible
                        $flatNumber = ($f * 100) + $n;

                        if (! Flat::where('wing_id', $wing->id)->where('floor', $f)->where('flat_number', $flatNumber)->exists()) {
                            Flat::create([
                                'society_id' => $society->id,
                                'wing_id' => $wing->id,
                                // keep legacy `wing` column populated for backward compatibility
                                'wing' => $wing->name,
                                'floor' => $f,
                                'flat_number' => $flatNumber,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
