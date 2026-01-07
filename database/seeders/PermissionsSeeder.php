<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Rooms Management
            ['name' => 'Manage Rooms', 'slug' => 'rooms.manage', 'description' => 'Create, edit, and delete rooms'],
            ['name' => 'View Rooms', 'slug' => 'rooms.view', 'description' => 'View room list and details'],
            
            // Bookings Management
            ['name' => 'Create Booking', 'slug' => 'bookings.create', 'description' => 'Create new bookings'],
            ['name' => 'Edit Booking', 'slug' => 'bookings.edit', 'description' => 'Edit existing bookings'],
            ['name' => 'Delete Booking', 'slug' => 'bookings.delete', 'description' => 'Delete bookings'],
            ['name' => 'View Bookings', 'slug' => 'bookings.view', 'description' => 'View booking list and details'],
            
            // POS Management
            ['name' => 'Sell POS', 'slug' => 'pos.sell', 'description' => 'Process POS sales'],
            ['name' => 'View POS', 'slug' => 'pos.view', 'description' => 'View POS sales history'],
            
            // Stock Management
            ['name' => 'Manage Stock', 'slug' => 'stock.manage', 'description' => 'Add, adjust, and manage stock'],
            ['name' => 'View Stock', 'slug' => 'stock.view', 'description' => 'View stock levels and movements'],
            
            // Payments
            ['name' => 'Create Payment', 'slug' => 'payments.create', 'description' => 'Record payments for bookings and POS'],
            ['name' => 'View Payments', 'slug' => 'payments.view', 'description' => 'View payment history'],
            
            // Reports
            ['name' => 'View Reports', 'slug' => 'reports.view', 'description' => 'Access all reports (sales, occupancy, stock)'],
            
            // User & Role Management
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'description' => 'Create, edit, and manage users'],
            ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'description' => 'Create, edit roles and assign permissions'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $this->command->info('Permissions seeded successfully!');
    }
}
