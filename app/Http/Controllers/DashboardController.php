<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Payment;
use App\Models\PosSale;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard
     */
    public function index()
    {
        $hotelId = session('hotel_id');
        $user = Auth::user();
        
        // Super admin can view dashboard without hotel context
        if ($user->isSuperAdmin() && !$hotelId) {
            $hotel = null;
            $totalRooms = 0;
            $occupiedRooms = 0;
            $availableRooms = 0;
            $occupancyRate = 0;
            $todayCheckIns = 0;
            $todayCheckOuts = 0;
            $todaySales = 0;
            $todaySalesCount = 0;
            $todayPayments = 0;
            $recentBookings = collect();
            $pendingBookings = 0;
            $calendar = [];
            $month = now()->month;
            $year = now()->year;
            $upcomingAvailable = [];
            
            return view('dashboard', compact(
                'hotel',
                'user',
                'totalRooms',
                'occupiedRooms',
                'availableRooms',
                'occupancyRate',
                'todayCheckIns',
                'todayCheckOuts',
                'todaySales',
                'todaySalesCount',
                'todayPayments',
                'recentBookings',
                'pendingBookings',
                'calendar',
                'month',
                'year',
                'upcomingAvailable'
            ));
        }
        
        // Regular users need hotel context
        if (!$hotelId) {
            return redirect()->route('login')
                ->with('error', 'Please select a hotel to continue.');
        }
        
        $hotel = Hotel::findOrFail($hotelId);

        // Today's statistics
        $today = Carbon::today();
        
        // Rooms
        $totalRooms = Room::where('hotel_id', $hotelId)->count();
        $occupiedRooms = Booking::where('hotel_id', $hotelId)
            ->where('check_in', '<=', $today)
            ->where('check_out', '>', $today)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();
        $availableRooms = $totalRooms - $occupiedRooms;
        $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

        // Today's bookings
        $todayCheckIns = Booking::where('hotel_id', $hotelId)
            ->whereDate('check_in', $today)
            ->count();
        $todayCheckOuts = Booking::where('hotel_id', $hotelId)
            ->whereDate('check_out', $today)
            ->count();

        // Today's sales
        $todaySales = PosSale::where('hotel_id', $hotelId)
            ->whereDate('sale_date', $today)
            ->sum('final_amount');
        $todaySalesCount = PosSale::where('hotel_id', $hotelId)
            ->whereDate('sale_date', $today)
            ->count();

        // Today's payments
        $todayPayments = Payment::where('hotel_id', $hotelId)
            ->whereDate('paid_at', $today)
            ->sum('amount');

        // Recent bookings
        $recentBookings = Booking::where('hotel_id', $hotelId)
            ->with('room')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Pending bookings
        $pendingBookings = Booking::where('hotel_id', $hotelId)
            ->where('status', 'pending')
            ->count();

        // Calendar data for current month
        $month = now()->month;
        $year = now()->year;
        
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
        
        // Create calendar data for current month only (simplified for dashboard)
        $firstDay = Carbon::create($year, $month, 1);
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

        // Upcoming available rooms (rooms that will be available in the next 7 days)
        $upcomingAvailable = [];
        $rooms = Room::where('hotel_id', $hotelId)->get();
        
        foreach ($rooms as $room) {
            // Check if room is currently booked
            $currentBooking = Booking::where('hotel_id', $hotelId)
                ->where('room_id', $room->id)
                ->where('check_in', '<=', now())
                ->where('check_out', '>', now())
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->first();
            
            if ($currentBooking) {
                // Room will be available after check-out
                $availableDate = Carbon::parse($currentBooking->check_out);
                if ($availableDate->isFuture() && $availableDate->diffInDays(now()) <= 7) {
                    $upcomingAvailable[] = [
                        'room' => $room,
                        'available_date' => $availableDate,
                        'current_booking' => $currentBooking,
                    ];
                }
            } else {
                // Room is currently available
                $upcomingAvailable[] = [
                    'room' => $room,
                    'available_date' => now(),
                    'current_booking' => null,
                ];
            }
        }
        
        // Sort by available date
        usort($upcomingAvailable, function ($a, $b) {
            return $a['available_date']->timestamp <=> $b['available_date']->timestamp;
        });
        
        // Limit to top 10
        $upcomingAvailable = array_slice($upcomingAvailable, 0, 10);

        return view('dashboard', compact(
            'hotel',
            'user',
            'totalRooms',
            'occupiedRooms',
            'availableRooms',
            'occupancyRate',
            'todayCheckIns',
            'todayCheckOuts',
            'todaySales',
            'todaySalesCount',
            'todayPayments',
            'recentBookings',
            'pendingBookings',
            'calendar',
            'month',
            'year',
            'upcomingAvailable'
        ));
    }
}
