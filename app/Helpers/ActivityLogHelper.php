<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogHelper
{
    /**
     * Log an activity
     *
     * @param string $action (created, updated, deleted, etc.)
     * @param Model|null $model The model that was affected
     * @param string $description Human-readable description
     * @param array|null $properties Additional properties (old/new values, etc.)
     * @return ActivityLog
     */
    public static function log(string $action, ?Model $model, string $description, ?array $properties = null): ActivityLog
    {
        $hotelId = session('hotel_id');
        $userId = Auth::id();

        if (!$hotelId || !$userId) {
            // Skip logging if no hotel context or user
            throw new \Exception('Cannot log activity: missing hotel context or user');
        }

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
    }

    /**
     * Log a created action
     */
    public static function created(Model $model, string $description, ?array $properties = null): ActivityLog
    {
        return self::log('created', $model, $description, $properties);
    }

    /**
     * Log an updated action
     */
    public static function updated(Model $model, string $description, ?array $properties = null): ActivityLog
    {
        return self::log('updated', $model, $description, $properties);
    }

    /**
     * Log a deleted action
     */
    public static function deleted(Model $model, string $description, ?array $properties = null): ActivityLog
    {
        return self::log('deleted', $model, $description, $properties);
    }
}

