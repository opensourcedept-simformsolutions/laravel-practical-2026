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
            [
                'name' => 'Riverfront Residency',
                'address' => 'Sabarmati Riverfront',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '380005',
            ],
            [
                'name' => 'Serene Meadows',
                'address' => 'Infocity Road',
                'city' => 'Gandhinagar',
                'state' => 'Gujarat',
                'pincode' => '382007',
            ],
            [
                'name' => 'Plaza Premium Apartments',
                'address' => 'Bodakdev',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '380054',
            ],
            [
                'name' => 'Vibrant Towers',
                'address' => 'Chandkheda',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '382424',
            ],
        ];

        foreach ($societies as $society) {
            Society::create($society);
        }
    }
}
