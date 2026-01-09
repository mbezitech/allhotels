<?php

namespace App\Http\Controllers;

use App\Models\HousekeepingRecord;
use App\Models\Room;
use App\Models\HotelArea;
use App\Models\User;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HousekeepingReportController extends Controller
{
    /**
     * Display housekeeping reports dashboard
     */
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Super admin can view all hotels, others only their hotel
        $hotels = $isSuperAdmin ? Hotel::all() : collect([Hotel::find($hotelId)]);
        
        $selectedHotelId = $request->get('hotel_id', $hotelId);
        
        // If super admin and no hotel selected, show summary
        if ($isSuperAdmin && !$selectedHotelId) {
            return $this->summary();
        }

        // Ensure user can only access their hotel unless super admin
        if (!$isSuperAdmin && $selectedHotelId != $hotelId) {
            abort(403, 'Unauthorized access.');
        }

        $dateFrom = $request->get('date_from', now()->subDays(7)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Log report view
        logActivity('report_viewed', null, "Housekeeping Reports dashboard viewed", [
            'report_type' => 'housekeeping_reports_index',
            'hotel_id' => $selectedHotelId,
        ]);

        return view('housekeeping-reports.index', compact('hotels', 'selectedHotelId', 'dateFrom', 'dateTo', 'isSuperAdmin'));
    }

    /**
     * Daily housekeeping summary
     */
    public function dailySummary(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        $selectedHotelId = $request->get('hotel_id', $hotelId);
        $date = $request->get('date', now()->format('Y-m-d'));
        
        // Log report view
        if ($request->has('export')) {
            logActivity('report_exported', null, "Housekeeping Daily Summary Report exported - Date: {$date}", [
                'report_type' => 'housekeeping_daily_summary',
                'format' => $request->get('format', 'pdf'),
                'date' => $date,
            ]);
        } else {
            logActivity('report_viewed', null, "Housekeeping Daily Summary Report viewed - Date: {$date}", [
                'report_type' => 'housekeeping_daily_summary',
                'date' => $date,
            ]);
        }

        // Ensure user can only access their hotel unless super admin
        if (!$isSuperAdmin && $selectedHotelId != $hotelId) {
            abort(403, 'Unauthorized access.');
        }

        $query = HousekeepingRecord::where('hotel_id', $selectedHotelId)
            ->whereDate('created_at', $date)
            ->with('room', 'area', 'assignedTo');

        $records = $query->get();
        
        $stats = [
            'total_rooms_cleaned' => $records->where('room_id', '!=', null)->where('cleaning_status', 'clean')->count() + 
                                    $records->where('room_id', '!=', null)->where('cleaning_status', 'inspected')->count(),
            'total_areas_cleaned' => $records->where('area_id', '!=', null)->where('cleaning_status', 'clean')->count() + 
                                    $records->where('area_id', '!=', null)->where('cleaning_status', 'inspected')->count(),
            'pending_tasks' => $records->where('cleaning_status', 'dirty')->count() + 
                              $records->where('cleaning_status', 'cleaning')->count(),
            'completed_tasks' => $records->whereIn('cleaning_status', ['clean', 'inspected'])->count(),
            'total_duration' => $records->whereNotNull('duration_minutes')->sum('duration_minutes'),
            'issues_found' => $records->where('has_issues', true)->count(),
        ];

        $hotels = $isSuperAdmin ? Hotel::all() : collect([Hotel::find($hotelId)]);

        return view('housekeeping-reports.daily-summary', compact('records', 'stats', 'date', 'hotels', 'selectedHotelId', 'isSuperAdmin'));
    }

    /**
     * Staff performance report
     */
    public function staffPerformance(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        $selectedHotelId = $request->get('hotel_id', $hotelId);
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        // Log report view
        if ($request->has('export')) {
            logActivity('report_exported', null, "Housekeeping Staff Performance Report exported - Range: {$dateFrom} to {$dateTo}", [
                'report_type' => 'housekeeping_staff_performance',
                'format' => $request->get('format', 'pdf'),
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]);
        } else {
            logActivity('report_viewed', null, "Housekeeping Staff Performance Report viewed - Range: {$dateFrom} to {$dateTo}", [
                'report_type' => 'housekeeping_staff_performance',
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]);
        }

        // Ensure user can only access their hotel unless super admin
        if (!$isSuperAdmin && $selectedHotelId != $hotelId) {
            abort(403, 'Unauthorized access.');
        }

        $performance = HousekeepingRecord::where('hotel_id', $selectedHotelId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('assigned_to', DB::raw('COUNT(*) as total_tasks'),
                     DB::raw('SUM(CASE WHEN cleaning_status IN ("clean", "inspected") THEN 1 ELSE 0 END) as completed_tasks'),
                     DB::raw('AVG(duration_minutes) as avg_duration'),
                     DB::raw('SUM(duration_minutes) as total_duration'),
                     DB::raw('SUM(CASE WHEN has_issues = 1 THEN 1 ELSE 0 END) as issues_count'))
            ->groupBy('assigned_to')
            ->with('assignedTo')
            ->get();

        $hotels = $isSuperAdmin ? Hotel::all() : collect([Hotel::find($hotelId)]);

        return view('housekeeping-reports.staff-performance', compact('performance', 'dateFrom', 'dateTo', 'hotels', 'selectedHotelId', 'isSuperAdmin'));
    }

    /**
     * Pending and overdue tasks report
     */
    public function pendingTasks(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        $selectedHotelId = $request->get('hotel_id', $hotelId);
        
        // Log report view
        if ($request->has('export')) {
            logActivity('report_exported', null, "Housekeeping Pending Tasks Report exported", [
                'report_type' => 'housekeeping_pending_tasks',
                'format' => $request->get('format', 'pdf'),
            ]);
        } else {
            logActivity('report_viewed', null, "Housekeeping Pending Tasks Report viewed", [
                'report_type' => 'housekeeping_pending_tasks',
            ]);
        }

        // Ensure user can only access their hotel unless super admin
        if (!$isSuperAdmin && $selectedHotelId != $hotelId) {
            abort(403, 'Unauthorized access.');
        }

        $pending = HousekeepingRecord::where('hotel_id', $selectedHotelId)
            ->whereIn('cleaning_status', ['dirty', 'cleaning'])
            ->with('room', 'area', 'assignedTo')
            ->orderBy('created_at', 'asc')
            ->get();

        $hotels = $isSuperAdmin ? Hotel::all() : collect([Hotel::find($hotelId)]);

        return view('housekeeping-reports.pending-tasks', compact('pending', 'hotels', 'selectedHotelId', 'isSuperAdmin'));
    }

    /**
     * Issues and damages report
     */
    public function issuesReport(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        $selectedHotelId = $request->get('hotel_id', $hotelId);
        $dateFrom = $request->get('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        
        // Log report view
        if ($request->has('export')) {
            logActivity('report_exported', null, "Housekeeping Issues Report exported - Range: {$dateFrom} to {$dateTo}", [
                'report_type' => 'housekeeping_issues',
                'format' => $request->get('format', 'pdf'),
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]);
        } else {
            logActivity('report_viewed', null, "Housekeeping Issues Report viewed - Range: {$dateFrom} to {$dateTo}", [
                'report_type' => 'housekeeping_issues',
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]);
        }

        // Ensure user can only access their hotel unless super admin
        if (!$isSuperAdmin && $selectedHotelId != $hotelId) {
            abort(403, 'Unauthorized access.');
        }

        $query = HousekeepingRecord::where('hotel_id', $selectedHotelId)
            ->where('has_issues', true)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with('room', 'area', 'assignedTo', 'issueResolvedBy');

        // Filter by resolution status
        $resolutionFilter = $request->get('resolution_status', 'all');
        if ($resolutionFilter === 'resolved') {
            $query->where('issue_resolved', true);
        } elseif ($resolutionFilter === 'unresolved') {
            $query->where('issue_resolved', false)->orWhereNull('issue_resolved');
        }
        // 'all' shows both resolved and unresolved

        $issues = $query->orderBy('created_at', 'desc')->get();

        $hotels = $isSuperAdmin ? Hotel::all() : collect([Hotel::find($hotelId)]);

        return view('housekeeping-reports.issues', compact('issues', 'dateFrom', 'dateTo', 'hotels', 'selectedHotelId', 'isSuperAdmin', 'resolutionFilter'));
    }

    /**
     * Summary view for super admin
     */
    private function summary()
    {
        $hotels = Hotel::all();
        $summary = [];

        foreach ($hotels as $hotel) {
            $records = HousekeepingRecord::where('hotel_id', $hotel->id)
                ->whereDate('created_at', today())
                ->get();

            $summary[$hotel->id] = [
                'hotel' => $hotel,
                'total_tasks' => $records->count(),
                'completed' => $records->whereIn('cleaning_status', ['clean', 'inspected'])->count(),
                'pending' => $records->whereIn('cleaning_status', ['dirty', 'cleaning'])->count(),
                'issues' => $records->where('has_issues', true)->count(),
            ];
        }

        return view('housekeeping-reports.summary', compact('summary', 'hotels'));
    }
}
