@extends('layouts.app')

@section('title', 'Housekeeping Record Details')
@section('page-title', 'Housekeeping Record Details')

@push('styles')
<style>
    .info-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee; }
    .info-row:last-child { border-bottom: none; }
    .info-label { font-weight: 500; color: #666; }
    .info-value { font-weight: 600; }
    .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
    .badge-dirty { background: #f8d7da; color: #721c24; }
    .badge-cleaning { background: #fff3cd; color: #856404; }
    .badge-clean { background: #d4edda; color: #155724; }
    .badge-inspected { background: #cce5ff; color: #004085; }
    .issues-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 8px; margin-top: 20px; }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">Housekeeping Record Details</h2>
    <div style="display: flex; gap: 10px;">
        @if(auth()->user()->hasPermission('housekeeping.manage') || auth()->user()->isSuperAdmin())
            <a href="{{ route('housekeeping-records.edit', $housekeepingRecord) }}" class="btn" style="background: #3498db; color: white;">Edit</a>
        @endif
        <a href="{{ route('housekeeping-records.index') }}" class="btn" style="background: #95a5a6; color: white;">Back to Records</a>
    </div>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <div class="info-row">
        <span class="info-label">Type:</span>
        <span class="info-value">{{ $housekeepingRecord->room_id ? 'Room Cleaning' : 'Area Cleaning' }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Location:</span>
        <span class="info-value">
            @if($housekeepingRecord->room)
                <a href="{{ route('rooms.show', $housekeepingRecord->room) }}" style="color: #667eea;">
                    Room {{ $housekeepingRecord->room->room_number }}
                </a>
            @elseif($housekeepingRecord->area)
                {{ $housekeepingRecord->area->name }}
            @else
                -
            @endif
        </span>
    </div>

    <div class="info-row">
        <span class="info-label">Status:</span>
        <span class="info-value">
            <span class="badge badge-{{ $housekeepingRecord->cleaning_status }}">
                {{ ucfirst($housekeepingRecord->cleaning_status) }}
            </span>
        </span>
    </div>

    <div class="info-row">
        <span class="info-label">Assigned To:</span>
        <span class="info-value">{{ $housekeepingRecord->assignedTo->name ?? '-' }}</span>
    </div>

    @if($housekeepingRecord->started_at)
        <div class="info-row">
            <span class="info-label">Started At:</span>
            <span class="info-value">{{ $housekeepingRecord->started_at->format('M d, Y H:i') }}</span>
        </div>
    @endif

    @if($housekeepingRecord->completed_at)
        <div class="info-row">
            <span class="info-label">Completed At:</span>
            <span class="info-value">{{ $housekeepingRecord->completed_at->format('M d, Y H:i') }}</span>
        </div>
    @endif

    @if($housekeepingRecord->duration_minutes)
        <div class="info-row">
            <span class="info-label">Duration:</span>
            <span class="info-value">{{ $housekeepingRecord->duration_minutes }} minutes</span>
        </div>
    @endif

    @if($housekeepingRecord->inspected_by)
        <div class="info-row">
            <span class="info-label">Inspected By:</span>
            <span class="info-value">{{ $housekeepingRecord->inspectedBy->name ?? '-' }}</span>
        </div>
    @endif

    @if($housekeepingRecord->inspected_at)
        <div class="info-row">
            <span class="info-label">Inspected At:</span>
            <span class="info-value">{{ $housekeepingRecord->inspected_at->format('M d, Y H:i') }}</span>
        </div>
    @endif

    @if($housekeepingRecord->notes)
        <div class="info-row">
            <span class="info-label">Notes:</span>
            <span class="info-value">{{ $housekeepingRecord->notes }}</span>
        </div>
    @endif

    @if($housekeepingRecord->has_issues && $housekeepingRecord->issues_found)
        <div class="issues-box">
            <strong style="color: #856404;">⚠️ Issues Found:</strong>
            <p style="margin-top: 10px; color: #856404;">{{ $housekeepingRecord->issues_found }}</p>
        </div>
    @endif

    <div class="info-row">
        <span class="info-label">Created:</span>
        <span class="info-value">{{ $housekeepingRecord->created_at->format('M d, Y H:i') }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Last Updated:</span>
        <span class="info-value">{{ $housekeepingRecord->updated_at->format('M d, Y H:i') }}</span>
    </div>

    @if($housekeepingRecord->cleaning_status == 'dirty')
        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee;">
            <form action="{{ route('housekeeping-records.start', $housekeepingRecord) }}" method="POST">
                @csrf
                <button type="submit" class="btn" style="background: #ffc107; color: #333;">Start Cleaning</button>
            </form>
        </div>
    @endif

    @if($housekeepingRecord->cleaning_status == 'cleaning')
        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee;">
            <form action="{{ route('housekeeping-records.complete', $housekeepingRecord) }}" method="POST">
                @csrf
                <button type="submit" class="btn" style="background: #28a745; color: white;">Mark as Complete</button>
            </form>
        </div>
    @endif

    @if($housekeepingRecord->cleaning_status == 'clean')
        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee;">
            <form action="{{ route('housekeeping-records.inspect', $housekeepingRecord) }}" method="POST">
                @csrf
                <button type="submit" class="btn" style="background: #667eea; color: white;">Inspect & Approve</button>
            </form>
        </div>
    @endif
</div>
@endsection

