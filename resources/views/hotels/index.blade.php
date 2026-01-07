@extends('layouts.app')

@section('title', 'Hotels')
@section('page-title', 'Hotels Management')

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('hotels.create') }}" style="display: inline-block; padding: 12px 24px; background: #667eea; color: white; border-radius: 8px; text-decoration: none; font-weight: 500;">
        + Add New Hotel
    </a>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h2 style="margin-bottom: 20px;">All Hotels</h2>
    
    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f8f9fa;">
                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #eee;">Name</th>
                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #eee;">Address</th>
                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #eee;">Owner</th>
                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #eee;">Phone</th>
                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #eee;">Email</th>
                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #eee;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($hotels as $hotel)
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px;">
                        <strong>{{ $hotel->name }}</strong>
                    </td>
                    <td style="padding: 12px; color: #666;">
                        {{ $hotel->address ?: '-' }}
                    </td>
                    <td style="padding: 12px;">
                        {{ $hotel->owner->name }}
                    </td>
                    <td style="padding: 12px; color: #666;">
                        {{ $hotel->phone ?: '-' }}
                    </td>
                    <td style="padding: 12px; color: #666;">
                        {{ $hotel->email ?: '-' }}
                    </td>
                    <td style="padding: 12px;">
                        <div style="display: flex; gap: 8px;">
                            <a href="{{ route('hotels.show', $hotel) }}" style="padding: 6px 12px; background: #3498db; color: white; border-radius: 6px; text-decoration: none; font-size: 13px;">View</a>
                            <a href="{{ route('hotels.edit', $hotel) }}" style="padding: 6px 12px; background: #667eea; color: white; border-radius: 6px; text-decoration: none; font-size: 13px;">Edit</a>
                            <form action="{{ route('hotels.destroy', $hotel) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this hotel? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="padding: 6px 12px; background: #e74c3c; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px;">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 40px; text-align: center; color: #999;">
                        No hotels found. <a href="{{ route('hotels.create') }}" style="color: #667eea;">Create your first hotel</a>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

