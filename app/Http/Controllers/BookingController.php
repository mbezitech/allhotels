<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Hotel;
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
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Check if showing deleted bookings
        $showDeleted = $request->has('show_deleted') && $request->show_deleted == '1';
        
        // Super admins can see all bookings, others only their hotel
        $query = Booking::query();
        if ($showDeleted) {
            $query->withTrashed();
        }
        
        if (!$isSuperAdmin) {
            $query->where('hotel_id', $hotelId);
        }
        
        $query->with(['room', 'createdBy.roles', 'hotel']);
        
        // Hotel filter for super admins
        if ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id) {
            $query->where('hotel_id', $request->hotel_id);
        }

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
        if ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id) {
            $rooms = Room::where('hotel_id', $request->hotel_id)
                ->with('roomType')
                ->orderBy('room_number')
                ->get();
        } else {
            $rooms = $hotelId ? Room::where('hotel_id', $hotelId)
                ->with('roomType')
                ->orderBy('room_number')
                ->get() : collect();
        }

        // Get all hotels for super admin filter
        $hotels = $isSuperAdmin ? Hotel::orderBy('name')->get() : collect();

        // Count deleted bookings for the current hotel context
        $deletedCount = 0;
        if ($showDeleted) {
            // When showing deleted, count only trashed
            $deletedQuery = Booking::onlyTrashed();
            if (!$isSuperAdmin) {
                $deletedQuery->where('hotel_id', $hotelId);
            }
            if ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id) {
                $deletedQuery->where('hotel_id', $request->hotel_id);
            }
            $deletedCount = $deletedQuery->count();
        } else {
            // When showing active, count deleted separately
            $deletedQuery = Booking::onlyTrashed();
            if (!$isSuperAdmin) {
                $deletedQuery->where('hotel_id', $hotelId);
            }
            if ($isSuperAdmin && $request->has('hotel_id') && $request->hotel_id) {
                $deletedQuery->where('hotel_id', $request->hotel_id);
            }
            $deletedCount = $deletedQuery->count();
        }

        return view('bookings.index', compact('bookings', 'rooms', 'hotels', 'isSuperAdmin', 'showDeleted', 'deletedCount'));
    }

    /**
     * Show the form for creating a new booking
     */
    public function create()
    {
        // Log access to booking creation form
        logActivity('create_form_accessed', null, "Accessed booking creation form", [
            'user_id' => auth()->id(),
            'hotel_id' => session('hotel_id'),
        ]);
        
        $hotelId = session('hotel_id');
        $rooms = Room::where('hotel_id', $hotelId)
            ->where('status', 'available')
            ->with('roomType')
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
            'country_code' => 'nullable|string|max:10',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'total_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
        
        // Combine country code with phone number if both are provided
        if (!empty($validated['country_code']) && !empty($validated['guest_phone'])) {
            $validated['guest_phone'] = $validated['country_code'] . ' ' . $validated['guest_phone'];
        } elseif (!empty($validated['country_code']) && empty($validated['guest_phone'])) {
            // If only country code is provided, don't save it
            unset($validated['country_code']);
        }
        unset($validated['country_code']); // Remove from validated as it's not a database field

        $hotelId = session('hotel_id');
        $room = Room::findOrFail($validated['room_id']);

        // Ensure room belongs to current hotel (unless super admin)
        if (!auth()->user()->isSuperAdmin() && $room->hotel_id != $hotelId) {
            abort(403, 'Unauthorized access to this room.');
        }

        // Check total guests don't exceed room capacity
        $totalGuests = $validated['adults'] + ($validated['children'] ?? 0);
        if ($totalGuests > $room->capacity) {
            throw ValidationException::withMessages([
                'adults' => "Total guests (adults + children) cannot exceed room capacity of {$room->capacity}.",
                'children' => "Total guests (adults + children) cannot exceed room capacity of {$room->capacity}.",
            ]);
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
        
        // Calculate final amount (total_amount - discount)
        $discount = $validated['discount'] ?? 0;
        $validated['discount'] = $discount;
        $validated['final_amount'] = max(0, $validated['total_amount'] - $discount);

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
        $this->authorizeHotel($booking);
        
        // Log booking viewing
        logActivity('viewed', $booking, "Viewed booking #{$booking->id} - {$booking->guest_name}", [
            'booking_reference' => $booking->booking_reference,
            'guest_name' => $booking->guest_name,
            'status' => $booking->status,
        ]);
        
        $booking->load('room', 'payments', 'posSales.items.extra');
        
        return view('bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified booking
     */
    public function edit(Booking $booking)
    {
        $this->authorizeHotel($booking);
        
        // Log booking edit form access
        logActivity('edit_form_accessed', $booking, "Accessed edit form for booking #{$booking->id} - {$booking->guest_name}", [
            'booking_reference' => $booking->booking_reference,
            'guest_name' => $booking->guest_name,
            'status' => $booking->status,
        ]);
        
        $hotelId = session('hotel_id');
        $rooms = Room::where('hotel_id', $hotelId)
            ->with('roomType')
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
            'country_code' => 'nullable|string|max:10',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'total_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled',
            'cancellation_reason' => 'required_if:status,cancelled|nullable|string|max:500',
            'notes' => 'nullable|string',
        ]);
        
        // Combine country code with phone number if both are provided
        if (!empty($validated['country_code']) && !empty($validated['guest_phone'])) {
            $validated['guest_phone'] = $validated['country_code'] . ' ' . $validated['guest_phone'];
        } elseif (!empty($validated['country_code']) && empty($validated['guest_phone'])) {
            // If only country code is provided, don't save it
            unset($validated['country_code']);
        }
        unset($validated['country_code']); // Remove from validated as it's not a database field

        $room = Room::findOrFail($validated['room_id']);

        // Check total guests don't exceed room capacity
        $totalGuests = $validated['adults'] + ($validated['children'] ?? 0);
        if ($totalGuests > $room->capacity) {
            throw ValidationException::withMessages([
                'adults' => "Total guests (adults + children) cannot exceed room capacity of {$room->capacity}.",
                'children' => "Total guests (adults + children) cannot exceed room capacity of {$room->capacity}.",
            ]);
        }

        // Check room availability (excluding current booking)
        if (!$room->isAvailableForDates($validated['check_in'], $validated['check_out'], $booking->id)) {
            throw ValidationException::withMessages([
                'room_id' => 'Room is not available for the selected dates.',
            ]);
        }

        // Capture old values for logging
        $oldValues = [
            'room_id' => $booking->room_id,
            'guest_name' => $booking->guest_name,
            'guest_email' => $booking->guest_email,
            'guest_phone' => $booking->guest_phone,
            'check_in' => $booking->check_in ? $booking->check_in->format('Y-m-d') : null,
            'check_out' => $booking->check_out ? $booking->check_out->format('Y-m-d') : null,
            'adults' => $booking->adults,
            'children' => $booking->children,
            'total_amount' => $booking->total_amount,
            'discount' => $booking->discount,
            'final_amount' => $booking->final_amount,
            'status' => $booking->status,
            'notes' => $booking->notes,
        ];
        
        $oldStatus = $booking->status;
        
        // If cancelling and no reason provided, set default user cancellation reason
        if ($validated['status'] === 'cancelled' && empty($validated['cancellation_reason'])) {
            $validated['cancellation_reason'] = 'Cancelled by ' . auth()->user()->name . ' (' . now()->format('Y-m-d H:i') . ')';
        }
        
        // If status is not cancelled, clear cancellation reason
        if ($validated['status'] !== 'cancelled') {
            $validated['cancellation_reason'] = null;
        }
        
        // Calculate final amount (total_amount - discount)
        $discount = $validated['discount'] ?? 0;
        $validated['discount'] = $discount;
        $validated['final_amount'] = max(0, $validated['total_amount'] - $discount);
        
        $booking->update($validated);
        
        // Capture new values for logging
        $booking->refresh();
        $newValues = [
            'room_id' => $booking->room_id,
            'guest_name' => $booking->guest_name,
            'guest_email' => $booking->guest_email,
            'guest_phone' => $booking->guest_phone,
            'check_in' => $booking->check_in ? $booking->check_in->format('Y-m-d') : null,
            'check_out' => $booking->check_out ? $booking->check_out->format('Y-m-d') : null,
            'adults' => $booking->adults,
            'children' => $booking->children,
            'total_amount' => $booking->total_amount,
            'discount' => $booking->discount,
            'final_amount' => $booking->final_amount,
            'status' => $booking->status,
            'notes' => $booking->notes,
        ];
        
        // Log status changes with special handling
        if ($oldStatus !== $validated['status']) {
            if ($validated['status'] === 'checked_in') {
                logActivity('checked_in', $booking, "Guest checked in - Booking #{$booking->id} - {$booking->guest_name}", null, ['status' => $oldStatus], ['status' => 'checked_in']);
            } elseif ($validated['status'] === 'checked_out') {
                logActivity('checked_out', $booking, "Guest checked out - Booking #{$booking->id} - {$booking->guest_name}", null, ['status' => $oldStatus], ['status' => 'checked_out']);
                
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
                logActivity('cancelled', $booking, "Booking cancelled: {$booking->guest_name} - Reason: {$reason}", null, ['status' => $oldStatus], ['status' => 'cancelled']);
            } else {
                logActivity('updated', $booking, "Booking status changed from {$oldStatus} to {$validated['status']} - Booking #{$booking->id}", null, ['status' => $oldStatus], ['status' => $validated['status']]);
            }
        }
        
        // Log all other field changes
        $changedFields = [];
        foreach ($oldValues as $key => $oldValue) {
            if ($key !== 'status' && isset($newValues[$key]) && $oldValue != $newValues[$key]) {
                $changedFields[$key] = ['old' => $oldValue, 'new' => $newValues[$key]];
            }
        }
        
        if (!empty($changedFields)) {
            $fieldNames = implode(', ', array_keys($changedFields));
            logActivity('updated', $booking, "Updated booking #{$booking->id} for {$booking->guest_name} - Changed: {$fieldNames}", null, $oldValues, $newValues);
        }

        return redirect()->route('bookings.index')
            ->with('success', 'Booking updated successfully.');
    }

    /**
     * Remove the specified booking (soft delete)
     */
    public function destroy(Booking $booking)
    {
        $this->authorizeHotel($booking);

        // Only allow deletion of pending or cancelled bookings
        if (!in_array($booking->status, ['pending', 'cancelled'])) {
            return redirect()->route('bookings.index')
                ->with('error', 'Cannot delete confirmed or active bookings.');
        }

        // Capture booking details before deletion
        $bookingDetails = [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'guest_name' => $booking->guest_name,
            'guest_email' => $booking->guest_email,
            'guest_phone' => $booking->guest_phone,
            'room_id' => $booking->room_id,
            'room_number' => $booking->room ? $booking->room->room_number : 'N/A',
            'check_in' => $booking->check_in ? $booking->check_in->format('Y-m-d') : null,
            'check_out' => $booking->check_out ? $booking->check_out->format('Y-m-d') : null,
            'status' => $booking->status,
            'total_amount' => $booking->total_amount,
            'final_amount' => $booking->final_amount,
        ];

        // Soft delete the booking
        $booking->delete();

        // Log the deletion
        logActivity('deleted', $booking, "Deleted booking #{$booking->id} for {$booking->guest_name}", $bookingDetails);

        return redirect()->route('bookings.index')
            ->with('success', 'Booking deleted successfully.');
    }

    /**
     * Restore a soft-deleted booking
     */
    public function restore($id)
    {
        $booking = Booking::withTrashed()->findOrFail($id);
        $this->authorizeHotel($booking);

        $booking->restore();

        // Log the restoration
        logActivity('restored', $booking, "Restored booking #{$booking->id} for {$booking->guest_name}", [
            'booking_reference' => $booking->booking_reference,
            'guest_name' => $booking->guest_name,
        ]);

        return redirect()->route('bookings.index')
            ->with('success', 'Booking restored successfully.');
    }

    /**
     * Permanently delete a booking
     */
    public function forceDelete($id)
    {
        $booking = Booking::withTrashed()->findOrFail($id);
        $this->authorizeHotel($booking);

        // Capture booking details before permanent deletion
        $bookingDetails = [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'guest_name' => $booking->guest_name,
            'guest_email' => $booking->guest_email,
            'guest_phone' => $booking->guest_phone,
            'room_id' => $booking->room_id,
            'room_number' => $booking->room ? $booking->room->room_number : 'N/A',
            'check_in' => $booking->check_in ? $booking->check_in->format('Y-m-d') : null,
            'check_out' => $booking->check_out ? $booking->check_out->format('Y-m-d') : null,
            'status' => $booking->status,
            'total_amount' => $booking->total_amount,
            'final_amount' => $booking->final_amount,
        ];

        // Permanently delete the booking
        $booking->forceDelete();

        // Log the permanent deletion
        logActivity('force_deleted', null, "Permanently deleted booking #{$booking->id} for {$booking->guest_name}", $bookingDetails);

        return redirect()->route('bookings.index')
            ->with('success', 'Booking permanently deleted.');
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
        
        // Get bookings for the month (exclude confirmed, show pending, checked_in, and checked_out)
        $bookings = Booking::where('hotel_id', $hotelId)
            ->whereIn('status', ['pending', 'checked_in', 'checked_out'])
            ->where(function ($query) use ($year, $month, $hotelId) {
                $query->whereYear('check_in', $year)
                      ->whereMonth('check_in', $month)
                      ->orWhere(function ($q2) use ($year, $month, $hotelId) {
                          $q2->where('hotel_id', $hotelId)
                             ->whereYear('check_out', $year)
                             ->whereMonth('check_out', $month);
                      });
            })
            ->with(['room.roomType', 'payments', 'posSales'])
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
     * Check in a confirmed booking
     */
    public function checkIn(Booking $booking)
    {
        $this->authorizeHotel($booking);

        if ($booking->status !== 'confirmed') {
            return redirect()->route('bookings.index')
                ->with('error', 'Only confirmed bookings can be checked in.');
        }

        $today = \Carbon\Carbon::today();
        $checkInDate = \Carbon\Carbon::parse($booking->check_in);
        $checkOutDate = \Carbon\Carbon::parse($booking->check_out);

        // Cannot check in before the check-in date
        if ($today->lt($checkInDate)) {
            return redirect()->route('bookings.index')
                ->with('error', "Cannot check in before the check-in date ({$checkInDate->format('M d, Y')}).");
        }

        // Cannot check in after the check-out date
        if ($today->gt($checkOutDate)) {
            return redirect()->route('bookings.index')
                ->with('error', "Cannot check in after the check-out date ({$checkOutDate->format('M d, Y')}).");
        }

        // Cannot check in if payment is not complete
        if (!$booking->isFullyPaid()) {
            $outstandingBalance = $booking->outstanding_balance;
            return redirect()->route('bookings.index')
                ->with('error', "Cannot check in. Payment incomplete. Outstanding balance: $" . number_format($outstandingBalance, 2) . ". Please complete payment first.");
        }

        $oldStatus = $booking->status;
        $booking->status = 'checked_in';
        $booking->save();

        logActivity('checked_in', $booking, "Guest checked in - Booking #{$booking->id} - {$booking->guest_name}", null, 
            ['status' => $oldStatus], 
            ['status' => 'checked_in']
        );

        return redirect()->route('bookings.index')
            ->with('success', 'Guest checked in successfully.');
    }

    /**
     * Check out a checked-in booking
     */
    public function checkOut(Booking $booking)
    {
        $this->authorizeHotel($booking);

        if ($booking->status !== 'checked_in') {
            return redirect()->route('bookings.index')
                ->with('error', 'Only checked-in bookings can be checked out.');
        }

        // Check for outstanding balance (booking + POS charges)
        $outstandingBalance = $booking->outstanding_balance;
        
        if ($outstandingBalance > 0) {
            // Get breakdown of outstanding amounts
            $bookingBalance = max(0, $booking->final_amount - $booking->total_paid);
            $posCharges = $booking->total_pos_charges;
            
            $message = "Cannot check out. Outstanding balance: $" . number_format($outstandingBalance, 2) . ". ";
            
            if ($bookingBalance > 0 && $posCharges > 0) {
                $message .= "Booking balance: $" . number_format($bookingBalance, 2) . ", POS charges: $" . number_format($posCharges, 2) . ". ";
            } elseif ($posCharges > 0) {
                $message .= "Unpaid POS charges: $" . number_format($posCharges, 2) . ". ";
            }
            
            $message .= "Please settle all outstanding amounts before checkout.";
            
            return redirect()->route('bookings.index')
                ->with('error', $message);
        }

        $oldStatus = $booking->status;
        $booking->status = 'checked_out';
        $booking->save();

        logActivity('checked_out', $booking, "Guest checked out - Booking #{$booking->id} - {$booking->guest_name}", null, 
            ['status' => $oldStatus], 
            ['status' => 'checked_out']
        );

        // Auto-update room cleaning status
        $room = $booking->room;
        if ($room) {
            $oldRoomStatus = $room->cleaning_status;
            $room->cleaning_status = 'dirty';
            $room->save();
            
            logSystemActivity('room_cleaning_status_changed', $room, "Room {$room->room_number} automatically marked as DIRTY after checkout", null, 
                ['cleaning_status' => $oldRoomStatus], 
                ['cleaning_status' => 'dirty']
            );
        }

        return redirect()->route('bookings.index')
            ->with('success', 'Guest checked out successfully.');
    }

    /**
     * Ensure booking belongs to current hotel
     * Note: Route model binding already handles this for show method,
     * but we keep this for other methods that might need it
     */
    private function authorizeHotel(Booking $booking)
    {
        // Super admins can access any booking
        if (auth()->user()->isSuperAdmin()) {
            return;
        }
        
        $hotelId = session('hotel_id');
        if (!$hotelId) {
            abort(403, 'No hotel context set. Please select a hotel.');
        }
        if ($booking->hotel_id != $hotelId) {
            abort(403, 'Unauthorized access to this booking.');
        }
    }
}
