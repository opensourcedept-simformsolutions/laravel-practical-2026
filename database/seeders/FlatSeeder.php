<?php

namespace Database\Seeders;

use App\Models\Flat;
use App\Models\Society;
use Illuminate\Database\Seeder;

class FlatSeeder extends Seeder
{
    public function run(): void
    {
        $flats = [
            ['wing' => 'A', 'floor' => 1, 'flat_number' => 101],
            ['wing' => 'A', 'floor' => 1, 'flat_number' => 102],
            ['wing' => 'A', 'floor' => 2, 'flat_number' => 201],
            ['wing' => 'A', 'floor' => 2, 'flat_number' => 202],
            ['wing' => 'B', 'floor' => 1, 'flat_number' => 101],
            ['wing' => 'B', 'floor' => 1, 'flat_number' => 102],
            ['wing' => 'B', 'floor' => 2, 'flat_number' => 201],
            ['wing' => 'B', 'floor' => 2, 'flat_number' => 202],
            ['wing' => 'C', 'floor' => 1, 'flat_number' => 101],
            ['wing' => 'C', 'floor' => 1, 'flat_number' => 102],
        ];

        foreach (Society::all() as $society) {
            foreach ($flats as $flat) {
                Flat::create([
                    ...$flat,
                    'society_id' => $society->id,
                ]);
            }
        }
    }
}
