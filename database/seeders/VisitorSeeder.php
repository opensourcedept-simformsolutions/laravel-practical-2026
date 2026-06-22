<?php

namespace Database\Seeders;

use App\Models\Visitor;
use Illuminate\Database\Seeder;

class VisitorSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Visitor::create([
                'name' => "Visitor $i",
                'phone' => '80000000' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'vehicle_number' => "GJ01AB10$i",
            ]);
        }
    }
}
