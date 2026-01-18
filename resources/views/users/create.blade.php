@extends('layouts.app')

@section('title', 'Create User')
@section('page-title', 'Create User')

@push('styles')
<style>
    .card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .form-group {
        margin-bottom: 20px;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }
    input, select {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
    }
    input:focus, select:focus {
        outline: none;
        border-color: #667eea;
    }
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .checkbox-group input {
        width: auto;
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
</style>
@endpush

@section('content')
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    @if($errors->any())
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #dc3545;">
            <strong>⚠️ Please fix the following errors:</strong>
            <ul style="margin: 10px 0 0 20px; padding: 0;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('users.store') }}">
        @csrf
        @if(request('return_to') === 'user-roles')
            <input type="hidden" name="return_to" value="user-roles">
        @endif

        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
            @error('email')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Password *</label>
            <input type="password" id="password" name="password" required minlength="8">
            @error('password')
                <span class="error">{{ $message }}</span>
            @enderror
            <small style="color: #666; display: block; margin-top: 5px;">Minimum 8 characters</small>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirm Password *</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">
        </div>

        @if(auth()->user()->isSuperAdmin())
        <div class="form-group">
            <div class="checkbox-group">
                <input type="checkbox" id="is_super_admin" name="is_super_admin" {{ old('is_super_admin') ? 'checked' : '' }}>
                <label for="is_super_admin" style="margin: 0; font-weight: normal;">Super Admin</label>
            </div>
            <small style="color: #666; display: block; margin-top: 5px;">Super admins have access to all hotels and can manage the system globally</small>
        </div>

        <div class="form-group">
            <label for="hotel_id">Assign as Owner of Hotel (Optional)</label>
            <select id="hotel_id" name="hotel_id">
                <option value="">-- Select Hotel (Optional) --</option>
                @foreach(\App\Models\Hotel::all() as $hotel)
                    <option value="{{ $hotel->id }}" {{ old('hotel_id') == $hotel->id ? 'selected' : '' }}>
                        {{ $hotel->name }}
                    </option>
                @endforeach
            </select>
            <small style="color: #666; display: block; margin-top: 5px;">If selected, user will be assigned as owner and automatically get Admin role with all permissions for this hotel (except deleting admin roles and hotels)</small>
        </div>
        @endif

        <div style="margin-top: 30px;">
            <button type="submit" class="btn btn-primary">Create User</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection


