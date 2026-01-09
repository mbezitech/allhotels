@extends('layouts.app')

@section('title', 'Extras')
@section('page-title', 'Extras')

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
    .category-badge {
        background: #e0e0e0;
        color: #333;
        padding: 2px 6px;
        border-radius: 3px;
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
    .alert-error {
        background: #f8d7da;
        color: #721c24;
    }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">All Extras</h2>
    @if(auth()->user()->hasPermission('stock.manage') || auth()->user()->isSuperAdmin())
        <a href="{{ route('extras.create') }}" class="btn btn-primary">Add Extra</a>
    @endif
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

    <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock Tracked</th>
                        <th>Current Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($extras as $extra)
                        <tr>
                            <td><strong>{{ $extra->name }}</strong></td>
                            <td>
                                @if($extra->category)
                                    <span class="category-badge">{{ $extra->category->name }}</span>
                                @else
                                    <span class="category-badge" style="background: #f8d7da; color: #721c24;">No Category</span>
                                @endif
                            </td>
                            <td>${{ number_format($extra->price, 2) }}</td>
                            <td>{{ $extra->stock_tracked ? 'Yes' : 'No' }}</td>
                            <td>
                                @if($extra->stock_tracked)
                                    @php
                                        $stock = $extra->getStockBalance();
                                        $isLow = $extra->isLowStock();
                                    @endphp
                                    <strong>{{ $stock }}</strong>
                                    @if($isLow)
                                        <span class="badge" style="background: #fff3cd; color: #856404; margin-left: 5px;">Low</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $extra->is_active ? 'active' : 'inactive' }}">
                                    {{ $extra->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('extras.edit', $extra) }}" class="btn btn-edit">Edit</a>
                                <form action="{{ route('extras.destroy', $extra) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #999; padding: 40px;">No extras found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
</div>
@endsection

