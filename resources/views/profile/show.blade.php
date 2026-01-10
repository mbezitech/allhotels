@extends('layouts.app')

@section('title', 'My Account')
@section('page-title', 'My Account')

@push('styles')
<style>
    .card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .info-row {
        display: flex;
        padding: 15px 0;
        border-bottom: 1px solid #eee;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-weight: 600;
        color: #666;
        width: 150px;
        flex-shrink: 0;
    }
    .info-value {
        color: #333;
        flex: 1;
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
    .badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        margin: 4px 4px 4px 0;
    }
    .badge-super-admin {
        background: #f8d7da;
        color: #721c24;
    }
    .badge-role {
        background: #667eea;
        color: white;
    }
    .badge-hotel {
        background: #d4edda;
        color: #155724;
        font-size: 11px;
    }
    .alert {
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .alert-success {
        background: #d4edda;
        color: #155724;
    }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">My Account</h2>
    <a href="{{ route('profile.edit') }}" class="btn btn-primary">Edit Profile</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <h3 style="color: #333; font-size: 20px; margin-bottom: 20px;">Personal Information</h3>
    
    <div class="info-row">
        <div class="info-label">Name:</div>
        <div class="info-value"><strong>{{ $user->name }}</strong></div>
    </div>
    
    <div class="info-row">
        <div class="info-label">Email:</div>
        <div class="info-value">{{ $user->email }}</div>
    </div>
    
    <div class="info-row">
        <div class="info-label">Phone:</div>
        <div class="info-value">{{ $user->phone ?? '<span style="color: #999;">Not set</span>' }}</div>
    </div>
    
    <div class="info-row">
        <div class="info-label">Account Type:</div>
        <div class="info-value">
            @if($user->is_super_admin)
                <span class="badge badge-super-admin">Super Admin</span>
            @else
                <span style="color: #666;">Regular User</span>
            @endif
        </div>
    </div>
    
    <div class="info-row">
        <div class="info-label">Member Since:</div>
        <div class="info-value">{{ $user->created_at->format('F d, Y') }}</div>
    </div>
</div>

<div class="card">
    <h3 style="color: #333; font-size: 20px; margin-bottom: 20px;">My Roles & Permissions</h3>
    
    @if($rolesWithHotels->count() > 0)
        <div style="margin-bottom: 15px;">
            <strong style="color: #666;">Current Hotel:</strong>
            @if(session('hotel_id'))
                @php
                    $currentHotel = \App\Models\Hotel::find(session('hotel_id'));
                @endphp
                @if($currentHotel)
                    <span style="color: #667eea; font-weight: 600;">{{ $currentHotel->name }}</span>
                @else
                    <span style="color: #999;">No hotel selected</span>
                @endif
            @else
                <span style="color: #999;">No hotel selected</span>
            @endif
        </div>
        
        <div style="margin-top: 20px;">
            @foreach($rolesWithHotels as $item)
                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px;">
                    <div style="display: flex; align-items: center; margin-bottom: 8px;">
                        <span class="badge badge-role">{{ $item['role']->name }}</span>
                        @if($item['hotel'])
                            <span class="badge badge-hotel">{{ $item['hotel']->name }}</span>
                        @endif
                    </div>
                    @if($item['role']->description)
                        <div style="color: #666; font-size: 13px; margin-top: 5px;">
                            {{ $item['role']->description }}
                        </div>
                    @endif
                    @php
                        $permissions = $item['role']->permissions;
                    @endphp
                    @if($permissions->count() > 0)
                        <div style="margin-top: 10px;">
                            <strong style="color: #666; font-size: 12px;">Permissions:</strong>
                            <div style="margin-top: 5px;">
                                @foreach($permissions as $permission)
                                    <span style="display: inline-block; padding: 3px 8px; background: #e9ecef; color: #495057; border-radius: 4px; font-size: 11px; margin: 2px;">
                                        {{ $permission->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <p style="color: #999; text-align: center; padding: 20px;">
            @if($user->is_super_admin)
                You are a super admin with full system access.
            @else
                No roles assigned yet. Contact your administrator to assign roles.
            @endif
        </p>
    @endif
</div>

@if($user->ownedHotels->count() > 0)
<div class="card">
    <h3 style="color: #333; font-size: 20px; margin-bottom: 20px;">Owned Hotels</h3>
    <div>
        @foreach($user->ownedHotels as $hotel)
            <div style="padding: 10px; background: #f8f9fa; border-radius: 6px; margin-bottom: 8px;">
                <strong>{{ $hotel->name }}</strong>
                @if($hotel->address)
                    <div style="color: #666; font-size: 13px; margin-top: 3px;">{{ $hotel->address }}</div>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endif
@endsection
