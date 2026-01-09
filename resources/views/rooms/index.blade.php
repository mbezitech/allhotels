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
    .btn-copy {
        background: #28a745;
        color: white;
        padding: 6px 12px;
        font-size: 12px;
        margin-left: 5px;
    }
    .btn-copy:hover {
        background: #218838;
    }
    .btn-copy.copied {
        background: #6c757d;
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
    .booking-link {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .booking-link-input {
        flex: 1;
        padding: 6px 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 12px;
        font-family: monospace;
        background: #f8f9fa;
        color: #333;
        min-width: 200px;
    }
    .booking-link-input:focus {
        outline: none;
        border-color: #667eea;
        background: white;
    }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">All Rooms</h2>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('public.search', $hotel->slug) }}" target="_blank" class="btn" style="background: #28a745;">View Public Search</a>
        @if(auth()->user()->hasPermission('rooms.manage') || auth()->user()->isSuperAdmin())
            <a href="{{ route('rooms.create') }}" class="btn btn-primary">Add Room</a>
        @endif
    </div>
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
                <th>Public Booking Link</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rooms as $room)
                @php
                    $bookingUrl = url('/book/' . $hotel->slug . '/' . $room->id);
                @endphp
                <tr>
                    <td><strong>{{ $room->room_number }}</strong></td>
                    <td>{{ $room->roomType->name ?? 'N/A' }}</td>
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
                        <div class="booking-link">
                            <input type="text" 
                                   value="{{ $bookingUrl }}" 
                                   readonly 
                                   class="booking-link-input" 
                                   id="booking-link-{{ $room->id }}"
                                   onclick="this.select();">
                            <button type="button" 
                                    class="btn btn-copy" 
                                    onclick="copyBookingLink({{ $room->id }}, '{{ $bookingUrl }}')"
                                    id="copy-btn-{{ $room->id }}">
                                Copy
                            </button>
                        </div>
                    </td>
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
                    <td colspan="9" style="text-align: center; color: #999; padding: 40px;">No rooms found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script>
function copyBookingLink(roomId, url) {
    const input = document.getElementById('booking-link-' + roomId);
    const button = document.getElementById('copy-btn-' + roomId);
    
    // Select and copy the text
    input.select();
    input.setSelectionRange(0, 99999); // For mobile devices
    
    try {
        navigator.clipboard.writeText(url).then(function() {
            // Success feedback
            const originalText = button.textContent;
            button.textContent = 'Copied!';
            button.classList.add('copied');
            
            setTimeout(function() {
                button.textContent = originalText;
                button.classList.remove('copied');
            }, 2000);
        }).catch(function(err) {
            // Fallback for older browsers
            document.execCommand('copy');
            const originalText = button.textContent;
            button.textContent = 'Copied!';
            button.classList.add('copied');
            
            setTimeout(function() {
                button.textContent = originalText;
                button.classList.remove('copied');
            }, 2000);
        });
    } catch (err) {
        // Fallback for older browsers
        document.execCommand('copy');
        const originalText = button.textContent;
        button.textContent = 'Copied!';
        button.classList.add('copied');
        
        setTimeout(function() {
            button.textContent = originalText;
            button.classList.remove('copied');
        }, 2000);
    }
}
</script>
@endpush
@endsection
