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
                <th>Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($areas as $area)
                <tr>
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
                    <td colspan="4" style="text-align: center; color: #999; padding: 40px;">No hotel areas found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

