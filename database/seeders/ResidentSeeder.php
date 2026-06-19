<?php

namespace Database\Seeders;

use App\Models\Resident;
use Illuminate\Database\Seeder;

class ResidentSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            Resident::create([
                'user_id' => $i + 2,
                'flat_id' => $i,
                'resident_type' => $i % 2 ? 'owner' : 'tenant',
            ]);
        }
    }
}
