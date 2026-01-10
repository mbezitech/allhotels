@extends('layouts.app')

@section('title', 'User Details')
@section('page-title', 'User Details')

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
    .info-label {
        font-weight: 600;
        width: 200px;
        color: #666;
    }
    .info-value {
        flex: 1;
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
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-size: 14px;
        margin-right: 10px;
    }
    .btn-primary {
        background: #667eea;
        color: white;
    }
    .btn-secondary {
        background: #95a5a6;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h2 style="color: #333; font-size: 24px;">User Information</h2>
        <div>
            <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">Edit User</a>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Back to List</a>
        </div>
    </div>

    <div class="info-row">
        <div class="info-label">Name</div>
        <div class="info-value"><strong>{{ $user->name }}</strong></div>
    </div>

    <div class="info-row">
        <div class="info-label">Email</div>
        <div class="info-value">{{ $user->email }}</div>
    </div>

    <div class="info-row">
        <div class="info-label">User Type</div>
        <div class="info-value">
            @if($user->is_super_admin)
                <span class="badge badge-super-admin">Super Admin</span>
            @elseif($user->ownedHotels->count() > 0)
                <span class="badge badge-owner">Owner</span>
            @else
                <span style="color: #666;">Regular User</span>
            @endif
        </div>
    </div>

    <div class="info-row">
        <div class="info-label">Owned Hotels</div>
        <div class="info-value">
            @if($user->ownedHotels->count() > 0)
                @foreach($user->ownedHotels as $hotel)
                    <span style="background: #e3f2fd; color: #1976d2; padding: 4px 8px; border-radius: 4px; font-size: 12px; margin-right: 5px;">
                        {{ $hotel->name }}
                    </span>
                @endforeach
            @else
                <span style="color: #999;">None</span>
            @endif
        </div>
    </div>

    <div class="info-row">
        <div class="info-label">Created At</div>
        <div class="info-value">{{ $user->created_at->format('F d, Y H:i:s') }}</div>
    </div>

    <div class="info-row" style="border-bottom: none;">
        <div class="info-label">Last Updated</div>
        <div class="info-value">{{ $user->updated_at->format('F d, Y H:i:s') }}</div>
    </div>
</div>
@endsection


