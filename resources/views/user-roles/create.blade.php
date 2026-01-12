@extends('layouts.app')

@section('title', 'Assign Roles')
@section('page-title', 'Assign Roles')

@push('styles')
<style>
    .form-group {
        margin-bottom: 20px;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }
    select {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s;
    }
    select:focus {
        outline: none;
        border-color: #667eea;
    }
    .btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
    }
    .btn-primary {
        background: #667eea;
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
        background: #667eea;
        color: white;
        margin: 2px;
    }
</style>
@endpush

@section('content')
@if(isset($isSuperAdmin) && $isSuperAdmin && isset($allHotels) && $allHotels->count() > 0)
    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <strong>Current Hotel:</strong> {{ $hotel->name }}
            </div>
            <select onchange="if(this.value) window.location.href='{{ route('user-roles.create') }}?hotel_id='+this.value" 
                    style="padding: 8px 16px; border: 2px solid #667eea; border-radius: 6px; background: white; cursor: pointer;">
                <option value="">Switch Hotel...</option>
                @foreach($allHotels as $h)
                    <option value="{{ $h->id }}" {{ $h->id == $hotel->id ? 'selected' : '' }}>{{ $h->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
@endif

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="color: #333; font-size: 24px; margin: 0;">Assign New Role</h2>
        @if(auth()->user()->isSuperAdmin() || (auth()->user()->hasPermission('users.manage', $hotel->id ?? null)))
            <a href="{{ route('users.create', ['return_to' => 'user-roles']) }}" class="btn btn-primary" style="background: #28a745; color: white; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-size: 14px;">
                + Create New User
            </a>
        @endif
    </div>
    
    <form method="POST" action="{{ route('user-roles.store') }}">
        @csrf
        @if(isset($isSuperAdmin) && $isSuperAdmin)
            <input type="hidden" name="hotel_id" value="{{ $hotel->id }}">
        @endif

        <div class="form-group">
            <label for="user_id">User *</label>
            <div style="display: flex; gap: 10px; align-items: flex-start;">
                <select id="user_id" name="user_id" required style="flex: 1;">
                    <option value="">-- Select User --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                @if(auth()->user()->isSuperAdmin() || (auth()->user()->hasPermission('users.manage', $hotel->id ?? null)))
                    <a href="{{ route('users.create', ['return_to' => 'user-roles']) }}" class="btn" style="background: #28a745; color: white; text-decoration: none; padding: 12px 16px; border-radius: 6px; white-space: nowrap;">
                        + New User
                    </a>
                @endif
            </div>
            @error('user_id')
                <span style="color: #e74c3c; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
            @enderror
            @if(auth()->user()->isSuperAdmin() || (auth()->user()->hasPermission('users.manage', $hotel->id ?? null)))
                <small style="color: #666; display: block; margin-top: 5px;">Can't find a user? <a href="{{ route('users.create', ['return_to' => 'user-roles']) }}" style="color: #667eea; text-decoration: underline;">Create a new user</a></small>
            @endif
        </div>

        <div class="form-group">
            <label for="role_id">Role *</label>
            <select id="role_id" name="role_id" required>
                <option value="">-- Select Role --</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                @endforeach
            </select>
            @error('role_id')
                <span style="color: #e74c3c; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Assign Role</button>
    </form>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <h2 style="color: #333; font-size: 24px; margin-bottom: 20px;">Current Role Assignments - {{ $hotel->name }}</h2>
    
    @if($usersWithRoles->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($usersWithRoles as $user)
                    <tr>
                        <td>
                            <strong>{{ $user->name }}</strong>
                            @if($hotel->owner_id == $user->id)
                                <span style="font-size: 11px; color: #667eea; margin-left: 5px;">(Owner)</span>
                            @endif
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @if($user->roles->count() > 0)
                                @foreach($user->roles as $role)
                                    <span class="badge">{{ $role->name }}</span>
                                @endforeach
                            @else
                                <span style="color: #999; font-size: 12px;">No roles assigned</span>
                            @endif
                        </td>
                        <td>
                            @if($user->roles->count() > 0)
                                @foreach($user->roles as $role)
                                    <form action="{{ route('user-roles.destroy', ['user' => $user->id, 'role' => $role->id]) }}" 
                                          method="POST" 
                                          style="display: inline; margin-right: 5px;"
                                          onsubmit="return confirm('Remove {{ $role->name }} role from {{ $user->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">
                                            Remove {{ $role->name }}
                                        </button>
                                    </form>
                                @endforeach
                            @else
                                <span style="color: #999; font-size: 12px;">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: #999; text-align: center; padding: 40px;">No users found for this hotel. Users must be assigned roles in this hotel to appear here.</p>
    @endif
</div>
@endsection
