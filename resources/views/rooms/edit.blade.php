@extends('layouts.app')

@section('title', 'Edit Room')
@section('page-title', 'Edit Room')

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
    <form method="POST" action="{{ route('rooms.update', $room) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="room_number">Room Number *</label>
            <input type="text" id="room_number" name="room_number" value="{{ old('room_number', $room->room_number) }}" required>
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
                        <option value="{{ $roomType->slug }}" {{ old('room_type', $room->room_type) == $roomType->slug ? 'selected' : '' }}>
                            {{ $roomType->name }} (${{ number_format($roomType->base_price, 2) }}/night)
                        </option>
                    @endforeach
                @else
                    <option value="standard" {{ old('room_type', $room->room_type) == 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="deluxe" {{ old('room_type', $room->room_type) == 'deluxe' ? 'selected' : '' }}>Deluxe</option>
                    <option value="suite" {{ old('room_type', $room->room_type) == 'suite' ? 'selected' : '' }}>Suite</option>
                    <option value="penthouse" {{ old('room_type', $room->room_type) == 'penthouse' ? 'selected' : '' }}>Penthouse</option>
                @endif
            </select>
            @error('room_type')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="status">Status *</label>
            <select id="status" name="status" required>
                <option value="available" {{ old('status', $room->status) == 'available' ? 'selected' : '' }}>Available</option>
                <option value="occupied" {{ old('status', $room->status) == 'occupied' ? 'selected' : '' }}>Occupied</option>
                <option value="maintenance" {{ old('status', $room->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="cleaning" {{ old('status', $room->status) == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
            </select>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="floor">Floor</label>
                <input type="number" id="floor" name="floor" value="{{ old('floor', $room->floor) }}" min="1">
            </div>

            <div class="form-group">
                <label for="capacity">Capacity *</label>
                <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $room->capacity) }}" required min="1">
            </div>
        </div>

        <div class="form-group">
            <label for="price_per_night">Price Per Night *</label>
            <input type="number" id="price_per_night" name="price_per_night" value="{{ old('price_per_night', $room->price_per_night) }}" step="0.01" min="0" required>
            @error('price_per_night')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3">{{ old('description', $room->description) }}</textarea>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Update Room</button>
            <a href="{{ route('rooms.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
