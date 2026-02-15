@extends('layouts.app')

@section('title', 'Users')
@section('page-title', 'Users')

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
    .badge-super-admin {
        background: #9c27b0;
        color: white;
    }
    .badge-owner {
        background: #2196f3;
        color: white;
    }
    .badge-active {
        background: #28a745;
        color: white;
    }
    .badge-inactive {
        background: #6c757d;
        color: white;
    }
    .btn-activate {
        background: #28a745;
        color: white;
    }
    .btn-deactivate {
        background: #ffc107;
        color: #333;
    }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">All Users</h2>
    <div>
        @if(isset($deletedCount) && $deletedCount > 0 && !($showDeleted ?? false))
            <a href="{{ route('users.index', ['show_deleted' => 1]) }}" 
               class="btn" 
               style="background: #ff9800; color: white; margin-right: 10px;">
                View Deleted ({{ $deletedCount }})
            </a>
        @endif
        @if($showDeleted ?? false)
            <a href="{{ route('users.index') }}" 
               class="btn" 
               style="background: #95a5a6; color: white; margin-right: 10px;">
                View Active Users
            </a>
        @endif
        <a href="{{ route('users.create') }}" class="btn btn-primary">Add User</a>
    </div>
</div>

@if($showDeleted ?? false)
    <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
        <strong style="color: #856404;">⚠️ Viewing Deleted Users</strong>
        <p style="color: #856404; margin: 5px 0 0 0; font-size: 14px;">These users have been soft-deleted and can be restored or permanently deleted.</p>
    </div>
@endif

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Type</th>
                <th>Owned Hotels</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr style="{{ ($showDeleted ?? false) && $user->trashed() ? 'opacity: 0.7; background-color: #f8f9fa;' : '' }}">
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->is_active ?? true)
                            <span class="badge badge-active">Active</span>
                        @else
                            <span class="badge badge-inactive">Disabled</span>
                        @endif
                    </td>
                    <td>
                        @if($user->is_super_admin)
                            <span class="badge badge-super-admin">Super Admin</span>
                        @elseif($user->ownedHotels->count() > 0)
                            <span class="badge badge-owner">Owner</span>
                        @else
                            <span style="color: #666;">Regular User</span>
                        @endif
                    </td>
                    <td>
                        @if($user->ownedHotels->count() > 0)
                            @foreach($user->ownedHotels as $hotel)
                                <span style="background: #e3f2fd; color: #1976d2; padding: 2px 6px; border-radius: 3px; font-size: 11px; margin-right: 5px;">
                                    {{ $hotel->name }}
                                </span>
                            @endforeach
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                    <td>
                        <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-edit" style="padding: 6px 12px; font-size: 12px;">Edit</a>
                            @php
                                $currentUser = auth()->user();
                                $hotelId = session('hotel_id');
                                // CRITICAL: Never allow users to enable/disable themselves
                                $canActivate = $user->id !== $currentUser->id && 
                                             ($currentUser->isSuperAdmin() || 
                                              ($hotelId && $currentUser->hasPermission('users.activate', $hotelId)));
                            @endphp
                            @if($canActivate)
                                @if($user->is_active ?? true)
                                    <form action="{{ route('users.deactivate', $user) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to disable this user? They will not be able to login.');">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn" style="background: #ffc107; color: #333; padding: 6px 12px; font-size: 12px; border: none; border-radius: 6px; cursor: pointer;">Disable</button>
                                    </form>
                                @else
                                    <form action="{{ route('users.activate', $user) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn" style="background: #28a745; color: white; padding: 6px 12px; font-size: 12px; border: none; border-radius: 6px; cursor: pointer;">Enable</button>
                                    </form>
                                @endif
                            @endif
                            @if($showDeleted ?? false)
                                {{-- Show restore and force delete for deleted users --}}
                                @if($user->id !== auth()->id() && (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('users.manage')))
                                    <form action="{{ route('users.restore', $user->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Restore this user?')">
                                        @csrf
                                        <button type="submit" class="btn" style="background: #28a745; color: white; padding: 6px 12px; font-size: 12px;">Restore</button>
                                    </form>
                                    <form action="{{ route('users.forceDelete', $user->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('⚠️ WARNING: This will permanently delete this user. This action cannot be undone. Are you absolutely sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">Permanently Delete</button>
                                    </form>
                                @endif
                            @else
                                {{-- Show regular delete for active users --}}
                                @if($user->id !== auth()->id() && (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('users.manage')))
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">Delete</button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: #999; padding: 40px;">No users found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection


