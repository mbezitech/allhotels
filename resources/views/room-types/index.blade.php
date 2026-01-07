@extends('layouts.app')

@section('title', 'Room Types')
@section('page-title', 'Room Types')

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
    .badge-active { background: #d4edda; color: #155724; }
    .badge-inactive { background: #e2e3e5; color: #383d41; }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">Room Types</h2>
    @if(auth()->user()->hasPermission('rooms.manage') || auth()->user()->isSuperAdmin())
        <a href="{{ route('room-types.create') }}" class="btn btn-primary">Add Room Type</a>
    @endif
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Description</th>
                <th>Base Price</th>
                <th>Default Capacity</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($roomTypes as $roomType)
                <tr>
                    <td><strong>{{ $roomType->name }}</strong></td>
                    <td>{{ Str::limit($roomType->description, 50) ?? '-' }}</td>
                    <td>${{ number_format($roomType->base_price, 2) }}</td>
                    <td>{{ $roomType->default_capacity }}</td>
                    <td>
                        <span class="badge badge-{{ $roomType->is_active ? 'active' : 'inactive' }}">
                            {{ $roomType->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        @if(auth()->user()->hasPermission('rooms.manage') || auth()->user()->isSuperAdmin())
                            <a href="{{ route('room-types.edit', $roomType) }}" class="btn btn-edit">Edit</a>
                            <form action="{{ route('room-types.destroy', $roomType) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #999; padding: 40px;">No room types found. Create your first room type to get started.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

