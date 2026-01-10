@extends('layouts.app')

@section('title', 'Extra Categories')
@section('page-title', 'Extra Categories')

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
    .badge-inactive { background: #f8d7da; color: #721c24; }
    .alert {
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .alert-success {
        background: #d4edda;
        color: #155724;
    }
    .alert-error {
        background: #f8d7da;
        color: #721c24;
    }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">All Extra Categories</h2>
    @if(auth()->user()->hasPermission('stock.manage') || auth()->user()->isSuperAdmin())
        <a href="{{ route('extra-categories.create') }}" class="btn btn-primary">Create Category</a>
    @endif
</div>

@if(isset($isSuperAdmin) && $isSuperAdmin && isset($hotels) && $hotels->count() > 0)
    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('extra-categories.index') }}" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
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
                <a href="{{ route('extra-categories.index') }}" style="padding: 8px 16px; background: #95a5a6; color: white; border-radius: 6px; text-decoration: none; font-size: 14px;">
                    Clear Filter
                </a>
            @endif
        </form>
    </div>
@endif

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    <table>
        <thead>
            <tr>
                @if(isset($isSuperAdmin) && $isSuperAdmin)
                <th>Hotel</th>
                @endif
                <th>Name</th>
                <th>Description</th>
                <th>Extras Count</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
                <tr>
                    @if(isset($isSuperAdmin) && $isSuperAdmin)
                    <td>
                        <strong style="color: #667eea;">{{ $category->hotel->name ?? 'Unknown Hotel' }}</strong>
                        @if($category->hotel && $category->hotel->address)
                            <div style="font-size: 11px; color: #999; margin-top: 2px;">{{ \Illuminate\Support\Str::limit($category->hotel->address, 30) }}</div>
                        @endif
                    </td>
                    @endif
                    <td><strong>{{ $category->name }}</strong></td>
                    <td>{{ $category->description ?? '-' }}</td>
                    <td>{{ $category->extras_count ?? $category->extras()->count() }}</td>
                    <td>
                        <span class="badge badge-{{ $category->is_active ? 'active' : 'inactive' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('extra-categories.edit', $category) }}" class="btn btn-edit">Edit</a>
                        <form action="{{ route('extra-categories.destroy', $category) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure? This will fail if any extras are using this category.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ (isset($isSuperAdmin) && $isSuperAdmin) ? '6' : '5' }}" style="text-align: center; color: #999; padding: 40px;">No categories found. Create your first category to get started.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection

