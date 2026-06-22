<?php

namespace Database\Seeders;

use App\Models\Complaint;
use Illuminate\Database\Seeder;

class ComplaintSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'security',
            'cleaning',
            'water',
            'parking',
        ];

        for ($i = 1; $i <= 10; $i++) {
            Complaint::create([
                'user_id' => rand(3, 12),
                'category' => $categories[array_rand($categories)],
                'description' => "Sample complaint $i",
                'admin_notes' => null,
                'status' => 'open',
            ]);
        }
    }
}
