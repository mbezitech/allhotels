<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Hotel;
use App\Models\HousekeepingRecord;
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
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        $user = Auth::user();
        
        // Auto-expire stale pending bookings (e.g. pending payment > 10 minutes)
        \App\Models\Booking::expireStalePending(10);

        // Super admin can view dashboard without hotel context
        if ($user->isSuperAdmin() && !$hotelId) {
            $hotel = null;
            $today = Carbon::today();
            
            // System-wide statistics
            $totalHotels = Hotel::count();
            $totalUsers = \App\Models\User::count();
            $totalSuperAdmins = \App\Models\User::where('is_super_admin', true)->count();
            $totalRegularUsers = $totalUsers - $totalSuperAdmins;
            
            // Rooms statistics across all hotels
            $totalRooms = Room::count();
            $occupiedRooms = Booking::where('check_in', '<=', $today)
                ->where('check_out', '>', $today)
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->count();
            $availableRooms = $totalRooms - $occupiedRooms;
            $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;
            
            // Bookings statistics
            $allBookings = Booking::count();
            $pendingBookings = Booking::where('status', 'pending')->count();
            $cancelledBookings = Booking::where('status', 'cancelled')->count();
            $confirmedBookings = Booking::where('status', 'confirmed')->count();
            // Count bookings scheduled to check in today
            $todayCheckIns = Booking::whereDate('check_in', $today)->count();
            
            // Count bookings that were actually checked out today (status = checked_out and updated today)
            // OR bookings scheduled to check out today that haven't been checked out yet
            $todayCheckOuts = Booking::where(function($query) use ($today) {
                $query->where(function($q) use ($today) {
                    // Bookings actually checked out today
                    $q->where('status', 'checked_out')
                      ->whereDate('updated_at', $today);
                })->orWhere(function($q) use ($today) {
                    // Bookings scheduled to check out today but not yet checked out
                    $q->whereDate('check_out', $today)
                      ->whereIn('status', ['confirmed', 'checked_in']);
                });
            })->count();
            
            // Sales statistics
            $todaySales = PosSale::whereDate('sale_date', $today)->sum('final_amount');
            $todaySalesCount = PosSale::whereDate('sale_date', $today)->count();
            $totalSales = PosSale::sum('final_amount');
            $totalSalesCount = PosSale::count();
            
            // Payments statistics
            $todayPayments = Payment::whereDate('paid_at', $today)->sum('amount');
            $totalPayments = Payment::sum('amount');
            
            // Recent bookings across all hotels
            $recentBookings = Booking::with(['room', 'createdBy', 'hotel'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            // Sales chart data (last 7 days)
            $salesChartData = [];
            $salesChartLabels = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $salesChartLabels[] = $date->format('M d');
                $salesChartData[] = PosSale::whereDate('sale_date', $date)->sum('final_amount');
            }
            
            // Bookings chart data (last 7 days)
            $bookingsChartData = [];
            $bookingsChartLabels = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $bookingsChartLabels[] = $date->format('M d');
                $bookingsChartData[] = Booking::whereDate('created_at', $date)->count();
            }
            
            // Hotels with most bookings
            $topHotels = Hotel::withCount('bookings')
                ->orderBy('bookings_count', 'desc')
                ->limit(5)
                ->get();
            
            // Hotels with most rooms
            $hotelsByRooms = Hotel::withCount('rooms')
                ->orderBy('rooms_count', 'desc')
                ->limit(5)
                ->get();
            
            // Unresolved issues across all hotels
            $canViewHousekeeping = true; // Super admin always has access
            $unresolvedIssues = HousekeepingRecord::where('has_issues', true)
                ->where(function($query) {
                    $query->where('issue_resolved', false)
                          ->orWhereNull('issue_resolved');
                })
                ->with(['hotel', 'room', 'area', 'assignedTo'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            $unresolvedIssuesCount = HousekeepingRecord::where('has_issues', true)
                ->where(function($query) {
                    $query->where('issue_resolved', false)
                          ->orWhereNull('issue_resolved');
                })
                ->count();
            
            $bookingFilter = 'all';
            $calendar = [];
            $month = now()->month;
            $year = now()->year;
            $upcomingAvailable = [];
            
            return view('dashboard', compact(
                'hotel',
                'user',
                'totalHotels',
                'totalUsers',
                'totalSuperAdmins',
                'totalRegularUsers',
                'totalRooms',
                'occupiedRooms',
                'availableRooms',
                'occupancyRate',
                'todayCheckIns',
                'todayCheckOuts',
                'todaySales',
                'todaySalesCount',
                'totalSales',
                'totalSalesCount',
                'todayPayments',
                'totalPayments',
                'recentBookings',
                'pendingBookings',
                'cancelledBookings',
                'confirmedBookings',
                'allBookings',
                'bookingFilter',
                'calendar',
                'month',
                'year',
                'upcomingAvailable',
                'salesChartData',
                'salesChartLabels',
                'bookingsChartData',
                'bookingsChartLabels',
                'topHotels',
                'hotelsByRooms',
                'canViewHousekeeping',
                'unresolvedIssues',
                'unresolvedIssuesCount'
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
        
        // Check permissions
        $canViewRooms = $user->isSuperAdmin() || $user->hasPermission('rooms.view', $hotelId);
        $canViewBookings = $user->isSuperAdmin() || $user->hasPermission('bookings.view', $hotelId);
        $canViewSales = $user->isSuperAdmin() || $user->hasPermission('pos.view', $hotelId);
        $canViewPayments = $user->isSuperAdmin() || $user->hasPermission('payments.view', $hotelId);
        $canViewReports = $user->isSuperAdmin() || $user->hasPermission('reports.view', $hotelId);
        $canViewHousekeeping = $user->isSuperAdmin() || $user->hasPermission('housekeeping_records.view', $hotelId);
        
        // Rooms - only fetch if user has permission
        $totalRooms = 0;
        $occupiedRooms = 0;
        $availableRooms = 0;
        $occupancyRate = 0;
        if ($canViewRooms) {
            $totalRooms = Room::where('hotel_id', $hotelId)->count();
            $occupiedRooms = Booking::where('hotel_id', $hotelId)
                ->where('check_in', '<=', $today)
                ->where('check_out', '>', $today)
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->count();
            $availableRooms = $totalRooms - $occupiedRooms;
            $occupancyRate = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;
        }

        // Today's bookings - only fetch if user has permission
        $todayCheckIns = 0;
        $todayCheckOuts = 0;
        if ($canViewBookings) {
            // Count bookings scheduled to check in today
            $todayCheckIns = Booking::where('hotel_id', $hotelId)
                ->whereDate('check_in', $today)
                ->count();
            
            // Count bookings that were actually checked out today (status = checked_out and updated today)
            // OR bookings scheduled to check out today that haven't been checked out yet
            $todayCheckOuts = Booking::where('hotel_id', $hotelId)
                ->where(function($query) use ($today) {
                    $query->where(function($q) use ($today) {
                        // Bookings actually checked out today
                        $q->where('status', 'checked_out')
                          ->whereDate('updated_at', $today);
                    })->orWhere(function($q) use ($today) {
                        // Bookings scheduled to check out today but not yet checked out
                        $q->whereDate('check_out', $today)
                          ->whereIn('status', ['confirmed', 'checked_in']);
                    });
                })
                ->count();
        }

        // Today's sales - only fetch if user has permission
        $todaySales = 0;
        $todaySalesCount = 0;
        if ($canViewSales) {
            $todaySales = PosSale::where('hotel_id', $hotelId)
                ->whereDate('sale_date', $today)
                ->sum('final_amount');
            $todaySalesCount = PosSale::where('hotel_id', $hotelId)
                ->whereDate('sale_date', $today)
                ->count();
        }

        // Today's payments - only fetch if user has permission
        $todayPayments = 0;
        if ($canViewPayments) {
            $todayPayments = Payment::where('hotel_id', $hotelId)
                ->whereDate('paid_at', $today)
                ->sum('amount');
        }

        // Get booking filter from request
        $bookingFilter = $request->get('booking_filter', 'all'); // all, pending, cancelled
        
        // Recent bookings with filter - only fetch if user has permission
        $recentBookings = collect();
        $pendingBookings = 0;
        $cancelledBookings = 0;
        $allBookings = 0;
        $calendar = [];
        $month = now()->month;
        $year = now()->year;
        
        if ($canViewBookings) {
            $recentBookingsQuery = Booking::where('hotel_id', $hotelId)
                ->with('room', 'createdBy');
            
            if ($bookingFilter === 'pending') {
                $recentBookingsQuery->where('status', 'pending');
            } elseif ($bookingFilter === 'cancelled') {
                $recentBookingsQuery->where('status', 'cancelled');
            }
            
            $recentBookings = $recentBookingsQuery
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Pending bookings count
            $pendingBookings = Booking::where('hotel_id', $hotelId)
                ->where('status', 'pending')
                ->count();
            
            // Cancelled bookings count
            $cancelledBookings = Booking::where('hotel_id', $hotelId)
                ->where('status', 'cancelled')
                ->count();
            
            // All bookings count
            $allBookings = Booking::where('hotel_id', $hotelId)->count();

            // Calendar data for current month
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
        }

        // Upcoming available rooms - only fetch if user has permission to view rooms and bookings
        $upcomingAvailable = [];
        if ($canViewRooms && $canViewBookings) {
            $rooms = Room::where('hotel_id', $hotelId)->with('roomType')->get();
            
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
        }
        
        // Unresolved issues - only fetch if user has permission
        $unresolvedIssues = collect();
        $unresolvedIssuesCount = 0;
        if ($canViewHousekeeping) {
            $unresolvedIssues = HousekeepingRecord::where('hotel_id', $hotelId)
                ->where('has_issues', true)
                ->where(function($query) {
                    $query->where('issue_resolved', false)
                          ->orWhereNull('issue_resolved');
                })
                ->with(['room', 'area', 'assignedTo'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            $unresolvedIssuesCount = HousekeepingRecord::where('hotel_id', $hotelId)
                ->where('has_issues', true)
                ->where(function($query) {
                    $query->where('issue_resolved', false)
                          ->orWhereNull('issue_resolved');
                })
                ->count();
        }

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
            'cancelledBookings',
            'allBookings',
            'bookingFilter',
            'calendar',
            'month',
            'year',
            'upcomingAvailable',
            'canViewRooms',
            'canViewBookings',
            'canViewSales',
            'canViewPayments',
            'canViewReports',
            'canViewHousekeeping',
            'unresolvedIssues',
            'unresolvedIssuesCount'
        ));
    }
}
