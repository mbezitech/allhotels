@extends('layouts.app')

@section('title', 'Hotels')
@section('page-title', 'Hotels Management')

@section('content')
<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <a href="{{ route('hotels.create') }}" style="display: inline-block; padding: 12px 24px; background: #667eea; color: white; border-radius: 8px; text-decoration: none; font-weight: 500;">
            + Add New Hotel
        </a>
    </div>
    <div>
        @if($showTrashed)
            <a href="{{ route('hotels.index') }}" style="display: inline-block; padding: 12px 24px; background: #95a5a6; color: white; border-radius: 8px; text-decoration: none; font-weight: 500;">
                View Active Hotels
            </a>
        @else
            @if($trashedCount > 0)
                <a href="{{ route('hotels.index', ['trashed' => true]) }}" style="display: inline-block; padding: 12px 24px; background: #e74c3c; color: white; border-radius: 8px; text-decoration: none; font-weight: 500;">
                    View Deleted Hotels ({{ $trashedCount }})
                </a>
            @endif
        @endif
    </div>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h2 style="margin-bottom: 20px;">{{ $showTrashed ? 'Deleted Hotels' : 'All Hotels' }}</h2>
    
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
                <tr style="border-bottom: 1px solid #eee; {{ $showTrashed ? 'opacity: 0.7;' : '' }}">
                    <td style="padding: 12px;">
                        <strong>{{ $hotel->name }}</strong>
                        @if($showTrashed)
                            <span style="margin-left: 8px; padding: 2px 6px; background: #e74c3c; color: white; border-radius: 3px; font-size: 10px;">Deleted</span>
                            <div style="font-size: 11px; color: #999; margin-top: 4px;">
                                Deleted: {{ $hotel->deleted_at->format('M d, Y H:i') }}
                            </div>
                        @endif
                    </td>
                    <td style="padding: 12px; color: #666;">
                        {{ $hotel->address ?: '-' }}
                    </td>
                    <td style="padding: 12px;">
                        {{ $hotel->owner ? $hotel->owner->name : '-' }}
                    </td>
                    <td style="padding: 12px; color: #666;">
                        {{ $hotel->phone ?: '-' }}
                    </td>
                    <td style="padding: 12px; color: #666;">
                        {{ $hotel->email ?: '-' }}
                    </td>
                    <td style="padding: 12px;">
                        <div style="display: flex; gap: 8px;">
                            @if($showTrashed)
                                <form action="{{ route('hotels.restore', $hotel->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" style="padding: 6px 12px; background: #27ae60; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px;">Restore</button>
                                </form>
                                <form action="{{ route('hotels.force-delete', $hotel->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to PERMANENTLY delete this hotel? This action CANNOT be undone and will delete all associated data!');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="padding: 6px 12px; background: #c0392b; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px;">Permanently Delete</button>
                                </form>
                            @else
                                <a href="{{ route('hotels.show', $hotel) }}" style="padding: 6px 12px; background: #3498db; color: white; border-radius: 6px; text-decoration: none; font-size: 13px;">View</a>
                                <a href="{{ route('hotels.edit', $hotel) }}" style="padding: 6px 12px; background: #667eea; color: white; border-radius: 6px; text-decoration: none; font-size: 13px;">Edit</a>
                                @php
                                    $isOwner = $hotel->owner_id === auth()->id();
                                @endphp
                                @if(!$isOwner)
                                    <form action="{{ route('hotels.destroy', $hotel) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this hotel? The hotel can be restored later.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="padding: 6px 12px; background: #e74c3c; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px;">Delete</button>
                                    </form>
                                @else
                                    <span style="color: #999; font-size: 12px; padding: 6px 12px;">Cannot delete own hotel</span>
                                @endif
                            @endif
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

