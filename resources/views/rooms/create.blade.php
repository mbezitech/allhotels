@extends('layouts.app')

@section('title', 'Create Room')
@section('page-title', 'Create Room')

@push('styles')
<style>
    .form-group {
        margin-bottom: 20px;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }
    input, select, textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s;
    }
    input:focus, select:focus, textarea:focus {
        outline: none;
        border-color: #667eea;
    }
    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
    }
    .btn-primary {
        background: #667eea;
        color: white;
    }
    .btn-secondary {
        background: #95a5a6;
        color: white;
    }
    .error {
        color: #e74c3c;
        font-size: 13px;
        margin-top: 5px;
        display: block;
    }
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    @media (max-width: 768px) {
        .grid-2 {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <form method="POST" action="{{ route('rooms.store') }}">
        @csrf

        <div class="form-group">
            <label for="room_number">Room Number *</label>
            <input type="text" id="room_number" name="room_number" value="{{ old('room_number') }}" required>
            @error('room_number')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="room_type">Room Type *</label>
            <select id="room_type" name="room_type" required>
                <option value="">-- Select Room Type --</option>
                @if(isset($roomTypes) && $roomTypes->count() > 0)
                    @foreach($roomTypes as $roomType)
                        <option value="{{ $roomType->slug }}" {{ old('room_type') == $roomType->slug ? 'selected' : '' }}>
                            {{ $roomType->name }} (${{ number_format($roomType->base_price, 2) }}/night)
                        </option>
                    @endforeach
                @else
                    <option value="standard">Standard</option>
                    <option value="deluxe">Deluxe</option>
                    <option value="suite">Suite</option>
                    <option value="penthouse">Penthouse</option>
                @endif
            </select>
            @error('room_type')
                <span class="error">{{ $message }}</span>
            @enderror
            @if(isset($roomTypes) && $roomTypes->count() == 0)
                <small style="color: #999; display: block; margin-top: 5px;">
                    No room types defined. <a href="{{ route('room-types.create') }}" style="color: #667eea;">Create one first</a> or use default types.
                </small>
            @endif
        </div>

        <div class="form-group">
            <label for="status">Status *</label>
            <select id="status" name="status" required>
                <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Available</option>
                <option value="occupied" {{ old('status') == 'occupied' ? 'selected' : '' }}>Occupied</option>
                <option value="maintenance" {{ old('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="cleaning" {{ old('status') == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
            </select>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="floor">Floor</label>
                <input type="number" id="floor" name="floor" value="{{ old('floor') }}" min="1">
            </div>

            <div class="form-group">
                <label for="capacity">Capacity *</label>
                <input type="number" id="capacity" name="capacity" value="{{ old('capacity', 2) }}" required min="1">
            </div>
        </div>

        <div class="form-group">
            <label for="price_per_night">Price Per Night *</label>
            <input type="number" id="price_per_night" name="price_per_night" value="{{ old('price_per_night') }}" step="0.01" min="0" required>
            @error('price_per_night')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3">{{ old('description') }}</textarea>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Create Room</button>
            <a href="{{ route('rooms.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
