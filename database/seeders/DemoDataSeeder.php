<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\Delivery;
use App\Models\Flat;
use App\Models\Resident;
use App\Models\Role;
use App\Models\Society;
use App\Models\User;
use App\Models\Visitor;
use App\Models\VisitorLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $today = now();

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Complaint::truncate();
        Delivery::truncate();
        VisitorLog::truncate();
        Visitor::truncate();
        Resident::truncate();
        User::truncate();
        Flat::truncate();
        Society::truncate();
        Role::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Roles
        $roles = [
            'super_admin' => Role::create(['name' => 'super_admin']),
            'admin' => Role::create(['name' => 'admin']),
            'resident' => Role::create(['name' => 'resident']),
            'gatekeeper' => Role::create(['name' => 'gatekeeper']),
        ];

        // Society
        $society = Society::create([
            'name' => 'Green Valley Society',
            'address' => 'SG Highway',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'pincode' => '380015',
        ]);

        // Admin users (fixed small count)
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'phone' => '9000000001',
            'password' => Hash::make('1'),
            'role_id' => $roles['super_admin']->id,
            'society_id' => $society->id,
        ]);

        $admin = User::create([
            'name' => 'Society Admin',
            'email' => 'admin@example.com',
            'phone' => '9000000002',
            'password' => Hash::make('1'),
            'role_id' => $roles['admin']->id,
            'society_id' => $society->id,
        ]);

        $gatekeeper = User::create([
            'name' => 'Main Gate Guard',
            'email' => 'gatekeeper@example.com',
            'phone' => '9000000003',
            'password' => Hash::make('1'),
            'role_id' => $roles['gatekeeper']->id,
            'society_id' => $society->id,
        ]);

        // Flats (40)
        $flats = [];
        for ($i = 1; $i <= 40; $i++) {
            $flats[] = Flat::create([
                'society_id' => $society->id,
                'wing' => 'A',
                'floor' => ceil($i / 10),
                'flat_number' => 100 + $i,
            ]);
        }

        // Residents Users (40)
        $residentUsers = [];
        for ($i = 1; $i <= 40; $i++) {
            $residentUsers[] = User::create([
                'name' => "Resident $i",
                'email' => "resident$i@example.com",
                'phone' => '9000001' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'password' => Hash::make('1'),
                'role_id' => $roles['resident']->id,
                'society_id' => $society->id,
            ]);
        }

        // Residents mapping (40)
        $residents = [];
        foreach ($residentUsers as $i => $user) {
            $residents[] = Resident::create([
                'user_id' => $user->id,
                'flat_id' => $flats[$i]->id,
                'resident_type' => $i % 2 == 0 ? 'owner' : 'tenant',
            ]);
        }

        // Visitors (40)
        $visitors = [];
        for ($i = 1; $i <= 40; $i++) {
            $visitors[] = Visitor::create([
                'name' => "Visitor $i",
                'phone' => '9876500' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'vehicle_number' => $i % 2 == 0 ? "GJ01AB" . (1000 + $i) : null,
            ]);
        }

        // Visitor Logs (100)
        for ($i = 0; $i < 100; $i++) {

            $entryTime = fake()->dateTimeBetween($today->copy()->subDays(30), $today);

            VisitorLog::create([
                'visitor_id' => $visitors[array_rand($visitors)]->id,
                'flat_id' => $flats[array_rand($flats)]->id,
                'created_by' => $residentUsers[array_rand($residentUsers)]->id,
                'gatekeeper_id' => $gatekeeper->id,
                'purpose' => fake()->randomElement(['Personal Visit', 'Delivery', 'Maintenance', 'Guest Visit',]),
                'visit_date' => $entryTime->format('Y-m-d'),
                'entry_time' => $entryTime,
                'status' => fake()->randomElement(['entered', 'pending', 'exited',]),
            ]);
        }

        // Deliveries (40)
        for ($i = 0; $i < 40; $i++) {

            $receivedAt = fake()->dateTimeBetween($today->copy()->subDays(30), $today);

            $isDelivered = fake()->boolean(70);

            Delivery::create([
                'flat_id' => $flats[$i]->id,
                'resident_id' => $residents[$i]->id,
                'vendor' => fake()->randomElement(['Amazon', 'Flipkart', 'Blinkit', 'Zepto', 'Swiggy Instamart',]),
                'package_details' => fake()->sentence(3),
                'status' => $isDelivered ? 'delivered' : 'received',
                'received_at' => $receivedAt,
                'delivered_at' => $isDelivered ? (clone $receivedAt)->modify('+' . rand(1, 24) . ' hours') : null,
            ]);
        }

        // Complaints (40)
        $categories = [
            'security',
            'cleaning',
            'water',
            'parking'
        ];

        for ($i = 0; $i < 40; $i++) {

            $createdAt = fake()->dateTimeBetween($today->copy()->subDays(30), $today);

            $status = fake()->randomElement(['open', 'in_progress', 'resolved',]);

            Complaint::create([
                'user_id' => $residentUsers[$i]->id,
                'category' => fake()->randomElement($categories),
                'description' => fake()->paragraph(),
                'status' => $status,
                'admin_notes' => $status === 'in_progress' ? 'Admin is working on it' : ($status === 'resolved' ? 'Issue resolved successfully' : null),
                'created_at' => $createdAt,
                'updated_at' => $status !== 'open' ? (clone $createdAt)->modify('+' . rand(1, 72) . ' hours') : $createdAt,
            ]);
        }
    }
}
