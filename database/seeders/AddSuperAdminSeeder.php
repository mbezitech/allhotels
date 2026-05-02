<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AddSuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'innocenfrancismhina@gmail.com';
        
        // Check if user exists
        $user = User::where('email', $email)->first();
        
        if ($user) {
            // Update existing user to super admin
            $user->update([
                'is_super_admin' => true,
                'is_active' => true,
            ]);
            $this->command->info("User {$email} updated to Super Admin!");
        } else {
            // Create new super admin user
            User::create([
                'name' => 'Innocent Francis Mhina',
                'email' => $email,
                'password' => Hash::make('SuperAdmin2026!'),
                'is_super_admin' => true,
                'is_active' => true,
            ]);
            $this->command->info('Super Admin user created successfully!');
        }
        
        $this->command->info("Email: {$email}");
        $this->command->info('Password: SuperAdmin2026!');
        $this->command->info('Please change the password after first login.');
    }
}
