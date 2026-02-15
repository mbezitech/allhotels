@extends('layouts.app')

@section('title', $hotel->name)
@section('page-title', $hotel->name)

@section('content')
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="color: #666; font-size: 14px; font-weight: 500; margin-bottom: 10px;">Total Rooms</h3>
        <div style="font-size: 32px; font-weight: bold; color: #333;">{{ $stats['total_rooms'] }}</div>
    </div>

    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="color: #666; font-size: 14px; font-weight: 500; margin-bottom: 10px;">Total Bookings</h3>
        <div style="font-size: 32px; font-weight: bold; color: #333;">{{ $stats['total_bookings'] }}</div>
    </div>

    <div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h3 style="color: #666; font-size: 14px; font-weight: 500; margin-bottom: 10px;">Active Bookings</h3>
        <div style="font-size: 32px; font-weight: bold; color: #333;">{{ $stats['active_bookings'] }}</div>
    </div>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Hotel Information</h2>
        <a href="{{ route('hotels.edit', $hotel) }}" style="padding: 10px 20px; background: #667eea; color: white; border-radius: 8px; text-decoration: none; font-size: 14px;">
            Edit Hotel
        </a>
    </div>
    
    <div style="display: grid; gap: 15px;">
        <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee;">
            <span style="font-weight: 500; color: #666;">Name:</span>
            <span style="font-weight: 600;">{{ $hotel->name }}</span>
        </div>

        @if($hotel->address)
            <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee;">
                <span style="font-weight: 500; color: #666;">Address:</span>
                <span>{{ $hotel->address }}</span>
            </div>
        @endif

        @if($hotel->phone)
            <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee;">
                <span style="font-weight: 500; color: #666;">Phone:</span>
                <span>{{ $hotel->phone }}</span>
            </div>
        @endif

        @if($hotel->email)
            <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee;">
                <span style="font-weight: 500; color: #666;">Email:</span>
                <span>{{ $hotel->email }}</span>
            </div>
        @endif

        <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee;">
            <span style="font-weight: 500; color: #666;">Owner:</span>
            <span>{{ $hotel->owner->name }} ({{ $hotel->owner->email }})</span>
        </div>

        <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee;">
            <span style="font-weight: 500; color: #666;">Timezone:</span>
            <span>{{ $hotel->timezone ?? config('app.timezone') }}</span>
        </div>

        <div style="display: flex; justify-content: space-between; padding: 12px 0;">
            <span style="font-weight: 500; color: #666;">Created:</span>
            <span>{{ $hotel->created_at->format('M d, Y') }}</span>
        </div>
    </div>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <div style="display: flex; gap: 15px;">
        <a href="{{ route('hotels.index') }}" style="padding: 10px 20px; background: #95a5a6; color: white; border-radius: 8px; text-decoration: none; font-size: 14px;">
            Back to Hotels
        </a>
        @if(session('hotel_id') != $hotel->id)
            <form action="{{ route('login') }}" method="GET" style="display: inline;">
                <input type="hidden" name="hotel_id" value="{{ $hotel->id }}">
                <button type="submit" style="padding: 10px 20px; background: #4caf50; color: white; border: none; border-radius: 8px; font-size: 14px; cursor: pointer;">
                    Switch to This Hotel
                </button>
            </form>
        @endif
    </div>
</div>
@endsection

