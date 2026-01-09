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
            ->with(['room', 'createdBy.roles']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('guest_name', 'like', "%{$search}%")
                  ->orWhere('guest_email', 'like', "%{$search}%")
                  ->orWhere('guest_phone', 'like', "%{$search}%")
                  ->orWhere('booking_reference', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by room
        if ($request->has('room_id') && $request->room_id) {
            $query->where('room_id', $request->room_id);
        }

        // Filter by source
        if ($request->has('source') && $request->source) {
            $query->where('source', $request->source);
        }

        // Filter by check-in date range
        if ($request->has('check_in_from') && $request->check_in_from) {
            $query->where('check_in', '>=', $request->check_in_from);
        }
        if ($request->has('check_in_to') && $request->check_in_to) {
            $query->where('check_in', '<=', $request->check_in_to);
        }

        // Filter by check-out date range
        if ($request->has('check_out_from') && $request->check_out_from) {
            $query->where('check_out', '>=', $request->check_out_from);
        }
        if ($request->has('check_out_to') && $request->check_out_to) {
            $query->where('check_out', '<=', $request->check_out_to);
        }

        // Filter by booking date range (created_at)
        if ($request->has('booking_date_from') && $request->booking_date_from) {
            $query->whereDate('created_at', '>=', $request->booking_date_from);
        }
        if ($request->has('booking_date_to') && $request->booking_date_to) {
            $query->whereDate('created_at', '<=', $request->booking_date_to);
        }

        // Sort order
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $bookings = $query->paginate(20)->withQueryString();

        // Get rooms for filter dropdown
        $rooms = Room::where('hotel_id', $hotelId)
            ->with('roomType')
            ->orderBy('room_number')
            ->get();

        return view('bookings.index', compact('bookings', 'rooms'));
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
        $validated['source'] = 'dashboard';
        $validated['created_by'] = auth()->id();

        $booking = Booking::create($validated);
        logActivity('created', $booking, "Created booking for {$booking->guest_name} - Room {$booking->room->room_number}", [
            'guest_name' => $booking->guest_name,
            'guest_email' => $booking->guest_email,
            'check_in' => $booking->check_in->format('Y-m-d'),
            'check_out' => $booking->check_out->format('Y-m-d'),
            'total_amount' => $booking->total_amount,
        ]);

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
            'cancellation_reason' => 'required_if:status,cancelled|nullable|string|max:500',
            'notes' => 'nullable|string',
        ]);

        $room = Room::findOrFail($validated['room_id']);

        // Check room availability (excluding current booking)
        if (!$room->isAvailableForDates($validated['check_in'], $validated['check_out'], $booking->id)) {
            throw ValidationException::withMessages([
                'room_id' => 'Room is not available for the selected dates.',
            ]);
        }

        $oldStatus = $booking->status;
        $oldValues = ['status' => $oldStatus];
        $newValues = ['status' => $validated['status']];
        
        // If cancelling and no reason provided, set default user cancellation reason
        if ($validated['status'] === 'cancelled' && empty($validated['cancellation_reason'])) {
            $validated['cancellation_reason'] = 'Cancelled by ' . auth()->user()->name . ' (' . now()->format('Y-m-d H:i') . ')';
        }
        
        // If status is not cancelled, clear cancellation reason
        if ($validated['status'] !== 'cancelled') {
            $validated['cancellation_reason'] = null;
        }
        
        $booking->update($validated);
        
        // Log status changes
        if ($oldStatus !== $validated['status']) {
            if ($validated['status'] === 'checked_in') {
                logActivity('checked_in', $booking, "Guest checked in - Booking #{$booking->id} - {$booking->guest_name}", null, $oldValues, $newValues);
            } elseif ($validated['status'] === 'checked_out') {
                logActivity('checked_out', $booking, "Guest checked out - Booking #{$booking->id} - {$booking->guest_name}", null, $oldValues, $newValues);
                
                // Auto-update room cleaning status if booking is checked out
                $room = $booking->room;
                if ($room) {
                    $oldRoomStatus = $room->cleaning_status;
                    $room->cleaning_status = 'dirty';
                    $room->save();
                    logSystemActivity('room_status_changed', $room, "Room {$room->room_number} automatically marked as DIRTY after checkout", null, 
                        ['cleaning_status' => $oldRoomStatus], 
                        ['cleaning_status' => 'dirty']
                    );
                }
            } elseif ($validated['status'] === 'cancelled') {
                $reason = $validated['cancellation_reason'] ?? 'No reason provided';
                logActivity('cancelled', $booking, "Booking cancelled: {$booking->guest_name} - Reason: {$reason}", null, $oldValues, $newValues);
            } else {
                logActivity('updated', $booking, "Booking status changed from {$oldStatus} to {$validated['status']} - Booking #{$booking->id}", null, $oldValues, $newValues);
            }
        } else {
            logActivity('updated', $booking, "Updated booking #{$booking->id} for {$booking->guest_name}");
        }

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
        
        // Get bookings for the month (only active / occupying statuses)
        $bookings = Booking::where('hotel_id', $hotelId)
            ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
            ->where(function ($query) use ($year, $month, $hotelId) {
                $query->whereYear('check_in', $year)
                      ->whereMonth('check_in', $month)
                      ->orWhere(function ($q2) use ($year, $month, $hotelId) {
                          $q2->where('hotel_id', $hotelId)
                             ->whereYear('check_out', $year)
                             ->whereMonth('check_out', $month);
                      });
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
