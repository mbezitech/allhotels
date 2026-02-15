<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTypeRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_room_type_rooms_relationship_works()
    {
        $hotel = Hotel::factory()->create();
        $roomType = RoomType::factory()->create([
            'hotel_id' => $hotel->id,
            'slug' => 'single'
        ]);
        
        $room = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id
        ]);

        // This would fail if the relationship key was 'room_type' column
        $this->assertTrue($roomType->rooms()->exists());
        $this->assertEquals($room->id, $roomType->rooms->first()->id);
    }
}
