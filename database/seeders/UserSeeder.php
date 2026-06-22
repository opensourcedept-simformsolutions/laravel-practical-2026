<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@society.com',
            'phone' => '9876543210',
            'password' => Hash::make('password'),
            'role_id' => 1,
        ]);

        User::create([
            'name' => 'Gatekeeper',
            'email' => 'gatekeeper@society.com',
            'phone' => '9876543211',
            'password' => Hash::make('password'),
            'role_id' => 2,
        ]);

        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => "Resident $i",
                'email' => "resident$i@example.com",
                'phone' => '90000000' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'password' => Hash::make('password'),
                'role_id' => $i % 2 ? 3 : 4,
            ]);
        }
    }
}
