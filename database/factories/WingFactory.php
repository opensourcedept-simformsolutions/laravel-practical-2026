<?php

namespace Database\Factories;

use App\Models\Society;
use App\Models\Wing;
use Illuminate\Database\Eloquent\Factories\Factory;

class WingFactory extends Factory
{
    protected $model = Wing::class;

    public function definition(): array
    {
        return [
            'society_id' => Society::factory(),
            'name' => fake()->unique()->regexify('[A-Z]{1,2}'),
            'total_floors' => fake()->numberBetween(1, 15),
            'flats_per_floor' => fake()->numberBetween(1, 10),
        ];
    }
}
