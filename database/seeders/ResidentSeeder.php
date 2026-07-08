<?php

namespace Database\Seeders;

use App\Models\Flat;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ResidentSeeder extends Seeder
{
    public function run(): void
    {
        $residentRole = Role::where('name', 'resident')->firstOrFail();

        foreach (Flat::all() as $flat) {
            $count = rand(2, 3);
            for ($i = 1; $i <= $count; $i++) {
                $user = User::create([
                    'name' => fake()->name(),
                    'email' => "resident{$flat->id}_{$i}@societyms.test",
                    'phone' => fake()->numerify('9#########'),
                    'password' => Hash::make('1'),
                    'role_id' => $residentRole->id,
                    'society_id' => $flat->society_id,
                    'email_verified_at' => now(),
                ]);

                Resident::create([
                    'user_id' => $user->id,
                    'flat_id' => $flat->id,
                    'resident_type' => $i === 1 ? 'owner' : 'tenant',
                ]);
            }
        }
    }
}
