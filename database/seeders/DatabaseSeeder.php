<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            SocietySeeder::class,
            WingSeeder::class,
            UserSeeder::class,
            FlatSeeder::class,
            ResidentSeeder::class,
            VisitorSeeder::class,
            VisitorLogSeeder::class,
            DeliverySeeder::class,
            ComplaintSeeder::class,
        ]);
    }
}
