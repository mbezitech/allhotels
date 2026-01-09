<?php

namespace Database\Seeders;

use App\Models\ExtraCategory;
use App\Models\Hotel;
use Illuminate\Database\Seeder;

class ExtraCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultCategories = [
            'Bar',
            'Pool',
            'Restaurant',
            'General',
        ];

        // Create default categories for each hotel
        $hotels = Hotel::all();
        
        foreach ($hotels as $hotel) {
            foreach ($defaultCategories as $categoryName) {
                // Check if category already exists for this hotel
                $exists = ExtraCategory::where('hotel_id', $hotel->id)
                    ->where('name', $categoryName)
                    ->exists();
                
                if (!$exists) {
                    ExtraCategory::create([
                        'hotel_id' => $hotel->id,
                        'name' => $categoryName,
                        'slug' => \Illuminate\Support\Str::slug($categoryName),
                        'description' => "Default {$categoryName} category",
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
