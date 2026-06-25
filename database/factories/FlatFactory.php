<?php

namespace Database\Factories;

use App\Models\Flat;
use App\Models\Society;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlatFactory extends Factory
{
    protected $model = Flat::class;

    public function definition(): array
    {
        return [
            'society_id' => Society::factory(),
            'wing' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
            'floor' => $this->faker->numberBetween(1, 15),
            'flat_number' => $this->faker->numberBetween(101, 1510),
        ];
    }
}
