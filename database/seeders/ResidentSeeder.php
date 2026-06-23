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
        $residentRoleIds = Role::whereIn('name', [
            'owner',
            'tenant',
        ])->pluck('id');

        $users = User::whereIn('role_id', $residentRoleIds)->get();

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
                'resident_type' => $user->role->name,
            ]);
        }
    }
}
