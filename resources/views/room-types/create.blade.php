@extends('layouts.app')

@section('title', 'Create Room Type')
@section('page-title', 'Create Room Type')

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
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .checkbox-group input {
        width: auto;
    }
</style>
@endpush

@section('content')
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <form method="POST" action="{{ route('room-types.store') }}">
        @csrf

        <div class="form-group">
            <label for="name">Room Type Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g., Standard, Deluxe, Suite">
            @error('name')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3" placeholder="Describe this room type...">{{ old('description') }}</textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="base_price">Base Price Per Night *</label>
                <input type="number" id="base_price" name="base_price" value="{{ old('base_price') }}" step="0.01" min="0" required>
                @error('base_price')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="default_capacity">Default Capacity *</label>
                <input type="number" id="default_capacity" name="default_capacity" value="{{ old('default_capacity', 2) }}" min="1" required>
                @error('default_capacity')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <div class="checkbox-group">
                <input type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <label for="is_active" style="margin: 0; font-weight: normal;">Active (available for use)</label>
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Create Room Type</button>
            <a href="{{ route('room-types.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

