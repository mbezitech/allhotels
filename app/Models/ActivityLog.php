<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'hotel_id',
        'user_id',
        'action',
        'model_type',
        'model_id',
        'description',
        'properties',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    /**
     * Get the hotel that owns the activity log
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the user who performed the action (nullable for system actions)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the model that was affected (polymorphic)
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the actor name (user name or "SYSTEM")
     */
    public function getActorNameAttribute(): string
    {
        return $this->user ? $this->user->name : 'SYSTEM';
    }

    /**
     * Get the subject type (alias for model_type)
     */
    public function getSubjectTypeAttribute(): ?string
    {
        return $this->model_type;
    }

    /**
     * Get the subject ID (alias for model_id)
     */
    public function getSubjectIdAttribute(): ?int
    {
        return $this->model_id;
    }

    /**
     * Check if this is a system-generated log
     */
    public function isSystemLog(): bool
    {
        return $this->user_id === null;
    }
}
