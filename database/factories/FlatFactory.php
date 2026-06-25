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
            'wing' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'floor' => fake()->numberBetween(1, 15),
            'flat_number' => fake()->numberBetween(101, 1515),
        ];
    }

    public function wingA(): static
    {
        return $this->state([
            'wing' => 'A',
        ]);
    }

    public function wingB(): static
    {
        return $this->state([
            'wing' => 'B',
        ]);
    }

    public function wingC(): static
    {
        return $this->state([
            'wing' => 'C',
        ]);
    }

    public function wingD(): static
    {
        return $this->state([
            'wing' => 'D',
        ]);
    }
}
