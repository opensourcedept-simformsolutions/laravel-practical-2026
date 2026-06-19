<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            DemoDataSeeder::class,
        ]);
        User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            RoleSeeder::class,
        ]);

        // User::create([
        //     'name' => 'Admin',
        //     'email' => 'admin@example.com',
        //     'password' => bcrypt('1'),
        //     'phone' => '111111111',
        //     'role_id' => Role::where('name', 'admin')->first()->id,
        // ]);
    }
}
