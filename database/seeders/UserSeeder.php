<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'super_admin')->firstOrFail();
        $adminRole = Role::where('name', 'admin')->firstOrFail();
        $gatekeeperRole = Role::where('name', 'gatekeeper')->firstOrFail();
        $ownerRole = Role::where('name', 'owner')->firstOrFail();
        $tenantRole = Role::where('name', 'tenant')->firstOrFail();

        /*
         * Super Admin
         */
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@societyms.test',
            'phone' => '9999999999',
            'password' => Hash::make('password'),
            'role_id' => $superAdminRole->id,
            'society_id' => Society::first()->id,
            'email_verified_at' => now(),
        ]);

        foreach (Society::all() as $society) {

            /*
             * Society Admin
             */
            User::create([
                'name' => "{$society->name} Admin",
                'email' => "admin{$society->id}@societyms.test",
                'phone' => fake()->numerify('9#########'),
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
                'society_id' => $society->id,
                'email_verified_at' => now(),
            ]);

            /*
             * Gatekeepers
             */
            foreach (range(1, 2) as $i) {
                User::create([
                    'name' => fake()->name(),
                    'email' => "gatekeeper{$society->id}_{$i}@societyms.test",
                    'phone' => fake()->numerify('9#########'),
                    'password' => Hash::make('password'),
                    'role_id' => $gatekeeperRole->id,
                    'society_id' => $society->id,
                    'email_verified_at' => now(),
                ]);
            }

            /*
             * Owners
             */
            foreach (range(1, 3) as $i) {
                User::create([
                    'name' => fake()->name(),
                    'email' => "owner{$society->id}_{$i}@societyms.test",
                    'phone' => fake()->numerify('9#########'),
                    'password' => Hash::make('password'),
                    'role_id' => $ownerRole->id,
                    'society_id' => $society->id,
                    'email_verified_at' => now(),
                ]);
            }

            /*
             * Tenants
             */
            foreach (range(1, 2) as $i) {
                User::create([
                    'name' => fake()->name(),
                    'email' => "tenant{$society->id}_{$i}@societyms.test",
                    'phone' => fake()->numerify('9#########'),
                    'password' => Hash::make('password'),
                    'role_id' => $tenantRole->id,
                    'society_id' => $society->id,
                    'email_verified_at' => now(),
                ]);
            }
        }
    }
}
