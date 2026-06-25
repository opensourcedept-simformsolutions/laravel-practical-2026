<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('##########'),
            'password' => Hash::make('password'),
            'role_id' => Role::factory(),
            'society_id' => Society::factory(),
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::firstOrCreate(['name' => 'super_admin'])->id,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::firstOrCreate(['name' => 'admin'])->id,
        ]);
    }

    public function resident(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::firstOrCreate(['name' => 'resident'])->id,
        ]);
    }

    public function gatekeeper(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::firstOrCreate(['name' => 'gatekeeper'])->id,
        ]);
    }
}
