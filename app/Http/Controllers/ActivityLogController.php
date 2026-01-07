<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs for current hotel
     */
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        
        $query = ActivityLog::where('hotel_id', $hotelId)
            ->with('user');

        // Filter by action
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        // Filter by model type
        if ($request->has('model_type') && $request->model_type) {
            $query->where('model_type', $request->model_type);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('activity-logs.index', compact('logs'));
    }

    /**
     * Display the specified activity log
     */
    public function show(ActivityLog $activityLog)
    {
        if ($activityLog->hotel_id != session('hotel_id')) {
            abort(403, 'Unauthorized access to this activity log.');
        }
        
        $activityLog->load('user', 'hotel');
        
        return view('activity-logs.show', compact('activityLog'));
    }
}
