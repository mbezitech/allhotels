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
        // Manager Role - Full access except user/role management
        $manager = Role::updateOrCreate(
            ['slug' => 'manager'],
            [
                'name' => 'Manager',
                'description' => 'Full access to hotel operations except user management'
            ]
        );

        $managerPermissions = [
            'rooms.manage', 'rooms.view',
            'bookings.create', 'bookings.edit', 'bookings.delete', 'bookings.view',
            'pos.sell', 'pos.view',
            'stock.manage', 'stock.view',
            'payments.create', 'payments.view',
            'reports.view',
            'housekeeping.manage', 'housekeeping.view',
            'activity_logs.view',
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
            'rooms.view',
            'bookings.create', 'bookings.edit', 'bookings.view',
            'pos.sell', 'pos.view',
            'payments.create', 'payments.view',
            'housekeeping.view',
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
            'pos.sell', 'pos.view',
            'stock.view',
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
