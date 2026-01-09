@extends('layouts.app')

@section('title', 'Edit Hotel Area')
@section('page-title', 'Edit Hotel Area')

@push('styles')
<style>
    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
    input, textarea, select { width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; transition: border-color 0.3s; }
    input:focus, textarea:focus, select:focus { outline: none; border-color: #667eea; }
    .btn { padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
    .btn-primary { background: #667eea; color: white; }
    .btn-secondary { background: #95a5a6; color: white; }
    .error { color: #e74c3c; font-size: 13px; margin-top: 5px; display: block; }
</style>
@endpush

@section('content')
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <form method="POST" action="{{ route('hotel-areas.update', $hotelArea) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Area Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $hotelArea->name) }}" required>
            @error('name')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3">{{ old('description', $hotelArea->description) }}</textarea>
            @error('description')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $hotelArea->is_active) ? 'checked' : '' }}>
                Active
            </label>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Update Area</button>
            <a href="{{ route('hotel-areas.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

