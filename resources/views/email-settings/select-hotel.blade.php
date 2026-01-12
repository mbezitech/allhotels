@extends('layouts.app')

@section('title', 'Select Hotel - Email Settings')
@section('page-title', 'Select Hotel')

@section('content')
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h2 style="color: #333; font-size: 24px; margin-bottom: 20px;">Select Hotel to Manage Email Settings</h2>
    
    @if($allHotels->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 15px;">
            @foreach($allHotels as $hotel)
                <a href="{{ route('email-settings.index', ['hotel_id' => $hotel->id]) }}" 
                   style="display: block; padding: 20px; background: #f8f9fa; border: 2px solid #e0e0e0; border-radius: 8px; text-decoration: none; transition: all 0.3s;"
                   onmouseover="this.style.borderColor='#667eea'; this.style.background='#f0f4ff';"
                   onmouseout="this.style.borderColor='#e0e0e0'; this.style.background='#f8f9fa';">
                    <div style="font-size: 18px; font-weight: 600; color: #333; margin-bottom: 5px;">{{ $hotel->name }}</div>
                    @if($hotel->address)
                        <div style="font-size: 12px; color: #666;">{{ \Illuminate\Support\Str::limit($hotel->address, 50) }}</div>
                    @endif
                </a>
            @endforeach
        </div>
    @else
        <p style="color: #999; text-align: center; padding: 40px;">No hotels found.</p>
    @endif
</div>
@endsection
