<?php

namespace Database\Factories;

use App\Models\Flat;
use App\Models\Society;
use App\Models\Wing;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlatFactory extends Factory
{
    protected $model = Flat::class;

    public function definition(): array
    {
        return [
            'society_id' => Society::factory(),
            'wing_id' => function (array $attributes) {
                $name = fake()->randomElement(['A', 'B', 'C', 'D']);

                return Wing::firstOrCreate([
                    'society_id' => $attributes['society_id'],
                    'name' => $name,
                ], [
                    'total_floors' => fake()->numberBetween(5, 15),
                    'flats_per_floor' => fake()->numberBetween(4, 10),
                ])->id;
            },
            'wing' => function (array $attributes) {
                return Wing::find($attributes['wing_id'])?->name ?? 'A';
            },
            'floor' => fake()->numberBetween(1, 15),
            'flat_number' => fake()->numberBetween(101, 1515),
        ];
    }

    public function wingA(): static
    {
        return $this->state(function (array $attributes) {
            $wing = Wing::firstOrCreate([
                'society_id' => $attributes['society_id'] ?? Society::factory()->create()->id,
                'name' => 'A',
            ], [
                'total_floors' => 15,
                'flats_per_floor' => 10,
            ]);

            return [
                'wing_id' => $wing->id,
                'wing' => 'A',
            ];
        });
    }

    public function wingB(): static
    {
        return $this->state(function (array $attributes) {
            $wing = Wing::firstOrCreate([
                'society_id' => $attributes['society_id'] ?? Society::factory()->create()->id,
                'name' => 'B',
            ], [
                'total_floors' => 15,
                'flats_per_floor' => 10,
            ]);

            return [
                'wing_id' => $wing->id,
                'wing' => 'B',
            ];
        });
    }

    public function wingC(): static
    {
        return $this->state(function (array $attributes) {
            $wing = Wing::firstOrCreate([
                'society_id' => $attributes['society_id'] ?? Society::factory()->create()->id,
                'name' => 'C',
            ], [
                'total_floors' => 15,
                'flats_per_floor' => 10,
            ]);

            return [
                'wing_id' => $wing->id,
                'wing' => 'C',
            ];
        });
    }

    public function wingD(): static
    {
        return $this->state(function (array $attributes) {
            $wing = Wing::firstOrCreate([
                'society_id' => $attributes['society_id'] ?? Society::factory()->create()->id,
                'name' => 'D',
            ], [
                'total_floors' => 15,
                'flats_per_floor' => 10,
            ]);

            return [
                'wing_id' => $wing->id,
                'wing' => 'D',
            ];
        });
    }
}
