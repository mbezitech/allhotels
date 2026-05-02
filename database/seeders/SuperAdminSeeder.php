<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin user
        User::firstOrCreate(
            ['email' => 'superadmin@allhotels.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('SuperAdmin2026!'),
                'is_super_admin' => true,
                'is_active' => true,
            ]
        );

        $this->command->info('Super Admin user created successfully!');
        $this->command->info('Username: superadmin@allhotels.com');
        $this->command->info('Password: SuperAdmin2026!');
    }
}
