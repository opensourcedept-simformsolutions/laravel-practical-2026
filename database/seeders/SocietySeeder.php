<?php

namespace Database\Seeders;

use App\Models\Society;
use Illuminate\Database\Seeder;

class SocietySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $societies = [
            [
                'name' => 'Green Valley Residency',
                'address' => 'SG Highway',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '380015',
            ],
            [
                'name' => 'Skyline Heights',
                'address' => 'Prahlad Nagar',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '380051',
            ],
        ];

        foreach ($societies as $society) {
            Society::create($society);
        }
    }
}
