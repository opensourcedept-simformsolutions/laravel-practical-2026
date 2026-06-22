<?php

namespace Database\Factories;

use App\Models\Delivery;
use App\Models\Flat;
use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Delivery>
 */
class DeliveryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['received', 'delivered']);
        $receivedAt = fake()->dateTimeBetween('-30 days', 'now');
        $deliveredAt = ($status === 'delivered') ? fake()->dateTimeBetween($receivedAt, 'now') : null;

        return [
            'flat_id' => Flat::factory(),
            'resident_id' => Resident::factory(),
            'vendor' => fake()->company(),
            'package_details' => fake()->sentence(),
            'status' => $status,
            'received_at' => $receivedAt,
            'delivered_at' => $deliveredAt,
        ];
    }
}
