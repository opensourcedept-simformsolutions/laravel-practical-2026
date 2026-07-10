<?php

namespace Database\Seeders;

use App\Models\Visitor;
use Illuminate\Database\Seeder;

class VisitorSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 20) as $i) {

            Visitor::create([
                'name' => fake()->name(),
                'phone' => fake()->numerify('9#########'),
                'vehicle_number' => fake()->optional(70)->regexify(
                    'GJ[0-9]{2}[A-Z]{2}[0-9]{4}'
                ),
            ]);
        }
    }
}
