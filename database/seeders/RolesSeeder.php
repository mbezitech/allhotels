<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Manager Role - Full access including user management
        $manager = Role::updateOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Manager',
                'description' => 'Full access to hotel operations including user management'
            ]
        );

        $managerPermissions = [
            // Rooms
            'rooms.view', 'rooms.manage', 'rooms.edit',
            'room_types.view', 'room_types.manage', 'room_types.edit',
            // Bookings
            'bookings.view', 'bookings.create', 'bookings.edit', 'bookings.delete', 'bookings.manage',
            // POS
            'pos.view', 'pos.sell', 'pos.edit', 'pos.delete', 'pos.manage',
            // Stock/Products
            'stock.view', 'stock.manage', 'stock.edit',
            'extras.view', 'extras.manage', 'extras.edit',
            'extra_categories.view', 'extra_categories.manage', 'extra_categories.edit',
            // Payments
            'payments.view', 'payments.create', 'payments.edit', 'payments.delete', 'payments.manage',
            // Reports
            'reports.view',
            // Housekeeping
            'housekeeping.view', 'housekeeping.manage', 'housekeeping.edit',
            'housekeeping_records.view', 'housekeeping_records.manage', 'housekeeping_records.edit',
            'housekeeping_reports.view',
            'hotel_areas.view', 'hotel_areas.manage', 'hotel_areas.edit',
            'tasks.view', 'tasks.manage', 'tasks.edit',
            // Activity Logs
            'activity_logs.view',
            // Users
            'users.view', 'users.manage', 'users.edit', 'users.activate',
        ];

        $manager->permissions()->sync(
            Permission::whereIn('slug', $managerPermissions)->pluck('id')
        );

        // Receptionist Role - Bookings and basic operations
        $receptionist = Role::updateOrCreate(
            ['slug' => 'receptionist'],
            [
                'name' => 'Receptionist',
                'description' => 'Handle bookings, view rooms, and process payments'
            ]
        );

        $receptionistPermissions = [
            // Rooms
            'rooms.view',
            'room_types.view',
            // Bookings
            'bookings.view', 'bookings.create', 'bookings.edit',
            // POS
            'pos.view', 'pos.sell',
            // Payments
            'payments.view', 'payments.create',
            // Housekeeping
            'housekeeping.view',
            'housekeeping_records.view',
            'tasks.view',
        ];

        $receptionist->permissions()->sync(
            Permission::whereIn('slug', $receptionistPermissions)->pluck('id')
        );

        // Staff Role - POS and basic viewing
        $staff = Role::updateOrCreate(
            ['slug' => 'staff'],
            [
                'name' => 'Staff',
                'description' => 'POS operations and basic viewing'
            ]
        );

        $staffPermissions = [
            // POS
            'pos.view', 'pos.sell',
            // Stock
            'stock.view',
            'extras.view',
        ];

        $staff->permissions()->sync(
            Permission::whereIn('slug', $staffPermissions)->pluck('id')
        );

        // Admin Role - Everything including user/role management
        $admin = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Full access including user and role management'
            ]
        );

        $admin->permissions()->sync(Permission::pluck('id'));

        $this->command->info('Roles seeded successfully!');
        $this->command->info('Created roles: Manager, Receptionist, Staff, Admin');
    }
}
