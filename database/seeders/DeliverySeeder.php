<?php

namespace Database\Seeders;

use App\Models\Delivery;
use App\Models\Resident;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DeliverySeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(1, 25) as $i) {

            $resident = Resident::inRandomOrder()->first();

            $status = fake()->randomElement([
                'received',
                'delivered',
            ]);

            Delivery::create([
                'flat_id' => $resident->flat_id,
                'resident_id' => $resident->id,
                'vendor' => fake()->randomElement([
                    'Amazon',
                    'Flipkart',
                    'Blinkit',
                    'Zepto',
                    'Swiggy Instamart',
                ]),
                'package_details' => fake()->sentence(),
                'status' => $status,
                'received_at' => now()->subDays(rand(1, 15)),
                'delivered_at' => $status === 'delivered'
                    ? now()->subDays(rand(0, 5))
                    : null,
            ]);
        }
    }
}
