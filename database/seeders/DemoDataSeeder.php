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
        // Roles
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $residentRole = Role::firstOrCreate(['name' => 'resident']);
        $gatekeeperRole = Role::firstOrCreate(['name' => 'gatekeeper']);

        // Societies
        $societies = [
            [
                'name' => 'Green Valley Society',
                'address' => 'SG Highway',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '380015',
            ],
            [
                'name' => 'Sunshine Residency',
                'address' => 'Vesu',
                'city' => 'Surat',
                'state' => 'Gujarat',
                'pincode' => '395007',
            ],
        ];

        $createdSocieties = [];

        foreach ($societies as $societyData) {
            $createdSocieties[] = Society::create($societyData);
        }

        // Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'phone' => '9000000001',
            'password' => Hash::make('1'),
            'role_id' => $superAdminRole->id,
            'society_id' => $createdSocieties[0]->id,
        ]);

        foreach ($createdSocieties as $societyIndex => $society) {

            // Admin
            User::create([
                'name' => "Admin {$society->name}",
                'email' => "admin{$societyIndex}@example.com",
                'phone' => '91000000' . ($societyIndex + 1),
                'password' => Hash::make('1'),
                'role_id' => $adminRole->id,
                'society_id' => $society->id,
            ]);

            // Gatekeeper
            $gatekeeper = User::create([
                'name' => "Gatekeeper {$society->name}",
                'email' => "gatekeeper{$societyIndex}@example.com",
                'phone' => '92000000' . ($societyIndex + 1),
                'password' => Hash::make('1'),
                'role_id' => $gatekeeperRole->id,
                'society_id' => $society->id,
            ]);

            for ($i = 1; $i <= 10; $i++) {

                // Flat
                $flat = Flat::create([
                    'society_id' => $society->id,
                    'wing' => chr(64 + (($i % 4) + 1)), // A,B,C,D
                    'floor' => rand(1, 5),
                    'flat_number' => 100 + $i,
                ]);

                // Resident User
                $residentUser = User::create([
                    'name' => "Resident {$societyIndex}{$i}",
                    'email' => "resident{$societyIndex}{$i}@example.com",
                    'phone' => '930' . str_pad(($societyIndex * 10 + $i), 7, '0', STR_PAD_LEFT),
                    'password' => Hash::make('1'),
                    'role_id' => $residentRole->id,
                    'society_id' => $society->id,
                ]);

                // Resident
                $resident = Resident::create([
                    'user_id' => $residentUser->id,
                    'flat_id' => $flat->id,
                    'resident_type' => rand(0, 1) ? 'owner' : 'tenant',
                ]);

                // Visitor
                $visitor = Visitor::create([
                    'name' => "Visitor {$societyIndex}{$i}",
                    'phone' => '987654' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'vehicle_number' => rand(0, 1)
                        ? 'GJ01AB' . rand(1000, 9999)
                        : null,
                ]);

                // Visitor Log
                $visitorStatuses = [
                    'accepted',
                    'pending',
                    'entered',
                    'exited',
                    'cancelled',
                ];

                VisitorLog::create([
                    'visitor_id' => $visitor->id,
                    'flat_id' => $flat->id,
                    'created_by' => $residentUser->id,
                    'gatekeeper_id' => $gatekeeper->id,
                    'purpose' => [
                        'Personal Visit',
                        'Courier',
                        'Food Delivery',
                        'Maintenance',
                    ][array_rand([
                        'Personal Visit',
                        'Courier',
                        'Food Delivery',
                        'Maintenance',
                    ])],
                    'visit_date' => now()->subDays(rand(0, 15))->toDateString(),
                    'entry_time' => now()->subHours(rand(1, 24)),
                    'status' => $visitorStatuses[array_rand($visitorStatuses)],
                ]);

                // Delivery
                $deliveryStatuses = [
                    'received',
                    'delivered',
                ];

                Delivery::create([
                    'flat_id' => $flat->id,
                    'resident_id' => $resident->id,
                    'vendor' => [
                        'Amazon',
                        'Flipkart',
                        'Myntra',
                        'Blinkit',
                    ][array_rand([
                        'Amazon',
                        'Flipkart',
                        'Myntra',
                        'Blinkit',
                    ])],
                    'package_details' => "Package {$i}",
                    'status' => $deliveryStatuses[array_rand($deliveryStatuses)],
                    'received_at' => now()->subDays(rand(0, 5)),
                    'delivered_at' => now(),
                ]);

                // Complaint
                $categories = [
                    'security',
                    'cleaning',
                    'water',
                    'parking',
                ];

                $complaintStatuses = [
                    'open',
                    'in_progress',
                    'resolved',
                ];

                Complaint::create([
                    'user_id' => $residentUser->id,
                    'category' => $categories[array_rand($categories)],
                    'description' => "Sample complaint {$i}",
                    'status' => $complaintStatuses[array_rand($complaintStatuses)],
                    'admin_notes' => 'Demo complaint notes',
                ]);
            }
        }
    }
}
