@extends('layouts.app')

@section('title', 'Rooms')
@section('page-title', 'Rooms')

@push('styles')
<style>
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
    }
    .btn-primary {
        background: #667eea;
        color: white;
    }
    .btn-edit {
        background: #3498db;
        color: white;
    }
    .btn-danger {
        background: #e74c3c;
        color: white;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    th {
        background: #f8f9fa;
        font-weight: 600;
    }
    .badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 500;
    }
    .badge-available { background: #d4edda; color: #155724; }
    .badge-occupied { background: #f8d7da; color: #721c24; }
    .badge-maintenance { background: #fff3cd; color: #856404; }
    .badge-cleaning { background: #d1ecf1; color: #0c5460; }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">All Rooms</h2>
    @if(auth()->user()->hasPermission('rooms.manage') || auth()->user()->isSuperAdmin())
        <a href="{{ route('rooms.create') }}" class="btn btn-primary">Add Room</a>
    @endif
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <table>
        <thead>
            <tr>
                <th>Room Number</th>
                <th>Type</th>
                <th>Status</th>
                <th>Floor</th>
                <th>Capacity</th>
                <th>Price/Night</th>
                <th>Bookings</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rooms as $room)
                <tr>
                    <td><strong>{{ $room->room_number }}</strong></td>
                    <td>{{ $room->room_type }}</td>
                    <td>
                        <span class="badge badge-{{ $room->status }}">
                            {{ ucfirst($room->status) }}
                        </span>
                    </td>
                    <td>{{ $room->floor ?? '-' }}</td>
                    <td>{{ $room->capacity }}</td>
                    <td>${{ number_format($room->price_per_night, 2) }}</td>
                    <td>{{ $room->bookings_count }}</td>
                    <td>
                        @if(auth()->user()->hasPermission('rooms.manage') || auth()->user()->isSuperAdmin())
                            <a href="{{ route('rooms.edit', $room) }}" class="btn btn-edit">Edit</a>
                            <form action="{{ route('rooms.destroy', $room) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #999; padding: 40px;">No rooms found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
