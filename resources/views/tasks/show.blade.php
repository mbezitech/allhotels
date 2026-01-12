@extends('layouts.app')

@section('title', 'Task Details')
@section('page-title', 'Task Details')

@push('styles')
<style>
    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-weight: 500;
        color: #666;
    }
    .info-value {
        font-weight: 600;
    }
    .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .badge-pending { background: #fff3cd; color: #856404; }
    .badge-in_progress { background: #d1ecf1; color: #0c5460; }
    .badge-completed { background: #d4edda; color: #155724; }
    .badge-cancelled { background: #f8d7da; color: #721c24; }
    .badge-low { background: #e2e3e5; color: #383d41; }
    .badge-medium { background: #d1ecf1; color: #0c5460; }
    .badge-high { background: #fff3cd; color: #856404; }
    .badge-urgent { background: #f8d7da; color: #721c24; }
    .badge-housekeeping { background: #e3f2fd; color: #1976d2; }
    .badge-maintenance { background: #fff3cd; color: #856404; }
    .badge-cleaning { background: #d1ecf1; color: #0c5460; }
    .overdue {
        color: #e74c3c;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">Task Details</h2>
    <div style="display: flex; gap: 10px;">
        @if(auth()->user()->hasPermission('tasks.edit', session('hotel_id')) || auth()->user()->isSuperAdmin())
            <a href="{{ route('tasks.edit', $task) }}" class="btn" style="background: #3498db; color: white;">Edit</a>
        @endif
        <a href="{{ route('tasks.index') }}" class="btn" style="background: #95a5a6; color: white;">Back to Tasks</a>
    </div>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <div class="info-row">
        <span class="info-label">Title:</span>
        <span class="info-value">{{ $task->title }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Type:</span>
        <span class="info-value">
            <span class="badge badge-{{ $task->type }}">
                {{ ucfirst($task->type) }}
            </span>
        </span>
    </div>

    <div class="info-row">
        <span class="info-label">Status:</span>
        <span class="info-value">
            <span class="badge badge-{{ $task->status }}">
                {{ ucfirst(str_replace('_', ' ', $task->status)) }}
            </span>
        </span>
    </div>

    <div class="info-row">
        <span class="info-label">Priority:</span>
        <span class="info-value">
            <span class="badge badge-{{ $task->priority }}">
                {{ ucfirst($task->priority) }}
            </span>
        </span>
    </div>

    @if($task->room)
        <div class="info-row">
            <span class="info-label">Room:</span>
            <span class="info-value">
                <a href="{{ route('rooms.show', $task->room) }}" style="color: #667eea;">
                    Room {{ $task->room->room_number }}
                </a>
            </span>
        </div>
    @endif

    @if($task->assignedTo)
        <div class="info-row">
            <span class="info-label">Assigned To:</span>
            <span class="info-value">{{ $task->assignedTo->name }}</span>
        </div>
    @else
        <div class="info-row">
            <span class="info-label">Assigned To:</span>
            <span class="info-value" style="color: #999;">Unassigned</span>
        </div>
    @endif

    <div class="info-row">
        <span class="info-label">Created By:</span>
        <span class="info-value">{{ $task->createdBy->name }}</span>
    </div>

    @if($task->due_date)
        <div class="info-row">
            <span class="info-label">Due Date:</span>
            <span class="info-value {{ $task->isOverdue() ? 'overdue' : '' }}">
                {{ $task->due_date->format('M d, Y H:i') }}
                @if($task->isOverdue())
                    <span style="margin-left: 5px;">⚠️ Overdue</span>
                @endif
            </span>
        </div>
    @endif

    @if($task->completed_at)
        <div class="info-row">
            <span class="info-label">Completed At:</span>
            <span class="info-value">{{ $task->completed_at->format('M d, Y H:i') }}</span>
        </div>
    @endif

    @if($task->description)
        <div class="info-row">
            <span class="info-label">Description:</span>
            <span class="info-value">{{ $task->description }}</span>
        </div>
    @endif

    @if($task->notes)
        <div class="info-row">
            <span class="info-label">Notes:</span>
            <span class="info-value">{{ $task->notes }}</span>
        </div>
    @endif

    <div class="info-row">
        <span class="info-label">Created:</span>
        <span class="info-value">{{ $task->created_at->format('M d, Y H:i') }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Last Updated:</span>
        <span class="info-value">{{ $task->updated_at->format('M d, Y H:i') }}</span>
    </div>
</div>
@endsection

