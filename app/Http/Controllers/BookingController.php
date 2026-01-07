<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings for current hotel
     */
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        
        $query = Booking::where('hotel_id', $hotelId)
            ->with('room');

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->where('check_in', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->where('check_out', '<=', $request->to_date);
        }

        $bookings = $query->orderBy('check_in', 'desc')->paginate(20);

        return view('bookings.index', compact('bookings'));
    }

    /**
     * Show the form for creating a new booking
     */
    public function create()
    {
        $hotelId = session('hotel_id');
        $rooms = Room::where('hotel_id', $hotelId)
            ->where('status', 'available')
            ->orderBy('room_number')
            ->get();

        return view('bookings.create', compact('rooms'));
    }

    /**
     * Store a newly created booking
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:255',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'total_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $hotelId = session('hotel_id');
        $room = Room::findOrFail($validated['room_id']);

        // Ensure room belongs to current hotel
        if ($room->hotel_id != $hotelId) {
            abort(403, 'Unauthorized access to this room.');
        }

        // Check room availability
        if (!$room->isAvailableForDates($validated['check_in'], $validated['check_out'])) {
            throw ValidationException::withMessages([
                'room_id' => 'Room is not available for the selected dates.',
            ]);
        }

        $validated['hotel_id'] = $hotelId;
        $validated['status'] = 'confirmed';

        $booking = Booking::create($validated);
        logActivity('created', $booking, "Created booking for {$booking->guest_name} - Room {$booking->room->room_number}");

        return redirect()->route('bookings.index')
            ->with('success', 'Booking created successfully.');
    }

    /**
     * Display the specified booking
     */
    public function show(Booking $booking)
    {
        // Route model binding already ensures booking belongs to current hotel
        $booking->load('room', 'payments');
        
        return view('bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified booking
     */
    public function edit(Booking $booking)
    {
        $this->authorizeHotel($booking);
        
        $hotelId = session('hotel_id');
        $rooms = Room::where('hotel_id', $hotelId)
            ->orderBy('room_number')
            ->get();

        return view('bookings.edit', compact('booking', 'rooms'));
    }

    /**
     * Update the specified booking
     */
    public function update(Request $request, Booking $booking)
    {
        $this->authorizeHotel($booking);

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
            'guest_phone' => 'nullable|string|max:255',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'total_amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled',
            'notes' => 'nullable|string',
        ]);

        $room = Room::findOrFail($validated['room_id']);

        // Check room availability (excluding current booking)
        if (!$room->isAvailableForDates($validated['check_in'], $validated['check_out'], $booking->id)) {
            throw ValidationException::withMessages([
                'room_id' => 'Room is not available for the selected dates.',
            ]);
        }

        $booking->update($validated);
        logActivity('updated', $booking, "Updated booking #{$booking->id} for {$booking->guest_name}");

        return redirect()->route('bookings.index')
            ->with('success', 'Booking updated successfully.');
    }

    /**
     * Remove the specified booking
     */
    public function destroy(Booking $booking)
    {
        $this->authorizeHotel($booking);

        // Only allow deletion of pending or cancelled bookings
        if (!in_array($booking->status, ['pending', 'cancelled'])) {
            return redirect()->route('bookings.index')
                ->with('error', 'Cannot delete confirmed or active bookings.');
        }

        $guestName = $booking->guest_name;
        $bookingId = $booking->id;
        $booking->delete();

        logActivity('deleted', null, "Deleted booking #{$bookingId} for {$guestName}", ['booking_id' => $bookingId]);

        return redirect()->route('bookings.index')
            ->with('success', 'Booking deleted successfully.');
    }

    /**
     * Display calendar view of bookings
     */
    public function calendar(Request $request)
    {
        $hotelId = session('hotel_id');
        
        // Get month and year from request or use current
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        // Get bookings for the month
        $bookings = Booking::where('hotel_id', $hotelId)
            ->whereYear('check_in', $year)
            ->whereMonth('check_in', $month)
            ->orWhere(function ($query) use ($year, $month, $hotelId) {
                $query->where('hotel_id', $hotelId)
                      ->whereYear('check_out', $year)
                      ->whereMonth('check_out', $month);
            })
            ->with('room')
            ->get();
        
        // Create calendar data
        $firstDay = \Carbon\Carbon::create($year, $month, 1);
        $lastDay = $firstDay->copy()->endOfMonth();
        $startDate = $firstDay->copy()->startOfWeek();
        $endDate = $lastDay->copy()->endOfWeek();
        
        $calendar = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dayBookings = $bookings->filter(function ($booking) use ($currentDate) {
                return $currentDate->between($booking->check_in, $booking->check_out);
            });
            
            $calendar[] = [
                'date' => $currentDate->copy(),
                'isCurrentMonth' => $currentDate->month == $month,
                'isToday' => $currentDate->isToday(),
                'bookings' => $dayBookings,
            ];
            
            $currentDate->addDay();
        }
        
        $prevMonth = $firstDay->copy()->subMonth();
        $nextMonth = $firstDay->copy()->addMonth();
        
        return view('bookings.calendar', compact('calendar', 'month', 'year', 'prevMonth', 'nextMonth'));
    }

    /**
     * Ensure booking belongs to current hotel
     * Note: Route model binding already handles this for show method,
     * but we keep this for other methods that might need it
     */
    private function authorizeHotel(Booking $booking)
    {
        $hotelId = session('hotel_id');
        if (!$hotelId) {
            abort(403, 'No hotel context set. Please select a hotel.');
        }
        if ($booking->hotel_id != $hotelId) {
            abort(403, 'Unauthorized access to this booking.');
        }
    }
}
