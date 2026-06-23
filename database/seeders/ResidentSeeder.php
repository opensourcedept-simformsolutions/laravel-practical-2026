<?php

namespace Database\Seeders;

use App\Models\Flat;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResidentSeeder extends Seeder
{
    public function run(): void
    {
        $residentRole = Role::where('name', 'resident')->firstOrFail();

        $users = User::where('role_id', $residentRole->id)->get();

        foreach ($users as $user) {

            $flat = Flat::where('society_id', $user->society_id)
                ->whereDoesntHave('residents')
                ->inRandomOrder()
                ->first();

            if (! $flat) {
                continue;
            }

            Resident::create([
                'user_id' => $user->id,
                'flat_id' => $flat->id,
                'resident_type' => fake()->randomElement([
                    'owner',
                    'tenant',
                ]),
            ]);
        }
    }
}
