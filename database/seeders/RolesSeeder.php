<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates roles for a specific hotel or all hotels if hotel_id is null.
     */
    public function run(?int $hotelId = null): void
    {
        // If no hotel_id provided, get all hotels
        if ($hotelId) {
            $hotel = \App\Models\Hotel::find($hotelId);
            if (!$hotel) {
                if ($this->command) {
                    $this->command->warn("Hotel with ID {$hotelId} not found.");
                }
                return;
            }
            $hotels = collect([$hotel]);
        } else {
            $hotels = \App\Models\Hotel::all();
        }
        
        if ($hotels->isEmpty()) {
            if ($this->command) {
                $this->command->warn('No hotels found. Please create a hotel first.');
            }
            return;
        }
        
        foreach ($hotels as $hotel) {
            // Manager Role - Full access including user management
            $manager = Role::updateOrCreate(
                ['slug' => 'manager', 'hotel_id' => $hotel->id],
                [
                    'name' => 'Manager',
                    'description' => 'Full access to hotel operations including user management',
                    'hotel_id' => $hotel->id
                ]
            );

        $managerPermissions = [
            // Dashboard
            'dashboard.view',
            // Rooms
            'rooms.view', 'rooms.manage', 'rooms.edit', 'rooms.delete',
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
            'housekeeping_records.view', 'housekeeping_records.manage', 'housekeeping_records.edit', 'housekeeping_records.inspect', 'housekeeping_records.resolve',
            'housekeeping_reports.view',
            'hotel_areas.view', 'hotel_areas.manage', 'hotel_areas.edit',
            'tasks.view', 'tasks.create', 'tasks.manage', 'tasks.edit',
            // Activity Logs
            'activity_logs.view',
            // Email Settings
            'email_settings.view', 'email_settings.manage',
            // Expenses
            'expenses.view', 'expenses.create', 'expenses.edit', 'expenses.delete', 'expenses.manage',
            'expense_categories.view', 'expense_categories.manage', 'expense_categories.edit',
            'expense_reports.view',
            // Users
            'users.view', 'users.manage', 'users.edit', 'users.activate',
        ];

            $managerPermissionIds = Permission::where('hotel_id', $hotel->id)
                ->whereIn('slug', $managerPermissions)
                ->pluck('id')
                ->toArray();
            
            // Detach all existing permissions first
            $manager->permissions()->detach();
            
            // Attach permissions with hotel_id in pivot
            foreach ($managerPermissionIds as $permissionId) {
                DB::table('role_permissions')->insert([
                    'role_id' => $manager->id,
                    'permission_id' => $permissionId,
                    'hotel_id' => $hotel->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Receptionist Role - Bookings and basic operations
            $receptionist = Role::updateOrCreate(
                ['slug' => 'receptionist', 'hotel_id' => $hotel->id],
                [
                    'name' => 'Receptionist',
                    'description' => 'Handle bookings, view rooms, and process payments',
                    'hotel_id' => $hotel->id
                ]
            );

        $receptionistPermissions = [
            // Dashboard
            'dashboard.view',
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
            'tasks.view', 'tasks.create',
        ];

            $receptionistPermissionIds = Permission::where('hotel_id', $hotel->id)
                ->whereIn('slug', $receptionistPermissions)
                ->pluck('id')
                ->toArray();
            
            // Detach all existing permissions first
            $receptionist->permissions()->detach();
            
            // Attach permissions with hotel_id in pivot
            foreach ($receptionistPermissionIds as $permissionId) {
                DB::table('role_permissions')->insert([
                    'role_id' => $receptionist->id,
                    'permission_id' => $permissionId,
                    'hotel_id' => $hotel->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Staff Role - POS and basic viewing
            $staff = Role::updateOrCreate(
                ['slug' => 'staff', 'hotel_id' => $hotel->id],
                [
                    'name' => 'Staff',
                    'description' => 'POS operations and basic viewing',
                    'hotel_id' => $hotel->id
                ]
            );

        $staffPermissions = [
            // Dashboard
            'dashboard.view',
            // POS
            'pos.view', 'pos.sell',
            // Stock
            'stock.view',
            'extras.view',
        ];

            $staffPermissionIds = Permission::where('hotel_id', $hotel->id)
                ->whereIn('slug', $staffPermissions)
                ->pluck('id')
                ->toArray();
            
            // Detach all existing permissions first
            $staff->permissions()->detach();
            
            // Attach permissions with hotel_id in pivot
            foreach ($staffPermissionIds as $permissionId) {
                DB::table('role_permissions')->insert([
                    'role_id' => $staff->id,
                    'permission_id' => $permissionId,
                    'hotel_id' => $hotel->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Admin Role - Everything including user/role management
            $admin = Role::updateOrCreate(
                ['slug' => 'admin', 'hotel_id' => $hotel->id],
                [
                    'name' => 'Admin',
                    'description' => 'Full access including user and role management',
                    'hotel_id' => $hotel->id
                ]
            );

            $adminPermissionIds = Permission::where('hotel_id', $hotel->id)
                ->pluck('id')
                ->toArray();
            
            // Detach all existing permissions first
            $admin->permissions()->detach();
            
            // Attach permissions with hotel_id in pivot
            foreach ($adminPermissionIds as $permissionId) {
                DB::table('role_permissions')->insert([
                    'role_id' => $admin->id,
                    'permission_id' => $permissionId,
                    'hotel_id' => $hotel->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($this->command) {
            $this->command->info('Roles seeded successfully!');
            $this->command->info('Created roles per hotel: Manager, Receptionist, Staff, Admin');
            $this->command->info('Total hotels processed: ' . $hotels->count());
        }
    }
}
