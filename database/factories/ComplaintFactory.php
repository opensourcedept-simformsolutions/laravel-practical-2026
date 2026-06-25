<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComplaintFactory extends Factory
{
    protected $model = Complaint::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->resident(),
            'category' => fake()->randomElement([
                'security',
                'cleaning',
                'water',
                'parking',
            ]),
            'description' => fake()->paragraph(),
            'admin_notes' => null,
            'status' => 'open',
        ];
    }

    public function open(): static
    {
        return $this->state([
            'status' => 'open',
            'admin_notes' => null,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state([
            'status' => 'in_progress',
            'admin_notes' => fake()->sentence(),
        ]);
    }

    public function resolved(): static
    {
        return $this->state([
            'status' => 'resolved',
            'admin_notes' => fake()->sentence(),
        ]);
    }
}
