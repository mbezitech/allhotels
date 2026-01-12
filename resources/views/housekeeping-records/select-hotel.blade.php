@extends('layouts.app')

@section('title', 'Select Hotel')
@section('page-title', 'Select Hotel')

@section('content')
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h2 style="color: #333; margin-bottom: 20px;">Select a Hotel</h2>
    <p style="color: #666; margin-bottom: 30px;">Please select a hotel to create a housekeeping record.</p>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
        @foreach($hotels as $hotel)
            <a href="{{ route('housekeeping-records.create', ['hotel_id' => $hotel->id]) }}" 
               style="display: block; padding: 20px; background: #f8f9fa; border: 2px solid #e0e0e0; border-radius: 8px; text-decoration: none; color: #333; transition: all 0.3s; hover:border-color: #667eea; hover:background: #fff;">
                <div style="font-weight: 600; color: #333; margin-bottom: 5px; font-size: 18px;">{{ $hotel->name }}</div>
                @if($hotel->address)
                    <div style="font-size: 14px; color: #666;">{{ $hotel->address }}</div>
                @endif
                @if($hotel->owner)
                    <div style="font-size: 12px; color: #999; margin-top: 5px;">Owner: {{ $hotel->owner->name }}</div>
                @endif
            </a>
        @endforeach
    </div>
</div>
@endsection
