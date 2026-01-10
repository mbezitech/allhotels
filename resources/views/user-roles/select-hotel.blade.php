@extends('layouts.app')

@section('title', 'Select Hotel')
@section('page-title', 'Select Hotel for Role Management')

@section('content')
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto;">
    <h2 style="color: #333; font-size: 24px; margin-bottom: 20px;">Select a Hotel</h2>
    <p style="color: #666; margin-bottom: 30px;">Please select a hotel to manage user roles for that hotel.</p>
    
    <div style="display: grid; gap: 15px;">
        @foreach($allHotels as $h)
            <a href="{{ route('user-roles.create', ['hotel_id' => $h->id]) }}" 
               style="display: block; padding: 20px; background: #f8f9fa; border: 2px solid #e0e0e0; border-radius: 8px; text-decoration: none; color: #333; transition: all 0.3s;">
                <strong style="font-size: 18px; color: #667eea;">{{ $h->name }}</strong>
                @if($h->address)
                    <div style="font-size: 14px; color: #666; margin-top: 5px;">{{ $h->address }}</div>
                @endif
                @if($h->owner)
                    <div style="font-size: 12px; color: #999; margin-top: 5px;">Owner: {{ $h->owner->name }}</div>
                @endif
            </a>
        @endforeach
    </div>
    
    @if($allHotels->count() === 0)
        <div style="text-align: center; padding: 40px; color: #999;">
            <p>No hotels found. <a href="{{ route('hotels.create') }}" style="color: #667eea;">Create a hotel first</a></p>
        </div>
    @endif
</div>
@endsection
