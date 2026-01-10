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
            ['email' => 'admin@hotels.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
                'is_super_admin' => true,
            ]
        );

        $this->command->info('Super Admin user created!');
        $this->command->info('Email: admin@hotels.com');
        $this->command->info('Password: admin123');
    }
}
