<?php

namespace Database\Factories;

use App\Models\Society;
use Illuminate\Database\Eloquent\Factories\Factory;

class SocietyFactory extends Factory
{
    protected $model = Society::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Society',
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'pincode' => fake()->numerify('######'),
        ];
    }
}
