<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SampleHotelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create an owner user
        $owner = User::firstOrCreate(
            ['email' => 'owner@hotels.com'],
            [
                'name' => 'Hotel Owner',
                'password' => bcrypt('password'),
                'is_super_admin' => false,
            ]
        );

        // Create sample hotels if they don't exist
        $hotels = [
            [
                'name' => 'Grand Plaza Hotel',
                'address' => '123 Main Street, Downtown, City 12345',
                'owner_id' => $owner->id,
            ],
            [
                'name' => 'Oceanview Resort',
                'address' => '456 Beach Boulevard, Coastal Area, City 67890',
                'owner_id' => $owner->id,
            ],
        ];

        foreach ($hotels as $hotelData) {
            Hotel::firstOrCreate(
                ['name' => $hotelData['name']],
                $hotelData
            );
        }

        $this->command->info('Sample hotels created successfully!');
    }
}
