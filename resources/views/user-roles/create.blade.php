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
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px; margin-bottom: 20px;">Assign New Role</h2>
    
    <form method="POST" action="{{ route('user-roles.store') }}">
        @csrf

        <div class="form-group">
            <label for="user_id">User *</label>
            <select id="user_id" name="user_id" required>
                <option value="">-- Select User --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                @endforeach
            </select>
            @error('user_id')
                <span style="color: #e74c3c; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
            @enderror
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
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td>
                            @foreach($user->roles as $role)
                                <span class="badge">{{ $role->name }}</span>
                            @endforeach
                        </td>
                        <td>
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
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: #999; text-align: center; padding: 40px;">No role assignments yet for this hotel</p>
    @endif
</div>
@endsection
