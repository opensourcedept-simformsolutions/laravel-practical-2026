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
            'name' => $this->faker->company().' Society',
            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'pincode' => $this->faker->numerify('######'),
        ];
    }
}
