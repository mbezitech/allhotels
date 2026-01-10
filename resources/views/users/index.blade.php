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
        background: #d4edda;
        color: #155724;
    }
    .badge-inactive {
        background: #f8d7da;
        color: #721c24;
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
    <a href="{{ route('users.create') }}" class="btn btn-primary">Add User</a>
</div>

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
                <tr>
                    <td><strong>{{ $user->name }}</strong></td>
                    <td>{{ $user->email }}</td>
                    <td>
                        @if($user->is_active ?? true)
                            <span class="badge badge-active">Active</span>
                        @else
                            <span class="badge badge-inactive">Inactive</span>
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
                            @if($user->id !== auth()->id() && (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('users.activate')))
                                @if($user->is_active ?? true)
                                    <form action="{{ route('users.deactivate', $user) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to deactivate this user? They will not be able to login.')">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-deactivate" style="padding: 6px 12px; font-size: 12px;">Deactivate</button>
                                    </form>
                                @else
                                    <form action="{{ route('users.activate', $user) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to activate this user?')">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-activate" style="padding: 6px 12px; font-size: 12px;">Activate</button>
                                    </form>
                                @endif
                            @endif
                            @if($user->id !== auth()->id() && (auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('users.manage')))
                                <form action="{{ route('users.destroy', $user) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">Delete</button>
                                </form>
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

<div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 20px; margin-top: 30px;">
    <h3 style="color: #856404; margin-bottom: 15px;">Default Login Credentials</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 15px;">
        <div style="background: white; padding: 15px; border-radius: 6px;">
            <strong style="color: #856404;">Super Admin</strong>
            <div style="margin-top: 8px; font-size: 14px;">
                <div><strong>Email:</strong> admin@hotels.com</div>
                <div><strong>Password:</strong> admin123</div>
            </div>
        </div>
        <div style="background: white; padding: 15px; border-radius: 6px;">
            <strong style="color: #856404;">Hotel Owner</strong>
            <div style="margin-top: 8px; font-size: 14px;">
                <div><strong>Email:</strong> owner@hotels.com</div>
                <div><strong>Password:</strong> password</div>
            </div>
        </div>
    </div>
    <p style="color: #856404; margin-top: 15px; font-size: 13px;">
        <strong>Note:</strong> These are default credentials. Please change passwords after first login for security.
    </p>
</div>
@endsection


