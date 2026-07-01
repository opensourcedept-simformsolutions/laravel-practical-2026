<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => '9'.$this->faker->numerify('#########'), // 10 digits starting with 9
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'role_id' => Role::factory(),
            'society_id' => Society::factory(),
        ];
    }
}
