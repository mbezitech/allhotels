@extends('layouts.app')

@section('title', 'Expense Categories')
@section('page-title', 'Expense Categories')

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
    <h2 style="color: #333; font-size: 24px;">Expense Categories</h2>
    @if(auth()->user()->hasPermission('expense_categories.manage', session('hotel_id')) || auth()->user()->isSuperAdmin())
        <a href="{{ route('expense-categories.create') }}" class="btn btn-primary">Add Category</a>
    @endif
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif

@if(isset($isSuperAdmin) && $isSuperAdmin && isset($hotels) && $hotels->count() > 0)
    <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
        <form method="GET" action="{{ route('expense-categories.index') }}" style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Filter by Hotel:</label>
                <select name="hotel_id" onchange="this.form.submit()" style="padding: 8px 16px; border: 2px solid #667eea; border-radius: 6px; background: white; cursor: pointer; min-width: 200px;">
                    <option value="">All Hotels</option>
                    @foreach($hotels as $h)
                        <option value="{{ $h->id }}" {{ request('hotel_id') == $h->id ? 'selected' : '' }}>
                            {{ $h->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Status:</label>
                <select name="is_active" onchange="this.form.submit()" style="padding: 8px 16px; border: 2px solid #e0e0e0; border-radius: 6px; background: white; cursor: pointer;">
                    <option value="">All</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            @if(request()->anyFilled(['hotel_id', 'is_active']))
                <div>
                    <a href="{{ route('expense-categories.index') }}" style="padding: 8px 16px; background: #95a5a6; color: white; border-radius: 6px; text-decoration: none; font-size: 14px;">
                        Clear Filter
                    </a>
                </div>
            @endif
        </form>
    </div>
@endif

<div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    @if($categories->count() > 0)
        <table>
            <thead>
                <tr>
                    @if(isset($isSuperAdmin) && $isSuperAdmin)
                        <th>Hotel</th>
                    @endif
                    <th>Name</th>
                    <th>Description</th>
                    <th>Expenses Count</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                    <tr>
                        @if(isset($isSuperAdmin) && $isSuperAdmin)
                            <td>{{ $category->hotel->name ?? 'N/A' }}</td>
                        @endif
                        <td><strong>{{ $category->name }}</strong></td>
                        <td>{{ Str::limit($category->description ?? '—', 50) }}</td>
                        <td>{{ $category->expenses_count ?? 0 }}</td>
                        <td>
                            <span class="badge badge-{{ $category->is_active ? 'active' : 'inactive' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                @if(auth()->user()->hasPermission('expense_categories.edit', session('hotel_id')) || auth()->user()->isSuperAdmin())
                                    <a href="{{ route('expense-categories.edit', $category) }}" class="btn btn-edit" style="padding: 6px 12px; font-size: 12px;">Edit</a>
                                @endif
                                @if(auth()->user()->hasPermission('expense_categories.manage', session('hotel_id')) || auth()->user()->isSuperAdmin())
                                    @if($category->expenses_count == 0)
                                        <form action="{{ route('expense-categories.destroy', $category) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this category?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">Delete</button>
                                        </form>
                                    @else
                                        <span style="color: #999; font-size: 12px; padding: 6px 12px;">Cannot delete (has expenses)</span>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: #999; text-align: center; padding: 40px;">No expense categories found.</p>
    @endif
</div>
@endsection
