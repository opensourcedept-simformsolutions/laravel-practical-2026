<?php

namespace Database\Factories;

use App\Models\Delivery;
use App\Models\Flat;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeliveryFactory extends Factory
{
    protected $model = Delivery::class;

    public function definition(): array
    {
        $received = fake()->dateTimeBetween('-5 days', 'now');

        return [
            'flat_id' => Flat::factory(),
            'resident_id' => Resident::factory(),
            'vendor' => fake()->randomElement([
                'Amazon',
                'Flipkart',
                'Blinkit',
                'Swiggy',
                'Zomato',
            ]),
            'package_details' => fake()->sentence(),
            'status' => 'received',
            'received_at' => $received,
            'delivered_at' => null,
        ];
    }

    public function received(): static
    {
        return $this->state(function () {
            return [
                'status' => 'received',
                'received_at' => now(),
                'delivered_at' => null,
            ];
        });
    }

    public function delivered(): static
    {
        return $this->state(function () {
            $received = now()->subHours(rand(1, 24));

            return [
                'status' => 'delivered',
                'received_at' => $received,
                'delivered_at' => now(),
            ];
        });
    }
}
