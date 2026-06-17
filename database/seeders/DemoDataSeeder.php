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
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::create([
            'name' => 'super_admin',
        ]);

        $adminRole = Role::create([
            'name' => 'admin',
        ]);

        $residentRole = Role::create([
            'name' => 'resident',
        ]);

        $gatekeeperRole = Role::create([
            'name' => 'gatekeeper',
        ]);

        $society = Society::create([
            'name' => 'Green Valley Society',
            'address' => 'SG Highway',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'pincode' => '380015',
        ]);

        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'phone' => '9000000001',
            'password' => Hash::make('1'),
            'role_id' => $superAdminRole->id,
            'society_id' => $society->id,
        ]);

        $admin = User::create([
            'name' => 'Society Admin',
            'email' => 'admin@example.com',
            'phone' => '9000000002',
            'password' => Hash::make('1'),
            'role_id' => $adminRole->id,
            'society_id' => $society->id,
        ]);

        $gatekeeper = User::create([
            'name' => 'Main Gate Guard',
            'email' => 'gatekeeper@example.com',
            'phone' => '9000000003',
            'password' => Hash::make('1'),
            'role_id' => $gatekeeperRole->id,
            'society_id' => $society->id,
        ]);

        $flat101 = Flat::create([
            'society_id' => $society->id,
            'wing' => 'A',
            'floor' => 1,
            'flat_number' => 101,
        ]);

        $flat102 = Flat::create([
            'society_id' => $society->id,
            'wing' => 'A',
            'floor' => 1,
            'flat_number' => 102,
        ]);

        $residentUser1 = User::create([
            'name' => 'John Resident',
            'email' => 'john@example.com',
            'phone' => '9000000004',
            'password' => Hash::make('1'),
            'role_id' => $residentRole->id,
            'society_id' => $society->id,
        ]);

        $residentUser2 = User::create([
            'name' => 'Jane Resident',
            'email' => 'jane@example.com',
            'phone' => '9000000005',
            'password' => Hash::make('1'),
            'role_id' => $residentRole->id,
            'society_id' => $society->id,
        ]);

        $resident1 = Resident::create([
            'user_id' => $residentUser1->id,
            'flat_id' => $flat101->id,
            'resident_type' => 'owner',
        ]);

        $resident2 = Resident::create([
            'user_id' => $residentUser2->id,
            'flat_id' => $flat102->id,
            'resident_type' => 'tenant',
        ]);

        $visitor1 = Visitor::create([
            'name' => 'Rahul Sharma',
            'phone' => '9876543210',
            'vehicle_number' => 'GJ01AB1234',
        ]);

        $visitor2 = Visitor::create([
            'name' => 'Courier Boy',
            'phone' => '9876543211',
            'vehicle_number' => null,
        ]);

        VisitorLog::create([
            'visitor_id' => $visitor1->id,
            'flat_id' => $flat101->id,
            'created_by' => $residentUser1->id,
            'gatekeeper_id' => $gatekeeper->id,
            'purpose' => 'Personal Visit',
            'visit_date' => now()->toDateString(),
            'entry_time' => now(),
            'status' => 'entered',
        ]);

        VisitorLog::create([
            'visitor_id' => $visitor2->id,
            'flat_id' => $flat102->id,
            'created_by' => $residentUser2->id,
            'gatekeeper_id' => $gatekeeper->id,
            'purpose' => 'Package Delivery',
            'visit_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        Delivery::create([
            'flat_id' => $flat101->id,
            'resident_id' => $resident1->id,
            'vendor' => 'Amazon',
            'package_details' => 'Bluetooth Speaker',
            'status' => 'received',
            'received_at' => now(),
        ]);

        Delivery::create([
            'flat_id' => $flat102->id,
            'resident_id' => $resident2->id,
            'vendor' => 'Flipkart',
            'package_details' => 'Laptop Bag',
            'status' => 'delivered',
            'received_at' => now()->subHour(),
            'delivered_at' => now(),
        ]);

        Complaint::create([
            'user_id' => $residentUser1->id,
            'category' => 'water',
            'description' => 'Low water pressure in bathroom.',
            'status' => 'open',
        ]);

        Complaint::create([
            'user_id' => $residentUser2->id,
            'category' => 'parking',
            'description' => 'Unauthorized vehicle parked.',
            'status' => 'in_progress',
            'admin_notes' => 'Security team informed.',
        ]);
    }
}
