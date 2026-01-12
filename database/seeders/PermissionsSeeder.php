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
            // ============================================
            // DASHBOARD MODULE
            // ============================================
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'description' => 'Access and view the dashboard'],
            
            // ============================================
            // ROOMS MODULE
            // ============================================
            ['name' => 'View Rooms', 'slug' => 'rooms.view', 'description' => 'View room list and details'],
            ['name' => 'Manage Rooms', 'slug' => 'rooms.manage', 'description' => 'Create and manage rooms'],
            ['name' => 'Edit Rooms', 'slug' => 'rooms.edit', 'description' => 'Edit existing rooms'],
            ['name' => 'Delete Rooms', 'slug' => 'rooms.delete', 'description' => 'Delete rooms'],
            
            // Room Types
            ['name' => 'View Room Types', 'slug' => 'room_types.view', 'description' => 'View room types list and details'],
            ['name' => 'Manage Room Types', 'slug' => 'room_types.manage', 'description' => 'Create, edit, and delete room types'],
            ['name' => 'Edit Room Types', 'slug' => 'room_types.edit', 'description' => 'Edit existing room types'],
            
            // ============================================
            // BOOKINGS MODULE
            // ============================================
            ['name' => 'View Bookings', 'slug' => 'bookings.view', 'description' => 'View booking list and details'],
            ['name' => 'Create Booking', 'slug' => 'bookings.create', 'description' => 'Create new bookings'],
            ['name' => 'Edit Booking', 'slug' => 'bookings.edit', 'description' => 'Edit existing bookings'],
            ['name' => 'Delete Booking', 'slug' => 'bookings.delete', 'description' => 'Delete bookings'],
            ['name' => 'Manage Bookings', 'slug' => 'bookings.manage', 'description' => 'Full booking management (create, edit, delete)'],
            
            // ============================================
            // POS SALES MODULE
            // ============================================
            ['name' => 'View POS Sales', 'slug' => 'pos.view', 'description' => 'View POS sales history'],
            ['name' => 'Sell POS', 'slug' => 'pos.sell', 'description' => 'Process POS sales'],
            ['name' => 'Edit POS Sales', 'slug' => 'pos.edit', 'description' => 'Edit existing POS sales'],
            ['name' => 'Delete POS Sales', 'slug' => 'pos.delete', 'description' => 'Delete POS sales'],
            ['name' => 'Manage POS Sales', 'slug' => 'pos.manage', 'description' => 'Full POS sales management (create, edit, delete)'],
            
            // ============================================
            // STOCK/EXTRAS MODULE
            // ============================================
            ['name' => 'View Stock', 'slug' => 'stock.view', 'description' => 'View stock levels and movements'],
            ['name' => 'Manage Stock', 'slug' => 'stock.manage', 'description' => 'Add, adjust, and manage stock'],
            ['name' => 'Edit Stock', 'slug' => 'stock.edit', 'description' => 'Edit stock levels and products'],
            
            // Extras/Products
            ['name' => 'View Products', 'slug' => 'extras.view', 'description' => 'View products/extras list and details'],
            ['name' => 'Manage Products', 'slug' => 'extras.manage', 'description' => 'Create, edit, and delete products/extras'],
            ['name' => 'Edit Products', 'slug' => 'extras.edit', 'description' => 'Edit existing products/extras'],
            
            // Extra Categories
            ['name' => 'View Product Categories', 'slug' => 'extra_categories.view', 'description' => 'View product categories list'],
            ['name' => 'Manage Product Categories', 'slug' => 'extra_categories.manage', 'description' => 'Create, edit, and delete product categories'],
            ['name' => 'Edit Product Categories', 'slug' => 'extra_categories.edit', 'description' => 'Edit existing product categories'],
            
            // ============================================
            // PAYMENTS MODULE
            // ============================================
            ['name' => 'View Payments', 'slug' => 'payments.view', 'description' => 'View payment history'],
            ['name' => 'Create Payment', 'slug' => 'payments.create', 'description' => 'Record payments for bookings and POS'],
            ['name' => 'Edit Payment', 'slug' => 'payments.edit', 'description' => 'Edit existing payments'],
            ['name' => 'Delete Payment', 'slug' => 'payments.delete', 'description' => 'Delete payments'],
            ['name' => 'Manage Payments', 'slug' => 'payments.manage', 'description' => 'Full payment management (create, edit, delete)'],
            
            // ============================================
            // HOUSEKEEPING MODULE
            // ============================================
            ['name' => 'View Housekeeping', 'slug' => 'housekeeping.view', 'description' => 'View housekeeping/maintenance tasks and records'],
            ['name' => 'Manage Housekeeping', 'slug' => 'housekeeping.manage', 'description' => 'Create, edit, and manage housekeeping/maintenance tasks'],
            ['name' => 'Edit Housekeeping', 'slug' => 'housekeeping.edit', 'description' => 'Edit existing housekeeping records'],
            
            // Housekeeping Records
            ['name' => 'View Housekeeping Records', 'slug' => 'housekeeping_records.view', 'description' => 'View housekeeping records'],
            ['name' => 'Manage Housekeeping Records', 'slug' => 'housekeeping_records.manage', 'description' => 'Create, edit, and manage housekeeping records'],
            ['name' => 'Edit Housekeeping Records', 'slug' => 'housekeeping_records.edit', 'description' => 'Edit existing housekeeping records'],
            ['name' => 'Inspect & Approve Housekeeping', 'slug' => 'housekeeping_records.inspect', 'description' => 'Inspect and approve housekeeping records (mark as inspected/ready)'],
            ['name' => 'Resolve Housekeeping Issues', 'slug' => 'housekeeping_records.resolve', 'description' => 'Resolve issues found during housekeeping inspections'],
            
            // Housekeeping Reports
            ['name' => 'View Housekeeping Reports', 'slug' => 'housekeeping_reports.view', 'description' => 'View housekeeping reports and analytics'],
            
            // Hotel Areas
            ['name' => 'View Hotel Areas', 'slug' => 'hotel_areas.view', 'description' => 'View hotel areas list'],
            ['name' => 'Manage Hotel Areas', 'slug' => 'hotel_areas.manage', 'description' => 'Create, edit, and delete hotel areas'],
            ['name' => 'Edit Hotel Areas', 'slug' => 'hotel_areas.edit', 'description' => 'Edit existing hotel areas'],
            
            // Tasks
            ['name' => 'View Tasks', 'slug' => 'tasks.view', 'description' => 'View tasks list and details'],
            ['name' => 'Create Tasks', 'slug' => 'tasks.create', 'description' => 'Create new tasks'],
            ['name' => 'Manage Tasks', 'slug' => 'tasks.manage', 'description' => 'Delete tasks'],
            ['name' => 'Edit Tasks', 'slug' => 'tasks.edit', 'description' => 'Edit existing tasks'],
            
            // ============================================
            // REPORTS MODULE
            // ============================================
            ['name' => 'View Reports', 'slug' => 'reports.view', 'description' => 'Access all reports (sales, occupancy, stock)'],
            
            // ============================================
            // USER & ROLE MANAGEMENT MODULE
            // ============================================
            ['name' => 'View Users', 'slug' => 'users.view', 'description' => 'View list of users'],
            ['name' => 'Manage Users', 'slug' => 'users.manage', 'description' => 'Create, edit, and manage users'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'description' => 'Edit existing users'],
            ['name' => 'Activate/Deactivate Users', 'slug' => 'users.activate', 'description' => 'Activate or deactivate user accounts'],
            
            ['name' => 'View Roles', 'slug' => 'roles.view', 'description' => 'View roles list and details'],
            ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'description' => 'Create, edit roles and assign permissions'],
            ['name' => 'Edit Roles', 'slug' => 'roles.edit', 'description' => 'Edit existing roles and permissions'],
            
            // ============================================
            // ACTIVITY LOGS MODULE
            // ============================================
            ['name' => 'View Activity Logs', 'slug' => 'activity_logs.view', 'description' => 'View system activity logs and audit trail'],
            
            // ============================================
            // EMAIL SETTINGS MODULE
            // ============================================
            ['name' => 'View Email Settings', 'slug' => 'email_settings.view', 'description' => 'View email settings'],
            ['name' => 'Manage Email Settings', 'slug' => 'email_settings.manage', 'description' => 'Create, edit, and manage email settings'],
            
            // ============================================
            // EXPENSES MODULE
            // ============================================
            ['name' => 'View Expenses', 'slug' => 'expenses.view', 'description' => 'View expenses list'],
            ['name' => 'Create Expenses', 'slug' => 'expenses.create', 'description' => 'Add new expenses'],
            ['name' => 'Edit Expenses', 'slug' => 'expenses.edit', 'description' => 'Edit existing expenses'],
            ['name' => 'Delete Expenses', 'slug' => 'expenses.delete', 'description' => 'Delete expenses'],
            ['name' => 'Manage Expenses', 'slug' => 'expenses.manage', 'description' => 'Full access to manage expenses'],
            
            ['name' => 'View Expense Categories', 'slug' => 'expense_categories.view', 'description' => 'View expense categories'],
            ['name' => 'Manage Expense Categories', 'slug' => 'expense_categories.manage', 'description' => 'Create, edit, and delete expense categories'],
            ['name' => 'Edit Expense Categories', 'slug' => 'expense_categories.edit', 'description' => 'Edit expense categories'],
            
            ['name' => 'View Expense Reports', 'slug' => 'expense_reports.view', 'description' => 'View expense reports and summaries'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $this->command->info('Permissions seeded successfully!');
        $this->command->info('Total permissions: ' . count($permissions));
    }
}
