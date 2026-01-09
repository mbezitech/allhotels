@extends('layouts.app')

@section('title', 'Hotel Area Details')
@section('page-title', 'Hotel Area Details')

@push('styles')
<style>
    .info-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee; }
    .info-row:last-child { border-bottom: none; }
    .info-label { font-weight: 500; color: #666; }
    .info-value { font-weight: 600; }
    .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
    .badge-active { background: #d4edda; color: #155724; }
    .badge-inactive { background: #f8d7da; color: #721c24; }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">Area Details</h2>
    <div style="display: flex; gap: 10px;">
        @if(auth()->user()->hasPermission('housekeeping.manage') || auth()->user()->isSuperAdmin())
            <a href="{{ route('hotel-areas.edit', $hotelArea) }}" class="btn" style="background: #3498db; color: white;">Edit</a>
        @endif
        <a href="{{ route('hotel-areas.index') }}" class="btn" style="background: #95a5a6; color: white;">Back to Areas</a>
    </div>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <div class="info-row">
        <span class="info-label">Name:</span>
        <span class="info-value">{{ $hotelArea->name }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Description:</span>
        <span class="info-value">{{ $hotelArea->description ?? '-' }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Status:</span>
        <span class="info-value">
            <span class="badge badge-{{ $hotelArea->is_active ? 'active' : 'inactive' }}">
                {{ $hotelArea->is_active ? 'Active' : 'Inactive' }}
            </span>
        </span>
    </div>

    <div class="info-row">
        <span class="info-label">Created:</span>
        <span class="info-value">{{ $hotelArea->created_at->format('M d, Y H:i') }}</span>
    </div>

    <div class="info-row">
        <span class="info-label">Last Updated:</span>
        <span class="info-value">{{ $hotelArea->updated_at->format('M d, Y H:i') }}</span>
    </div>

    @if($hotelArea->housekeepingRecords->count() > 0)
        <div style="margin-top: 30px; padding-top: 20px; border-top: 2px solid #eee;">
            <h3 style="color: #333; font-size: 18px; margin-bottom: 15px;">Housekeeping Records ({{ $hotelArea->housekeepingRecords->count() }})</h3>
            <p style="color: #666;">This area has {{ $hotelArea->housekeepingRecords->count() }} housekeeping record(s).</p>
            <a href="{{ route('housekeeping-records.index', ['area_id' => $hotelArea->id]) }}" class="btn" style="background: #667eea; color: white; margin-top: 10px;">View Records</a>
        </div>
    @endif
</div>
@endsection

