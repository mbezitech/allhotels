<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PublicBookingController extends Controller
{
    /**
     * Show public booking page for a room
     * URL: /book/{hotel_slug}/{room_id}
     */
    public function show(string $hotelSlug, int $roomId, Request $request)
    {
        $hotel = Hotel::where('slug', $hotelSlug)->firstOrFail();
        $room = Room::where('id', $roomId)
            ->where('hotel_id', $hotel->id)
            ->where('status', 'available')
            ->with('roomType')
            ->firstOrFail();

        // Get check-in and check-out dates from query parameters (if coming from search)
        $checkIn = $request->get('check_in');
        $checkOut = $request->get('check_out');

        return view('public.booking', compact('hotel', 'room', 'checkIn', 'checkOut'));
    }

    /**
     * Store public booking (guest booking)
     */
    public function store(Request $request, string $hotelSlug, int $roomId)
    {
        $hotel = Hotel::where('slug', $hotelSlug)->firstOrFail();
        $room = Room::where('id', $roomId)
            ->where('hotel_id', $hotel->id)
            ->where('status', 'available')
            ->firstOrFail();

        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'required|string|max:255',
            'country_code' => 'nullable|string|max:10',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
        ]);
        
        // Combine country code with phone number if both are provided
        if (!empty($validated['country_code']) && !empty($validated['guest_phone'])) {
            $validated['guest_phone'] = $validated['country_code'] . ' ' . $validated['guest_phone'];
        } elseif (!empty($validated['country_code']) && empty($validated['guest_phone'])) {
            // If only country code is provided, don't save it
            unset($validated['country_code']);
        }
        unset($validated['country_code']); // Remove from validated as it's not a database field

        // Check total guests don't exceed room capacity
        $totalGuests = $validated['adults'] + ($validated['children'] ?? 0);
        if ($totalGuests > $room->capacity) {
            return back()
                ->withInput()
                ->withErrors([
                    'adults' => "Total guests (adults + children) cannot exceed room capacity of {$room->capacity}.",
                    'children' => "Total guests (adults + children) cannot exceed room capacity of {$room->capacity}.",
                ]);
        }

        // Check room availability
        if (!$room->isAvailableForDates($validated['check_in'], $validated['check_out'])) {
            return back()
                ->withInput()
                ->withErrors(['check_in' => 'Room is not available for the selected dates. Please choose different dates.']);
        }

        // Calculate total amount based on number of nights
        $checkIn = Carbon::parse($validated['check_in']);
        $checkOut = Carbon::parse($validated['check_out']);
        $nights = $checkIn->diffInDays($checkOut);
        $totalAmount = $room->price_per_night * $nights;

        // Create booking
        $booking = Booking::create([
            'hotel_id' => $hotel->id,
            'room_id' => $room->id,
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'],
            'guest_phone' => $validated['guest_phone'],
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'adults' => $validated['adults'],
            'children' => $validated['children'] ?? 0,
            'total_amount' => $totalAmount,
            'status' => 'pending', // Guest bookings start as pending
            'source' => 'public',
            'created_by' => null,
            'notes' => 'Booked via public website.',
        ]);

        // Always log public bookings (system log since no user is logged in)
        logSystemActivity('created', $booking, "Guest booking created via public website: {$booking->guest_name} - Room {$room->room_number}", [
            'booking_reference' => $booking->booking_reference,
            'is_guest_booking' => true,
            'check_in' => $booking->check_in->format('Y-m-d'),
            'check_out' => $booking->check_out->format('Y-m-d'),
        ]);

        return redirect()->route('public.booking.confirmation', [
            'hotel_slug' => $hotelSlug,
            'booking_reference' => $booking->booking_reference
        ]);
    }

    /**
     * Show booking confirmation page
     */
    public function confirmation(string $hotelSlug, string $bookingReference)
    {
        $hotel = Hotel::where('slug', $hotelSlug)->firstOrFail();
        $booking = Booking::where('booking_reference', $bookingReference)
            ->where('hotel_id', $hotel->id)
            ->with('room', 'room.roomType')
            ->firstOrFail();

        return view('public.confirmation', compact('hotel', 'booking'));
    }

    /**
     * Show public room search page
     * URL: /search/{hotel_slug}
     */
    public function search(string $hotelSlug, Request $request)
    {
        $hotel = Hotel::where('slug', $hotelSlug)->firstOrFail();
        
        $checkIn = $request->get('check_in');
        $checkOut = $request->get('check_out');
        $availableRooms = collect();
        
        if ($checkIn && $checkOut) {
            $request->validate([
                'check_in' => 'required|date|after_or_equal:today',
                'check_out' => 'required|date|after:check_in',
            ]);
            
            // Get all available rooms for the selected dates
            $rooms = Room::where('hotel_id', $hotel->id)
                ->where('status', 'available')
                ->with('roomType')
                ->get();
            
            $availableRooms = $rooms->filter(function ($room) use ($checkIn, $checkOut) {
                return $room->isAvailableForDates($checkIn, $checkOut);
            });
        }
        
        return view('public.search', compact('hotel', 'availableRooms', 'checkIn', 'checkOut'));
    }

    /**
     * API Get room types (for WordPress plugin)
     */
    public function apiGetRoomTypes(Request $request, string $hotelSlug)
    {
        try {
            $hotel = Hotel::where('slug', $hotelSlug)->firstOrFail();
            
            $roomTypes = \App\Models\RoomType::where('hotel_id', $hotel->id)->get();
            
            // Attach images from the first room of each type
            $roomTypes->transform(function ($type) {
                $firstRoom = \App\Models\Room::where('room_type_id', $type->id)
                    ->whereNotNull('images')
                    ->where('images', '!=', '[]')
                    ->first();
                $type->images = $firstRoom ? $firstRoom->images : [];
                return $type;
            });
            
            return response()->json([
                'status' => 'success',
                'data' => $roomTypes
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hotel not found or an error occurred.',
            ], 404);
        }
    }

    /**
     * API Get available rooms (for WordPress plugin)
     */
    public function apiGetRooms(Request $request, string $hotelSlug)
    {
        try {
            $hotel = Hotel::where('slug', $hotelSlug)->firstOrFail();
            
            $checkIn = $request->get('check_in');
            $checkOut = $request->get('check_out');
            
            $roomsQuery = Room::where('hotel_id', $hotel->id)
                ->where('status', 'available')
                ->with('roomType');
                
            $rooms = $roomsQuery->get();
            
            if ($checkIn && $checkOut) {
                // Validate dates if both provided
                $request->merge(['check_in' => $checkIn, 'check_out' => $checkOut]);
                try {
                    $request->validate([
                        'check_in' => 'required|date|after_or_equal:today',
                        'check_out' => 'required|date|after:check_in',
                    ]);
                    
                    $rooms = $rooms->filter(function ($room) use ($checkIn, $checkOut) {
                        return $room->isAvailableForDates($checkIn, $checkOut);
                    });
                } catch (ValidationException $e) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid dates provided.',
                        'errors' => $e->errors()
                    ], 422);
                }
            }
            
            // Re-index array after filtering
            return response()->json([
                'status' => 'success',
                'data' => array_values($rooms->toArray())
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hotel not found or an error occurred.',
            ], 404);
        }
    }

    /**
     * API Store booking (for WordPress plugin)
     */
    public function apiStore(Request $request, string $hotelSlug, int $roomId)
    {
        try {
            $hotel = Hotel::where('slug', $hotelSlug)->firstOrFail();
            $room = Room::where('id', $roomId)
                ->where('hotel_id', $hotel->id)
                ->where('status', 'available')
                ->firstOrFail();

            $validated = $request->validate([
                'guest_name' => 'required|string|max:255',
                'guest_email' => 'required|email|max:255',
                'guest_phone' => 'required|string|max:255',
                'check_in' => 'required|date|after_or_equal:today',
                'check_out' => 'required|date|after:check_in',
                'adults' => 'required|integer|min:1',
                'children' => 'nullable|integer|min:0',
            ]);

            // Check total guests don't exceed room capacity
            $totalGuests = $validated['adults'] + ($validated['children'] ?? 0);
            if ($totalGuests > $room->capacity) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Total guests cannot exceed room capacity of {$room->capacity}."
                ], 422);
            }

            // Check room availability
            if (!$room->isAvailableForDates($validated['check_in'], $validated['check_out'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Room is not available for the selected dates.'
                ], 422);
            }

            // Calculate total amount based on number of nights
            $checkIn = Carbon::parse($validated['check_in']);
            $checkOut = Carbon::parse($validated['check_out']);
            $nights = $checkIn->diffInDays($checkOut);
            $totalAmount = $room->price_per_night * $nights;

            // Create booking
            $booking = Booking::create([
                'hotel_id' => $hotel->id,
                'room_id' => $room->id,
                'guest_name' => $validated['guest_name'],
                'guest_email' => $validated['guest_email'],
                'guest_phone' => $validated['guest_phone'],
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
                'adults' => $validated['adults'],
                'children' => $validated['children'] ?? 0,
                'total_amount' => $totalAmount,
                'status' => 'pending', // Guest bookings start as pending
                'source' => 'api',
                'created_by' => null,
                'notes' => 'Booked via external API (WordPress plugin).',
            ]);

            // Always log api bookings
            logSystemActivity('created', $booking, "Guest booking created via API: {$booking->guest_name} - Room {$room->room_number}", [
                'booking_reference' => $booking->booking_reference,
                'is_guest_booking' => true,
                'check_in' => $booking->check_in->format('Y-m-d'),
                'check_out' => $booking->check_out->format('Y-m-d'),
                'source' => 'api'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Booking created successfully.',
                'booking_reference' => $booking->booking_reference,
                'total_amount' => $totalAmount
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hotel or room not found, or an error occurred.',
            ], 404);
        }
    }
}
