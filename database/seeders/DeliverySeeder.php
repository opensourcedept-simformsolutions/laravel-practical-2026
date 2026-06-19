<?php

namespace Database\Seeders;

use App\Models\Delivery;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DeliverySeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Delivery::create([
                'flat_id' => $i,
                'resident_id' => $i,
                'vendor' => 'Amazon',
                'package_details' => "Package $i",
                'status' => $i % 2 ? 'received' : 'delivered',
                'received_at' => Carbon::now()->subHours(rand(1, 24)),
                'delivered_at' => $i % 2 ? null : Carbon::now(),
            ]);
        }
    }
}
