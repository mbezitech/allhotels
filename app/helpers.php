<?php

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

if (!function_exists('logActivity')) {
    /**
     * Log an activity
     *
     * @param string $action (created, updated, deleted, etc.)
     * @param Model|null $model The model that was affected
     * @param string $description Human-readable description
     * @param array|null $properties Additional properties (old/new values, etc.)
     * @return ActivityLog|null
     */
    function logActivity(string $action, ?Model $model, string $description, ?array $properties = null): ?ActivityLog
    {
        $hotelId = session('hotel_id');
        $userId = Auth::id();

        if (!$hotelId || !$userId) {
            // Skip logging if no hotel context or user (e.g., during migrations)
            return null;
        }

        try {
            return ActivityLog::create([
                'hotel_id' => $hotelId,
                'user_id' => $userId,
                'action' => $action,
                'model_type' => $model ? get_class($model) : null,
                'model_id' => $model ? $model->id : null,
                'description' => $description,
                'properties' => $properties,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Exception $e) {
            // Silently fail if logging fails (e.g., during tests)
            return null;
        }
    }
}

