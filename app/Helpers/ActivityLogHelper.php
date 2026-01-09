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
     * @param string $action (created, updated, deleted, checkout, checkin, etc.)
     * @param Model|null $model The model that was affected
     * @param string $description Human-readable description
     * @param array|null $properties Additional properties
     * @param array|null $oldValues Old values before change
     * @param array|null $newValues New values after change
     * @param bool $isSystemLog Whether this is a system-generated log
     * @return ActivityLog|null
     */
    public static function log(
        string $action,
        ?Model $model,
        string $description,
        ?array $properties = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        bool $isSystemLog = false
    ): ?ActivityLog {
        $hotelId = session('hotel_id');
        $userId = $isSystemLog ? null : Auth::id();

        // For system logs, get hotel_id from model if available
        if (!$hotelId && $model && method_exists($model, 'getAttribute')) {
            $hotelId = $model->getAttribute('hotel_id') ?? $model->hotel_id ?? null;
        }

        if (!$hotelId) {
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
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to log activity: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log a system-generated activity
     */
    public static function systemLog(
        string $action,
        ?Model $model,
        string $description,
        ?array $properties = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): ?ActivityLog {
        return self::log($action, $model, $description, $properties, $oldValues, $newValues, true);
    }

    /**
     * Log a created action
     */
    public static function created(Model $model, string $description, ?array $properties = null): ?ActivityLog
    {
        return self::log('created', $model, $description, $properties);
    }

    /**
     * Log an updated action with old/new values
     */
    public static function updated(
        Model $model,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $properties = null
    ): ?ActivityLog {
        return self::log('updated', $model, $description, $properties, $oldValues, $newValues);
    }

    /**
     * Log a deleted action
     */
    public static function deleted(Model $model, string $description, ?array $properties = null): ?ActivityLog
    {
        return self::log('deleted', $model, $description, $properties);
    }
}
