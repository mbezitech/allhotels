<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Extra;
use App\Models\Payment;
use App\Models\PosSale;
use App\Models\Room;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Show reports index/dashboard
     */
    public function index()
    {
        return view('reports.index');
    }

    /**
     * Daily sales report (POS sales by date)
     */
    public function dailySales(Request $request)
    {
        $hotelId = session('hotel_id');
        
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $startDate = $request->get('start_date', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        // Single day report
        $dailySales = PosSale::where('hotel_id', $hotelId)
            ->whereDate('sale_date', $date)
            ->with('items.extra')
            ->get();

        $dailyTotal = $dailySales->sum('final_amount');
        $dailyCount = $dailySales->count();

        // Date range report
        $rangeSales = PosSale::where('hotel_id', $hotelId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->get();

        $rangeTotal = $rangeSales->sum('final_amount');
        $rangeCount = $rangeSales->count();
        $averageDaily = $rangeCount > 0 ? $rangeTotal / Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1 : 0;

        // Sales by date (for chart)
        $salesByDate = PosSale::where('hotel_id', $hotelId)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->selectRaw('sale_date, COUNT(*) as count, SUM(final_amount) as total')
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get();

        return view('reports.daily-sales', compact(
            'date',
            'startDate',
            'endDate',
            'dailySales',
            'dailyTotal',
            'dailyCount',
            'rangeTotal',
            'rangeCount',
            'averageDaily',
            'salesByDate'
        ));
    }

    /**
     * Occupancy report
     */
    public function occupancy(Request $request)
    {
        $hotelId = session('hotel_id');
        
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $startDate = $request->get('start_date', Carbon::today()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::today()->format('Y-m-d'));

        // Total rooms
        $totalRooms = Room::where('hotel_id', $hotelId)->count();

        // Single day occupancy
        $occupiedOnDate = Booking::where('hotel_id', $hotelId)
            ->where('check_in', '<=', $date)
            ->where('check_out', '>', $date)
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->count();

        $occupancyRate = $totalRooms > 0 ? ($occupiedOnDate / $totalRooms) * 100 : 0;

        // Date range occupancy
        $occupancyByDate = [];
        $currentDate = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        while ($currentDate <= $end) {
            $occupied = Booking::where('hotel_id', $hotelId)
                ->where('check_in', '<=', $currentDate->format('Y-m-d'))
                ->where('check_out', '>', $currentDate->format('Y-m-d'))
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->count();

            $rate = $totalRooms > 0 ? ($occupied / $totalRooms) * 100 : 0;

            $occupancyByDate[] = [
                'date' => $currentDate->format('Y-m-d'),
                'occupied' => $occupied,
                'available' => $totalRooms - $occupied,
                'rate' => round($rate, 2),
            ];

            $currentDate->addDay();
        }

        // Average occupancy for period
        $avgOccupied = collect($occupancyByDate)->avg('occupied');
        $avgRate = $totalRooms > 0 ? ($avgOccupied / $totalRooms) * 100 : 0;

        return view('reports.occupancy', compact(
            'date',
            'startDate',
            'endDate',
            'totalRooms',
            'occupiedOnDate',
            'occupancyRate',
            'occupancyByDate',
            'avgOccupied',
            'avgRate'
        ));
    }

    /**
     * Stock reports
     */
    public function stock(Request $request)
    {
        $hotelId = session('hotel_id');
        
        // Low stock items
        $lowStockItems = Extra::where('hotel_id', $hotelId)
            ->where('stock_tracked', true)
            ->get()
            ->filter(function ($extra) use ($hotelId) {
                return $extra->isLowStock($hotelId);
            })
            ->map(function ($extra) use ($hotelId) {
                $extra->current_stock = $extra->getStockBalance($hotelId);
                return $extra;
            })
            ->sortBy('current_stock');

        // Fast-moving items (items with most 'out' movements in last 30 days)
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        
        $fastMoving = StockMovement::where('hotel_id', $hotelId)
            ->where('type', 'out')
            ->where('created_at', '>=', $thirtyDaysAgo)
            ->selectRaw('product_id, SUM(quantity) as total_out')
            ->groupBy('product_id')
            ->orderByDesc('total_out')
            ->limit(10)
            ->get()
            ->map(function ($movement) use ($hotelId) {
                $extra = Extra::find($movement->product_id);
                if ($extra) {
                    $extra->total_out = $movement->total_out;
                    $extra->current_stock = $extra->getStockBalance($hotelId);
                    return $extra;
                }
                return null;
            })
            ->filter();

        // Slow-moving items (items with no 'out' movements in last 90 days)
        $ninetyDaysAgo = Carbon::now()->subDays(90);
        
        $slowMoving = Extra::where('hotel_id', $hotelId)
            ->where('stock_tracked', true)
            ->where('is_active', true)
            ->get()
            ->filter(function ($extra) use ($hotelId, $ninetyDaysAgo) {
                $hasRecentOut = StockMovement::where('hotel_id', $hotelId)
                    ->where('product_id', $extra->id)
                    ->where('type', 'out')
                    ->where('created_at', '>=', $ninetyDaysAgo)
                    ->exists();
                
                return !$hasRecentOut;
            })
            ->map(function ($extra) use ($hotelId) {
                $extra->current_stock = $extra->getStockBalance($hotelId);
                return $extra;
            });

        return view('reports.stock', compact('lowStockItems', 'fastMoving', 'slowMoving'));
    }
}
