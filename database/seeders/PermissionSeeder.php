<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Society Management
            ['name' => 'View Societies', 'slug' => 'societies.view', 'group' => 'Society Management', 'description' => 'View list of societies'],
            ['name' => 'Create Societies', 'slug' => 'societies.create', 'group' => 'Society Management', 'description' => 'Create new societies'],
            ['name' => 'Edit Societies', 'slug' => 'societies.edit', 'group' => 'Society Management', 'description' => 'Edit existing societies'],
            ['name' => 'Delete Societies', 'slug' => 'societies.delete', 'group' => 'Society Management', 'description' => 'Delete societies'],

            // Wings & Flats
            ['name' => 'View Wings', 'slug' => 'wings.view', 'group' => 'Wings & Flats', 'description' => 'View society wings'],
            ['name' => 'Manage Wings', 'slug' => 'wings.manage', 'group' => 'Wings & Flats', 'description' => 'Create, edit, or delete wings'],
            ['name' => 'View Flats', 'slug' => 'flats.view', 'group' => 'Wings & Flats', 'description' => 'View flats'],
            ['name' => 'Create Flats', 'slug' => 'flats.create', 'group' => 'Wings & Flats', 'description' => 'Create flats'],
            ['name' => 'Edit Flats', 'slug' => 'flats.edit', 'group' => 'Wings & Flats', 'description' => 'Edit flats'],
            ['name' => 'Delete Flats', 'slug' => 'flats.delete', 'group' => 'Wings & Flats', 'description' => 'Delete flats'],
            ['name' => 'Export Flats', 'slug' => 'flats.export', 'group' => 'Wings & Flats', 'description' => 'Export flat data'],

            // User Management
            ['name' => 'View Users', 'slug' => 'users.view', 'group' => 'User Management', 'description' => 'View user list'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'group' => 'User Management', 'description' => 'Create new user accounts'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'group' => 'User Management', 'description' => 'Edit user accounts'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'group' => 'User Management', 'description' => 'Delete user accounts'],
            ['name' => 'Restore Users', 'slug' => 'users.restore', 'group' => 'User Management', 'description' => 'Restore soft-deleted user accounts'],

            // Resident Management
            ['name' => 'View Residents', 'slug' => 'residents.view', 'group' => 'Resident Management', 'description' => 'View resident profiles'],
            ['name' => 'Create Residents', 'slug' => 'residents.create', 'group' => 'Resident Management', 'description' => 'Create resident profiles'],
            ['name' => 'Edit Residents', 'slug' => 'residents.edit', 'group' => 'Resident Management', 'description' => 'Edit resident profiles'],
            ['name' => 'Delete Residents', 'slug' => 'residents.delete', 'group' => 'Resident Management', 'description' => 'Delete resident profiles'],
            ['name' => 'Import Residents', 'slug' => 'residents.import', 'group' => 'Resident Management', 'description' => 'Bulk import residents via CSV'],

            // Visitor Management & Gatekeeper
            ['name' => 'View Visitor Logs', 'slug' => 'visitor_logs.view', 'group' => 'Visitor Management', 'description' => 'View visitor log history'],
            ['name' => 'Mark Entry / Exit', 'slug' => 'visitor_logs.entry_exit', 'group' => 'Visitor Management', 'description' => 'Mark visitor entry and exit'],
            ['name' => 'Scan QR Pass', 'slug' => 'visitor_logs.scan', 'group' => 'Visitor Management', 'description' => 'Scan visitor pass QR codes at gate'],
            ['name' => 'View Visitor Passes', 'slug' => 'passes.view', 'group' => 'Visitor Management', 'description' => 'View visitor passes'],
            ['name' => 'Create Visitor Pass', 'slug' => 'passes.create', 'group' => 'Visitor Management', 'description' => 'Generate new visitor pass'],
            ['name' => 'Edit Visitor Pass', 'slug' => 'passes.edit', 'group' => 'Visitor Management', 'description' => 'Edit visitor pass'],
            ['name' => 'Approve / Reject Pass', 'slug' => 'passes.approve_reject', 'group' => 'Visitor Management', 'description' => 'Approve or reject visitor pass requests'],
            ['name' => 'Cancel Visitor Pass', 'slug' => 'passes.cancel', 'group' => 'Visitor Management', 'description' => 'Cancel visitor pass'],

            // Deliveries
            ['name' => 'View Deliveries', 'slug' => 'deliveries.view', 'group' => 'Deliveries', 'description' => 'View package deliveries'],
            ['name' => 'Create Delivery', 'slug' => 'deliveries.create', 'group' => 'Deliveries', 'description' => 'Log new incoming package delivery'],
            ['name' => 'Edit Delivery', 'slug' => 'deliveries.edit', 'group' => 'Deliveries', 'description' => 'Edit package delivery details'],
            ['name' => 'Mark Delivered', 'slug' => 'deliveries.deliver', 'group' => 'Deliveries', 'description' => 'Mark package as handed over to resident'],
            ['name' => 'Delete Delivery', 'slug' => 'deliveries.delete', 'group' => 'Deliveries', 'description' => 'Delete delivery logs'],

            // Complaints
            ['name' => 'View Complaints', 'slug' => 'complaints.view', 'group' => 'Complaints', 'description' => 'View resident complaints'],
            ['name' => 'Create Complaint', 'slug' => 'complaints.create', 'group' => 'Complaints', 'description' => 'Submit new complaint'],
            ['name' => 'Edit Complaint', 'slug' => 'complaints.edit', 'group' => 'Complaints', 'description' => 'Edit complaint details'],
            ['name' => 'Resolve Complaint', 'slug' => 'complaints.resolve', 'group' => 'Complaints', 'description' => 'Update status and resolve complaints'],
            ['name' => 'Delete Complaint', 'slug' => 'complaints.delete', 'group' => 'Complaints', 'description' => 'Delete complaint entries'],

            // System Reports & Audits
            ['name' => 'View Reports', 'slug' => 'reports.view', 'group' => 'Reports & Audits', 'description' => 'Access system analytics and reports'],
            ['name' => 'Export Reports', 'slug' => 'reports.export', 'group' => 'Reports & Audits', 'description' => 'Download CSV reports'],
            ['name' => 'View Activity Logs', 'slug' => 'activity_logs.view', 'group' => 'Reports & Audits', 'description' => 'View system activity audit trail'],
            ['name' => 'View Auth Audit', 'slug' => 'auth_audit.view', 'group' => 'Reports & Audits', 'description' => 'View login and authentication logs'],
            ['name' => 'Impersonate User', 'slug' => 'impersonate.user', 'group' => 'Reports & Audits', 'description' => 'Impersonate user sessions'],

            // Permissions Management
            ['name' => 'Manage Permissions', 'slug' => 'permissions.manage', 'group' => 'Permissions', 'description' => 'Manage user and role permission assignments'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(['slug' => $permissionData['slug']], $permissionData);
        }

        $allPermissions = Permission::all();

        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdminRole->permissions()->sync($allPermissions->pluck('id'));

        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminSlugs = [
            'wings.view', 'wings.manage',
            'flats.view', 'flats.create', 'flats.edit', 'flats.delete', 'flats.export',
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.restore',
            'residents.view', 'residents.create', 'residents.edit', 'residents.delete', 'residents.import',
            'visitor_logs.view', 'visitor_logs.entry_exit',
            'passes.view', 'passes.create', 'passes.edit', 'passes.approve_reject', 'passes.cancel',
            'deliveries.view', 'deliveries.create', 'deliveries.edit', 'deliveries.deliver', 'deliveries.delete',
            'complaints.view', 'complaints.create', 'complaints.edit', 'complaints.resolve', 'complaints.delete',
            'reports.view', 'reports.export', 'activity_logs.view', 'auth_audit.view', 'permissions.manage',
        ];
        $adminRole->permissions()->sync(
            Permission::whereIn('slug', $adminSlugs)->pluck('id')
        );

        $residentRole = Role::firstOrCreate(['name' => 'resident']);
        $residentSlugs = [
            'passes.view', 'passes.create', 'passes.approve_reject', 'passes.cancel',
            'deliveries.view',
            'complaints.view', 'complaints.create',
        ];
        $residentRole->permissions()->sync(
            Permission::whereIn('slug', $residentSlugs)->pluck('id')
        );

        $gatekeeperRole = Role::firstOrCreate(['name' => 'gatekeeper']);
        $gatekeeperSlugs = [
            'visitor_logs.view', 'visitor_logs.entry_exit', 'visitor_logs.scan',
            'passes.view',
            'deliveries.view', 'deliveries.create', 'deliveries.deliver',
            'complaints.view', 'complaints.create',
        ];
        $gatekeeperRole->permissions()->sync(
            Permission::whereIn('slug', $gatekeeperSlugs)->pluck('id')
        );
    }
}
