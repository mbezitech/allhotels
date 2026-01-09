<?php

namespace App\Http\Controllers;

use App\Models\HotelArea;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HotelAreaController extends Controller
{
    /**
     * Display a listing of hotel areas
     */
    public function index()
    {
        $hotelId = session('hotel_id');
        $areas = HotelArea::where('hotel_id', $hotelId)
            ->orderBy('name')
            ->get();

        return view('hotel-areas.index', compact('areas'));
    }

    /**
     * Show the form for creating a new hotel area
     */
    public function create()
    {
        return view('hotel-areas.create');
    }

    /**
     * Store a newly created hotel area
     */
    public function store(Request $request)
    {
        $hotelId = session('hotel_id');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('hotel_areas')->where(fn ($query) => $query->where('hotel_id', $hotelId))],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['hotel_id'] = $hotelId;
        $area = HotelArea::create($validated);

        logActivity('created', $area, "Created hotel area: {$area->name}");

        return redirect()->route('hotel-areas.index')
            ->with('success', 'Hotel area created successfully.');
    }

    /**
     * Display the specified hotel area
     */
    public function show(HotelArea $hotelArea)
    {
        $this->authorizeHotel($hotelArea);
        $hotelArea->load('housekeepingRecords.assignedTo');
        return view('hotel-areas.show', compact('hotelArea'));
    }

    /**
     * Show the form for editing the specified hotel area
     */
    public function edit(HotelArea $hotelArea)
    {
        $this->authorizeHotel($hotelArea);
        return view('hotel-areas.edit', compact('hotelArea'));
    }

    /**
     * Update the specified hotel area
     */
    public function update(Request $request, HotelArea $hotelArea)
    {
        $this->authorizeHotel($hotelArea);
        $hotelId = session('hotel_id');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('hotel_areas')->where(fn ($query) => $query->where('hotel_id', $hotelId))->ignore($hotelArea->id)],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $hotelArea->update($validated);

        logActivity('updated', $hotelArea, "Updated hotel area: {$hotelArea->name}");

        return redirect()->route('hotel-areas.index')
            ->with('success', 'Hotel area updated successfully.');
    }

    /**
     * Remove the specified hotel area
     */
    public function destroy(HotelArea $hotelArea)
    {
        $this->authorizeHotel($hotelArea);

        if ($hotelArea->housekeepingRecords()->exists()) {
            return back()->with('error', 'Cannot delete area: housekeeping records are associated with it.');
        }

        $areaName = $hotelArea->name;
        $hotelArea->delete();

        logActivity('deleted', null, "Deleted hotel area: {$areaName}", ['area_id' => $hotelArea->id]);

        return redirect()->route('hotel-areas.index')
            ->with('success', 'Hotel area deleted successfully.');
    }

    /**
     * Ensure area belongs to current hotel
     */
    private function authorizeHotel(HotelArea $area)
    {
        if ($area->hotel_id != session('hotel_id')) {
            abort(403, 'Unauthorized access to this area.');
        }
    }
}
