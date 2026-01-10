@extends('layouts.app')

@section('title', 'Hotel Areas')
@section('page-title', 'Hotel Areas')

@push('styles')
<style>
    .btn { padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
    .btn-primary { background: #667eea; color: white; }
    .btn-edit { background: #3498db; color: white; }
    .btn-danger { background: #e74c3c; color: white; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    th { background: #f8f9fa; font-weight: 600; }
    .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500; }
    .badge-active { background: #d4edda; color: #155724; }
    .badge-inactive { background: #f8d7da; color: #721c24; }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">Hotel Areas</h2>
    @if(auth()->user()->hasPermission('housekeeping.manage') || auth()->user()->isSuperAdmin())
        <a href="{{ route('hotel-areas.create') }}" class="btn btn-primary">Create Area</a>
    @endif
</div>

@if(isset($isSuperAdmin) && $isSuperAdmin && isset($hotels) && $hotels->count() > 0)
    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('hotel-areas.index') }}" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Filter by Hotel:</label>
                <select name="hotel_id" onchange="this.form.submit()" style="padding: 8px 16px; border: 2px solid #667eea; border-radius: 6px; background: white; cursor: pointer; min-width: 200px;">
                    <option value="">All Hotels</option>
                    @foreach($hotels as $h)
                        <option value="{{ $h->id }}" {{ (isset($selectedHotelId) && $selectedHotelId == $h->id) || request('hotel_id') == $h->id ? 'selected' : '' }}>
                            {{ $h->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(request('hotel_id'))
                <a href="{{ route('hotel-areas.index') }}" style="padding: 8px 16px; background: #95a5a6; color: white; border-radius: 6px; text-decoration: none; font-size: 14px;">
                    Clear Filter
                </a>
            @endif
        </form>
    </div>
@endif

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    @if(session('success'))
        <div style="background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div style="background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px;">{{ session('error') }}</div>
    @endif

    <table>
        <thead>
            <tr>
                @if(isset($isSuperAdmin) && $isSuperAdmin)
                <th>Hotel</th>
                @endif
                <th>Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($areas as $area)
                <tr>
                    @if(isset($isSuperAdmin) && $isSuperAdmin)
                    <td>
                        <strong style="color: #667eea;">{{ $area->hotel->name ?? 'Unknown Hotel' }}</strong>
                        @if($area->hotel && $area->hotel->address)
                            <div style="font-size: 11px; color: #999; margin-top: 2px;">{{ \Illuminate\Support\Str::limit($area->hotel->address, 30) }}</div>
                        @endif
                    </td>
                    @endif
                    <td><strong>{{ $area->name }}</strong></td>
                    <td>{{ $area->description ?? '-' }}</td>
                    <td>
                        <span class="badge badge-{{ $area->is_active ? 'active' : 'inactive' }}">
                            {{ $area->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('hotel-areas.show', $area) }}" class="btn" style="background: #3498db; color: white; padding: 6px 12px; font-size: 12px;">View</a>
                        @if(auth()->user()->hasPermission('housekeeping.manage') || auth()->user()->isSuperAdmin())
                            <a href="{{ route('hotel-areas.edit', $area) }}" class="btn btn-edit" style="padding: 6px 12px; font-size: 12px;">Edit</a>
                            <form action="{{ route('hotel-areas.destroy', $area) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this area?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ (isset($isSuperAdmin) && $isSuperAdmin) ? '5' : '4' }}" style="text-align: center; color: #999; padding: 40px;">No hotel areas found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

