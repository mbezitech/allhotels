@extends('layouts.app')

@section('title', 'Create Housekeeping Record')
@section('page-title', 'Create Housekeeping Record')

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
        <strong>Note:</strong> Select either a Room OR an Area, not both. This will determine what type of cleaning record you're creating.
    </div>

    <form method="POST" action="{{ route('housekeeping-records.store') }}">
        @csrf

        @if(isset($isSuperAdmin) && $isSuperAdmin && isset($hotels) && $hotels->count() > 0)
        <div class="form-group">
            <label for="hotel_id">Hotel *</label>
            <select id="hotel_id" name="hotel_id" required onchange="window.location.href = '{{ route('housekeeping-records.create') }}?hotel_id=' + this.value;">
                <option value="">-- Select Hotel --</option>
                @foreach($hotels as $h)
                    <option value="{{ $h->id }}" {{ (isset($hotelId) && $hotelId == $h->id) ? 'selected' : '' }}>
                        {{ $h->name }}
                    </option>
                @endforeach
            </select>
            <small style="color: #666; margin-top: 5px; display: block;">Select a hotel to view its rooms and areas.</small>
        </div>
        @elseif(isset($isSuperAdmin) && $isSuperAdmin)
            <input type="hidden" name="hotel_id" value="{{ $hotelId }}">
        @endif

        <div class="grid-2">
            <div class="form-group">
                <label for="room_id">Room (Optional)</label>
                <select id="room_id" name="room_id" onchange="document.getElementById('area_id').value = '';">
                    <option value="">-- No Room --</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ old('room_id', $roomId ?? '') == $room->id ? 'selected' : '' }}>
                            Room {{ $room->room_number }} ({{ $room->cleaning_status ?? 'N/A' }})
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
                        <option value="{{ $area->id }}" {{ old('area_id', $areaId ?? '') == $area->id ? 'selected' : '' }}>
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
                    <option value="">-- Select Staff --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
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
                    <option value="dirty" {{ old('cleaning_status', 'dirty') == 'dirty' ? 'selected' : '' }}>Dirty</option>
                    <option value="cleaning" {{ old('cleaning_status') == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
                    <option value="clean" {{ old('cleaning_status') == 'clean' ? 'selected' : '' }}>Clean</option>
                    @if(auth()->user()->hasPermission('housekeeping_records.inspect', session('hotel_id')) || auth()->user()->isSuperAdmin())
                        <option value="inspected" {{ old('cleaning_status') == 'inspected' ? 'selected' : '' }}>Inspected</option>
                    @endif
                </select>
                @error('cleaning_status')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="notes">Notes</label>
            <textarea id="notes" name="notes" rows="3" placeholder="Additional notes about the cleaning...">{{ old('notes') }}</textarea>
            @error('notes')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="issues_found">Issues Found (Damages, Missing Items, etc.)</label>
            <textarea id="issues_found" name="issues_found" rows="3" placeholder="Describe any issues, damages, or missing items found during cleaning...">{{ old('issues_found') }}</textarea>
            @error('issues_found')
                <span class="error">{{ $message }}</span>
            @enderror
            <small style="color: #666; margin-top: 5px; display: block;">If issues are found, they will be automatically flagged in the system.</small>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Create Record</button>
            <a href="{{ route('housekeeping-records.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

