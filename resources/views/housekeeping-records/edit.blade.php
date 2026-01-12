@extends('layouts.app')

@section('title', 'Edit Housekeeping Record')
@section('page-title', 'Edit Housekeeping Record')

@push('styles')
<style>
    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
    input, select, textarea { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; transition: border-color 0.3s; }
    input:focus, select:focus, textarea:focus { outline: none; border-color: #667eea; }
    .btn { padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
    .btn-primary { background: #667eea; color: white; }
    .btn-secondary { background: #95a5a6; color: white; }
    .error { color: #e74c3c; font-size: 13px; margin-top: 5px; display: block; }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    @media (max-width: 768px) { .grid-2 { grid-template-columns: 1fr; } }
    .info-box { background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #667eea; }
</style>
@endpush

@section('content')
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <div class="info-box">
        <strong>Note:</strong> Select either a Room OR an Area, not both.
    </div>

    <form method="POST" action="{{ route('housekeeping-records.update', $housekeepingRecord) }}">
        @csrf
        @method('PUT')

        <div class="grid-2">
            <div class="form-group">
                <label for="room_id">Room (Optional)</label>
                <select id="room_id" name="room_id" onchange="document.getElementById('area_id').value = '';">
                    <option value="">-- No Room --</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ old('room_id', $housekeepingRecord->room_id) == $room->id ? 'selected' : '' }}>
                            Room {{ $room->room_number }}
                        </option>
                    @endforeach
                </select>
                @error('room_id')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="area_id">Area (Optional)</label>
                <select id="area_id" name="area_id" onchange="document.getElementById('room_id').value = '';">
                    <option value="">-- No Area --</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}" {{ old('area_id', $housekeepingRecord->area_id) == $area->id ? 'selected' : '' }}>
                            {{ $area->name }}
                        </option>
                    @endforeach
                </select>
                @error('area_id')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="assigned_to">Assign To *</label>
                <select id="assigned_to" name="assigned_to" required>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('assigned_to', $housekeepingRecord->assigned_to) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                @error('assigned_to')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="cleaning_status">Cleaning Status *</label>
                <select id="cleaning_status" name="cleaning_status" required>
                    <option value="dirty" {{ old('cleaning_status', $housekeepingRecord->cleaning_status) == 'dirty' ? 'selected' : '' }}>Dirty</option>
                    <option value="cleaning" {{ old('cleaning_status', $housekeepingRecord->cleaning_status) == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                    <option value="clean" {{ old('cleaning_status', $housekeepingRecord->cleaning_status) == 'clean' ? 'selected' : '' }}>Clean</option>
                    @if(auth()->user()->hasPermission('housekeeping_records.inspect', session('hotel_id')) || auth()->user()->isSuperAdmin())
                        <option value="inspected" {{ old('cleaning_status', $housekeepingRecord->cleaning_status) == 'inspected' ? 'selected' : '' }}>Inspected</option>
                    @endif
                </select>
                @error('cleaning_status')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="started_at">Started At</label>
                <input type="datetime-local" id="started_at" name="started_at" value="{{ old('started_at', $housekeepingRecord->started_at ? $housekeepingRecord->started_at->format('Y-m-d\TH:i') : '') }}">
                @error('started_at')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="completed_at">Completed At</label>
                <input type="datetime-local" id="completed_at" name="completed_at" value="{{ old('completed_at', $housekeepingRecord->completed_at ? $housekeepingRecord->completed_at->format('Y-m-d\TH:i') : '') }}">
                @error('completed_at')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="3">{{ old('notes', $housekeepingRecord->notes) }}</textarea>
            @error('notes')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="issues_found">Issues Found (Damages, Missing Items, etc.)</label>
            <textarea id="issues_found" name="issues_found" rows="3">{{ old('issues_found', $housekeepingRecord->issues_found) }}</textarea>
            @error('issues_found')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-top: 30px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Update Record</button>
            <a href="{{ route('housekeeping-records.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

