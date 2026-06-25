<?php

namespace Database\Factories;

use App\Models\Flat;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitorLogFactory extends Factory
{
    protected $model = VisitorLog::class;

    public function definition(): array
    {
        return [
            'visitor_id' => Visitor::factory(),
            'flat_id' => Flat::factory(),
            'created_by' => User::factory()->gatekeeper(),
            'gatekeeper_id' => User::factory()->gatekeeper(),
            'purpose' => fake()->randomElement([
                'Guest',
                'Courier',
                'Maintenance',
                'Food Delivery',
            ]),
            'entry_time' => null,
            'exit_time' => null,
            'status' => 'pending',
            'photo_path' => fake()->optional()->imageUrl(),
        ];
    }

    public function pending(): static
    {
        return $this->state([
            'status' => 'pending',
            'entry_time' => null,
            'exit_time' => null,
        ]);
    }

    public function entered(): static
    {
        return $this->state([
            'status' => 'entered',
            'entry_time' => now(),
            'exit_time' => null,
        ]);
    }

    public function exited(): static
    {
        return $this->state(function () {
            return [
                'status' => 'exited',
                'entry_time' => now()->subHours(2),
                'exit_time' => now(),
            ];
        });
    }
}
