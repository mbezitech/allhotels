<?php

namespace Database\Seeders;

use App\Models\HotelArea;
use App\Models\Hotel;
use Illuminate\Database\Seeder;

class HotelAreasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultAreas = [
            ['name' => 'Reception', 'description' => 'Main reception area'],
            ['name' => 'Lobby', 'description' => 'Hotel lobby and entrance area'],
            ['name' => 'Corridors', 'description' => 'Hallways and corridors'],
            ['name' => 'Toilets', 'description' => 'Public restrooms'],
            ['name' => 'Restaurant', 'description' => 'Restaurant area'],
            ['name' => 'Bar', 'description' => 'Bar area'],
            ['name' => 'Pool Area', 'description' => 'Swimming pool and surrounding area'],
        ];

        // Create default areas for each hotel
        $hotels = Hotel::all();
        
        foreach ($hotels as $hotel) {
            foreach ($defaultAreas as $areaData) {
                // Check if area already exists for this hotel
                $exists = HotelArea::where('hotel_id', $hotel->id)
                    ->where('name', $areaData['name'])
                    ->exists();
                
                if (!$exists) {
                    HotelArea::create([
                        'hotel_id' => $hotel->id,
                        'name' => $areaData['name'],
                        'slug' => \Illuminate\Support\Str::slug($areaData['name']),
                        'description' => $areaData['description'],
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}
