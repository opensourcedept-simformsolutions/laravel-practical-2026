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
use Carbon\Carbon;
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

        // Admin users (fixed small count)
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'phone' => '9000000001',
            'password' => Hash::make('1'),
            'role_id' => $roles['super_admin']->id,
            'society_id' => null,
        ]);

        // Society
        $societies = [];

        for ($s = 1; $s <= 3; $s++) {
            $societies[] = Society::create([
                'name' => "Green Valley Society $s",
                'address' => "SG Highway Area $s",
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '38001'.$s,
            ]);
        }

        foreach ($societies as $society) {

            $this->command->info("🏢 Seeding Society: {$society->name}");

            $admin = User::create([
                'name' => "Admin {$society->id}",
                'email' => "admin{$society->id}@example.com",
                'phone' => '90000000'.$society->id,
                'password' => Hash::make('1'),
                'role_id' => $roles['admin']->id,
                'society_id' => $society->id,
            ]);
            $this->command->info("✔ Admin created for Society {$society->id}");

            // Gatekeeper
            $gatekeeper = User::create([
                'name' => "Gatekeeper {$society->id}",
                'email' => "gate{$society->id}@example.com",
                'phone' => '90000000'.($society->id + 10),
                'password' => Hash::make('1'),
                'role_id' => $roles['gatekeeper']->id,
                'society_id' => $society->id,
            ]);
            $this->command->info("✔ Gate Keeper created for Society {$society->id}");

            // Flats
            $flats = [];

            $wings = ['A', 'B', 'C', 'D'];

            foreach ($wings as $wing) {
                for ($floor = 1; $floor <= 4; $floor++) {

                    for ($flat = 1; $flat <= 4; $flat++) {

                        $flatNumber = ($floor * 100) + $flat;

                        $flats[] = Flat::create([
                            'society_id' => $society->id,
                            'wing' => $wing,
                            'floor' => $floor,
                            'flat_number' => $flatNumber,
                        ]);
                    }
                }
            }
            $this->command->info("✔ Flats created for Society {$society->id}");

            // Residents Users
            $residentUsers = [];

            for ($i = 1; $i <= 40; $i++) {
                $residentUsers[] = User::create([
                    'name' => "Resident {$society->id}-$i",
                    'email' => "resident{$society->id}_$i@example.com",
                    'phone' => '900'.$society->id.str_pad($i, 3, '0', STR_PAD_LEFT),
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
            $this->command->info("✔ Residents created for Society {$society->id}");

            // Visitors (40)
            $visitors = [];

            for ($i = 1; $i <= 40; $i++) {
                $visitors[] = Visitor::create([
                    'name' => "Visitor {$society->id}-$i",
                    'phone' => '98765'.$society->id.str_pad($i, 3, '0', STR_PAD_LEFT),
                    'vehicle_number' => $i % 2 == 0 ? "GJ01{$society->id}".(1000 + $i) : null,
                ]);
            }
            $this->command->info("✔ Visitors created for Society {$society->id}");

            // Visitor Logs (100)
            for ($i = 0; $i < 100; $i++) {

                $entryTime = Carbon::instance(
                    fake()->dateTimeBetween($today->copy()->subDays(30), $today)
                );

                $status = fake()->randomElement(['entered', 'pending', 'exited']);

                $exitTime = null;

                if ($status === 'exited') {
                    $exitTime = fake()->dateTimeBetween(
                        $entryTime,
                        $entryTime->copy()->addHours(12)
                    );
                }

                VisitorLog::create([
                    'visitor_id' => $visitors[array_rand($visitors)]->id,
                    'flat_id' => $flats[array_rand($flats)]->id,
                    'created_by' => $residentUsers[array_rand($residentUsers)]->id,
                    'gatekeeper_id' => $gatekeeper->id,
                    'purpose' => fake()->randomElement([
                        'Personal Visit',
                        'Delivery',
                        'Maintenance',
                        'Guest Visit',
                    ]),
                    'visit_date' => $entryTime->format('Y-m-d'),
                    'entry_time' => $entryTime,
                    'exit_time' => $exitTime,
                    'status' => $status,
                ]);
            }
            $this->command->info("✔ Visitor Logs done for Society {$society->id}");

            // Deliveries (40)
            for ($i = 0; $i < 40; $i++) {

                $receivedAt = fake()->dateTimeBetween($today->copy()->subDays(30), $today);

                $isDelivered = fake()->boolean(70);

                Delivery::create([
                    'flat_id' => $flats[$i]->id,
                    'resident_id' => $residents[$i]->id,
                    'vendor' => fake()->randomElement([
                        'Amazon',
                        'Flipkart',
                        'Blinkit',
                        'Zepto',
                        'Swiggy Instamart',
                    ]),
                    'package_details' => fake()->sentence(3),
                    'status' => $isDelivered ? 'delivered' : 'received',
                    'received_at' => $receivedAt,
                    'delivered_at' => $isDelivered
                        ? (clone $receivedAt)->modify('+'.rand(1, 24).' hours')
                        : null,
                ]);
            }

            // Complaints (40)
            $categories = [
                'security',
                'cleaning',
                'water',
                'parking',
            ];

            for ($i = 0; $i < 40; $i++) {

                $createdAt = fake()->dateTimeBetween($today->copy()->subDays(30), $today);

                $status = fake()->randomElement(['open', 'in_progress', 'resolved']);

                Complaint::create([
                    'user_id' => $residentUsers[$i]->id,
                    'category' => fake()->randomElement($categories),
                    'description' => fake()->paragraph(),
                    'status' => $status,
                    'admin_notes' => $status === 'in_progress'
                        ? 'Admin is working on it'
                        : ($status === 'resolved'
                            ? 'Issue resolved successfully'
                            : null),
                    'created_at' => $createdAt,
                    'updated_at' => $status !== 'open'
                        ? (clone $createdAt)->modify('+'.rand(1, 72).' hours')
                        : $createdAt,
                ]);
            }
        }
    }
}
