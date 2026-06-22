<?php

namespace Database\Seeders;

use App\Models\Flat;
use Illuminate\Database\Seeder;

class FlatSeeder extends Seeder
{
    public function run(): void
    {
        $flats = [
            ['A',1,'101'],
            ['A',1,'102'],
            ['A',2,'201'],
            ['A',2,'202'],
            ['B',1,'101'],
            ['B',1,'102'],
            ['B',2,'201'],
            ['B',2,'202'],
            ['C',3,'301'],
            ['C',3,'302'],
        ];

        foreach ($flats as $flat) {
            Flat::create([
                'wing' => $flat[0],
                'floor' => $flat[1],
                'flat_number' => $flat[2],
            ]);
        }
    }
}
