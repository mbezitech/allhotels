<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class HousekeepingRecord extends Model
{
    protected $fillable = [
        'hotel_id',
        'room_id',
        'area_id',
        'assigned_to',
        'cleaning_status',
        'started_at',
        'completed_at',
        'duration_minutes',
        'notes',
        'issues_found',
        'has_issues',
        'issue_resolved',
        'issue_resolved_at',
        'issue_resolved_by',
        'issue_resolution_notes',
        'inspected_by',
        'inspected_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'inspected_at' => 'datetime',
        'issue_resolved_at' => 'datetime',
        'has_issues' => 'boolean',
        'issue_resolved' => 'boolean',
    ];

    /**
     * Get the hotel that owns the record
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    /**
     * Get the room for this record (if applicable)
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the area for this record (if applicable)
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(HotelArea::class, 'area_id');
    }

    /**
     * Get the user assigned to this cleaning task
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who inspected this cleaning
     */
    public function inspectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    /**
     * Get the user who resolved the issue
     */
    public function issueResolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issue_resolved_by');
    }

    /**
     * Calculate duration in minutes
     */
    public function calculateDuration(): ?int
    {
        if ($this->started_at && $this->completed_at) {
            return $this->started_at->diffInMinutes($this->completed_at);
        }
        return null;
    }

    /**
     * Update duration when completed
     */
    public function updateDuration(): void
    {
        if ($this->completed_at && $this->started_at) {
            $this->duration_minutes = $this->calculateDuration();
            $this->save();
        }
    }

    /**
     * Boot method to log housekeeping activities
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($record) {
            $location = $record->room ? "Room {$record->room->room_number}" : ($record->area ? $record->area->name : 'Unknown');
            $assignedToName = $record->assignedTo ? $record->assignedTo->name : 'Unassigned';
            logActivity(
                'housekeeping_task_created',
                $record,
                "Housekeeping task created for {$location} - Assigned to {$assignedToName}",
                ['cleaning_status' => $record->cleaning_status]
            );
        });

        static::updating(function ($record) {
            // Log cleaning status changes
            if ($record->isDirty('cleaning_status')) {
                $oldStatus = $record->getOriginal('cleaning_status');
                $newStatus = $record->cleaning_status;
                $location = $record->room ? "Room {$record->room->room_number}" : ($record->area ? $record->area->name : 'Unknown');
                
                if ($newStatus === 'cleaning' && $oldStatus === 'dirty') {
                    logActivity(
                        'cleaning_started',
                        $record,
                        "Cleaning started for {$location}",
                        null,
                        ['cleaning_status' => $oldStatus],
                        ['cleaning_status' => $newStatus, 'started_at' => $record->started_at]
                    );
                } elseif ($newStatus === 'clean' && in_array($oldStatus, ['dirty', 'cleaning'])) {
                    logActivity(
                        'cleaning_completed',
                        $record,
                        "Cleaning completed for {$location}",
                        ['duration_minutes' => $record->duration_minutes],
                        ['cleaning_status' => $oldStatus],
                        ['cleaning_status' => $newStatus, 'completed_at' => $record->completed_at]
                    );
                } elseif ($newStatus === 'inspected' && $oldStatus === 'clean') {
                    $inspectedByName = $record->inspectedBy ? $record->inspectedBy->name : 'Unknown';
                    logActivity(
                        'room_inspected',
                        $record,
                        "Room inspected and marked READY: {$location}",
                        ['inspected_by' => $inspectedByName],
                        ['cleaning_status' => $oldStatus],
                        ['cleaning_status' => $newStatus, 'inspected_at' => $record->inspected_at]
                    );
                }
            }

            // Log issues reported
            if ($record->isDirty('issues_found') && !empty($record->issues_found)) {
                $location = $record->room ? "Room {$record->room->room_number}" : ($record->area ? $record->area->name : 'Unknown');
                logActivity(
                    'issues_reported',
                    $record,
                    "Issues/damages reported for {$location}",
                    ['issues' => $record->issues_found]
                );
            }

            // Log issue resolution
            if ($record->isDirty('issue_resolved') && $record->issue_resolved) {
                $location = $record->room ? "Room {$record->room->room_number}" : ($record->area ? $record->area->name : 'Unknown');
                $resolvedByName = $record->issueResolvedBy ? $record->issueResolvedBy->name : Auth::user()->name ?? 'Unknown';
                logActivity(
                    'issue_resolved',
                    $record,
                    "Issues resolved for {$location}",
                    [
                        'resolved_by' => $resolvedByName,
                        'resolution_notes' => $record->issue_resolution_notes
                    ],
                    ['issue_resolved' => false],
                    ['issue_resolved' => true, 'issue_resolved_at' => $record->issue_resolved_at]
                );
            }
        });
    }
}
