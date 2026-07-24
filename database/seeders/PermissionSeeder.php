<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'Create Resident',
            'View Resident',
            'Update Resident',
            'Delete Resident',

            'Create Visitor',
            'View Visitor',
            'Update Visitor',
            'Delete Visitor',

            'Create Complaint',
            'View Complaint',
            'Update Complaint',
            'Delete Complaint',

            'Approve Complaint',
            'Reject Complaint',

            'Create Society',
            'Update Society',

            'Manage Gatekeepers',
            'Assign Roles',

            'Export Reports',
            'View Dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
            ]);
        }
    }
}