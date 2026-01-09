<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Room;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    /**
     * Display a list of useful links (public URLs) for the current hotel.
     */
    public function index()
    {
        $hotelId = session('hotel_id');
        $hotel = Hotel::findOrFail($hotelId);

        $rooms = Room::where('hotel_id', $hotelId)
            ->orderBy('room_number')
            ->get();

        return view('links.index', compact('hotel', 'rooms'));
    }
}


