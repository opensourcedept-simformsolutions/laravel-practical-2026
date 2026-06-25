<?php

namespace Database\Factories;

use App\Models\Flat;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResidentFactory extends Factory
{
    protected $model = Resident::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->resident(),
            'flat_id' => Flat::factory(),
            'resident_type' => fake()->randomElement([
                'owner',
                'tenant',
            ]),
        ];
    }

    public function owner(): static
    {
        return $this->state([
            'resident_type' => 'owner',
        ]);
    }

    public function tenant(): static
    {
        return $this->state([
            'resident_type' => 'tenant',
        ]);
    }
}
