<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of activity logs
     */
    public function index(Request $request)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Super admin can view all hotels
        $hotels = $isSuperAdmin ? Hotel::all() : collect([Hotel::find($hotelId)]);
        
        $selectedHotelId = $request->get('hotel_id', $hotelId);
        
        // If super admin and no hotel selected, show all (including logs with null hotel_id for deleted hotels)
        if ($isSuperAdmin && !$selectedHotelId) {
            $query = ActivityLog::query(); // Show all including deleted hotels (null hotel_id)
        } else {
            // Ensure user can only access their hotel unless super admin
            if (!$isSuperAdmin && $selectedHotelId != $hotelId) {
                abort(403, 'Unauthorized access.');
            }
            // Show logs for the selected hotel
            if ($isSuperAdmin && $selectedHotelId === 'all') {
                $query = ActivityLog::query(); // Show all including deleted hotels
            } elseif ($isSuperAdmin && $selectedHotelId === 'deleted') {
                // Special filter for deleted hotels (null hotel_id)
                $query = ActivityLog::whereNull('hotel_id');
            } else {
                $query = ActivityLog::where('hotel_id', $selectedHotelId);
            }
        }

        $query->with('user', 'hotel');

        // Filter by action
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        // Filter by model type (subject type)
        if ($request->has('model_type') && $request->model_type) {
            if ($request->model_type === 'Room') {
                $query->where('model_type', 'App\Models\Room');
            } elseif ($request->model_type === 'Booking') {
                $query->where('model_type', 'App\Models\Booking');
            } elseif ($request->model_type === 'HousekeepingRecord') {
                $query->where('model_type', 'App\Models\HousekeepingRecord');
            } elseif ($request->model_type === 'Task') {
                $query->where('model_type', 'App\Models\Task');
            } elseif ($request->model_type === 'HotelArea') {
                $query->where('model_type', 'App\Models\HotelArea');
            } elseif ($request->model_type === 'Hotel') {
                $query->where('model_type', 'App\Models\Hotel');
            } else {
                $query->where('model_type', $request->model_type);
            }
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            if ($request->user_id === 'system') {
                $query->whereNull('user_id');
            } elseif ($request->user_id === 'super_admin') {
                // Filter for super admin logins/logouts
                $superAdminIds = User::where('is_super_admin', true)->pluck('id');
                $query->whereIn('user_id', $superAdminIds)
                      ->whereIn('action', ['user_login', 'user_logout']);
            } else {
                $query->where('user_id', $request->user_id);
            }
        }
        
        // Filter for super admin actions only
        if ($request->has('super_admin_only') && $request->super_admin_only) {
            $superAdminIds = User::where('is_super_admin', true)->pluck('id');
            $query->whereIn('user_id', $superAdminIds);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Get available actions and model types for filters
        $availableActions = ActivityLog::distinct()->pluck('action')->sort()->values();
        $availableModelTypes = ActivityLog::distinct()->pluck('model_type')->filter()->sort()->values();
        $users = User::all();

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('activity-logs.index', compact(
            'logs',
            'hotels',
            'selectedHotelId',
            'isSuperAdmin',
            'availableActions',
            'availableModelTypes',
            'users'
        ));
    }

    /**
     * Display the specified activity log
     */
    public function show(ActivityLog $activityLog)
    {
        $hotelId = session('hotel_id');
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Super admin can view any log (including deleted hotel logs with null hotel_id)
        // Others can only view logs for their hotel
        if (!$isSuperAdmin) {
            // If log has null hotel_id (deleted hotel), only super admin can view
            if (is_null($activityLog->hotel_id)) {
                abort(403, 'Unauthorized access to this activity log.');
            }
            // Regular users can only view logs for their hotel
            if ($activityLog->hotel_id != $hotelId) {
                abort(403, 'Unauthorized access to this activity log.');
            }
        }
        
        $activityLog->load('user', 'hotel');
        
        return view('activity-logs.show', compact('activityLog'));
    }
}
