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
            'user_id' => User::factory(),
            'flat_id' => Flat::factory(),
            'resident_type' => $this->faker->randomElement(['owner', 'tenant']),
        ];
    }
}
