@extends('layouts.app')

@section('title', 'Create Hotel')
@section('page-title', 'Create New Hotel')

@section('content')
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 800px;">
    <h2 style="margin-bottom: 20px;">Create New Hotel</h2>
    
    <form method="POST" action="{{ route('hotels.store') }}">
        @csrf

        <div style="margin-bottom: 20px;">
            <label for="name" style="display: block; margin-bottom: 8px; font-weight: 500;">Hotel Name *</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required 
                   style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
            @error('name')
                <span style="color: #e74c3c; font-size: 13px; margin-top: 5px; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom: 20px;">
            <label for="address" style="display: block; margin-bottom: 8px; font-weight: 500;">Address</label>
            <textarea id="address" name="address" rows="3" 
                      style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">{{ old('address') }}</textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label for="phone" style="display: block; margin-bottom: 8px; font-weight: 500;">Phone</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}" 
                       style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
            </div>

            <div>
                <label for="email" style="display: block; margin-bottom: 8px; font-weight: 500;">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" 
                       style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
            </div>
        </div>

        <div style="margin-bottom: 20px;">
            <label for="owner_id" style="display: block; margin-bottom: 8px; font-weight: 500;">Owner *</label>
            <select id="owner_id" name="owner_id" required 
                    style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                <option value="">-- Select Owner --</option>
                @foreach($owners as $owner)
                    <option value="{{ $owner->id }}" {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                        {{ $owner->name }} ({{ $owner->email }})
                    </option>
                @endforeach
            </select>
            @error('owner_id')
                <span style="color: #e74c3c; font-size: 13px; margin-top: 5px; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom: 20px;">
            <label for="timezone" style="display: block; margin-bottom: 8px; font-weight: 500;">Timezone *</label>
            <select id="timezone" name="timezone" required
                    style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                @foreach($timezones ?? [] as $region => $zones)
                    <optgroup label="{{ $region }}">
                        @foreach($zones as $value => $label)
                            <option value="{{ $value }}" {{ old('timezone', 'Africa/Nairobi') == $value ? 'selected' : '' }}>
                                {{ $label }} ({{ $value }})
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            <small style="color: #666; display: block; margin-top: 5px;">Dates and times will be shown in this timezone for this hotel.</small>
            @error('timezone')
                <span style="color: #e74c3c; font-size: 13px; margin-top: 5px; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: flex; gap: 15px; margin-top: 30px;">
            <button type="submit" style="padding: 12px 24px; background: #667eea; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer;">
                Create Hotel
            </button>
            <a href="{{ route('hotels.index') }}" style="padding: 12px 24px; background: #95a5a6; color: white; border-radius: 8px; text-decoration: none; font-size: 14px; display: inline-block;">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

