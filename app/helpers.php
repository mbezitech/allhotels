<?php

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

if (!function_exists('logActivity')) {
    /**
     * Log an activity
     *
     * @param string $action (created, updated, deleted, checkout, checkin, etc.)
     * @param Model|null $model The model that was affected
     * @param string $description Human-readable description
     * @param array|null $properties Additional properties
     * @param array|null $oldValues Old values before change
     * @param array|null $newValues New values after change
     * @param bool $isSystemLog Whether this is a system-generated log (user_id will be null)
     * @return ActivityLog|null
     */
    function logActivity(
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

        // Special handling for Hotel model - use the hotel's own ID
        // This is needed because hotels don't have a hotel_id field (they ARE the hotel)
        if ($model instanceof \App\Models\Hotel) {
            $hotelId = $model->id;
        }

        // For system logs or when no session hotel_id, try to get from model
        if (!$hotelId && $model && method_exists($model, 'getAttribute')) {
            $hotelId = $model->getAttribute('hotel_id') ?? $model->hotel_id ?? null;
        }

        // If still no hotel_id, try to get from properties if provided
        if (!$hotelId && $properties && isset($properties['hotel_id'])) {
            $hotelId = $properties['hotel_id'];
        }

        if (!$hotelId) {
            // Skip logging if no hotel context (e.g., during migrations or public actions)
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
            // Silently fail if logging fails (e.g., during tests or if table doesn't exist)
            \Log::warning('Failed to log activity: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('logSystemActivity')) {
    /**
     * Log a system-generated activity (no user)
     *
     * @param string $action
     * @param Model|null $model
     * @param string $description
     * @param array|null $properties
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return ActivityLog|null
     */
    function logSystemActivity(
        string $action,
        ?Model $model,
        string $description,
        ?array $properties = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): ?ActivityLog {
        return logActivity($action, $model, $description, $properties, $oldValues, $newValues, true);
    }
}
