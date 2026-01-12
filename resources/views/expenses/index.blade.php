@extends('layouts.app')

@section('title', 'Expenses')
@section('page-title', 'Expenses')

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
    .badge-cash { background: #d4edda; color: #155724; }
    .badge-bank { background: #cfe2ff; color: #084298; }
    .badge-mobile { background: #fff3cd; color: #856404; }
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
    .filter-form {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="color: #333; font-size: 24px;">All Expenses</h2>
    <div style="display: flex; gap: 10px;">
        @if(auth()->user()->hasPermission('expenses.create', session('hotel_id')) || auth()->user()->isSuperAdmin())
            <a href="{{ route('expenses.create') }}" class="btn btn-primary">Add Expense</a>
        @endif
        @if(auth()->user()->hasPermission('expense_reports.view', session('hotel_id')) || auth()->user()->isSuperAdmin())
            <a href="{{ route('expense-reports.index') }}" class="btn" style="background: #28a745; color: white;">View Reports</a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-error">{{ session('error') }}</div>
@endif

@if(isset($isSuperAdmin) && $isSuperAdmin && isset($hotels) && $hotels->count() > 0)
    <div class="filter-form">
        <form method="GET" action="{{ route('expenses.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Filter by Hotel:</label>
                <select name="hotel_id" onchange="this.form.submit()" style="width: 100%; padding: 8px; border: 2px solid #667eea; border-radius: 6px; background: white; cursor: pointer;">
                    <option value="">All Hotels</option>
                    @foreach($hotels as $h)
                        <option value="{{ $h->id }}" {{ request('hotel_id') == $h->id ? 'selected' : '' }}>
                            {{ $h->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Category:</label>
                <select name="category_id" style="width: 100%; padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px;">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Payment Method:</label>
                <select name="payment_method" style="width: 100%; padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px;">
                    <option value="">All Methods</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bank" {{ request('payment_method') == 'bank' ? 'selected' : '' }}>Bank</option>
                    <option value="mobile" {{ request('payment_method') == 'mobile' ? 'selected' : '' }}>Mobile</option>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Date From:</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" style="width: 100%; padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Date To:</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" style="width: 100%; padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Search:</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search description..." style="width: 100%; padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
            <div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Filter</button>
            </div>
            @if(request()->anyFilled(['hotel_id', 'category_id', 'payment_method', 'date_from', 'date_to', 'search']))
                <div>
                    <a href="{{ route('expenses.index') }}" class="btn" style="background: #95a5a6; color: white; width: 100%;">Clear</a>
                </div>
            @endif
        </form>
    </div>
@else
    <div class="filter-form">
        <form method="GET" action="{{ route('expenses.index') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; align-items: end;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Category:</label>
                <select name="category_id" style="width: 100%; padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px;">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Payment Method:</label>
                <select name="payment_method" style="width: 100%; padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px;">
                    <option value="">All Methods</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bank" {{ request('payment_method') == 'bank' ? 'selected' : '' }}>Bank</option>
                    <option value="mobile" {{ request('payment_method') == 'mobile' ? 'selected' : '' }}>Mobile</option>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Date From:</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" style="width: 100%; padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Date To:</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" style="width: 100%; padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 13px;">Search:</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search description..." style="width: 100%; padding: 8px; border: 2px solid #e0e0e0; border-radius: 6px;">
            </div>
            <div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Filter</button>
            </div>
            @if(request()->anyFilled(['category_id', 'payment_method', 'date_from', 'date_to', 'search']))
                <div>
                    <a href="{{ route('expenses.index') }}" class="btn" style="background: #95a5a6; color: white; width: 100%;">Clear</a>
                </div>
            @endif
        </form>
    </div>
@endif

<div style="background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    @if($expenses->count() > 0)
        <table>
            <thead>
                <tr>
                    @if(isset($isSuperAdmin) && $isSuperAdmin)
                        <th>Hotel</th>
                    @endif
                    <th>Date</th>
                    <th>Category</th>
                    <th>Description</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Added By</th>
                    <th>Receipt</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($expenses as $expense)
                    <tr>
                        @if(isset($isSuperAdmin) && $isSuperAdmin)
                            <td>{{ $expense->hotel->name ?? 'N/A' }}</td>
                        @endif
                        <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                        <td>{{ $expense->category->name ?? 'N/A' }}</td>
                        <td>{{ Str::limit($expense->description, 50) }}</td>
                        <td style="font-weight: 600; color: #e74c3c;">${{ number_format($expense->amount, 2) }}</td>
                        <td>
                            <span class="badge badge-{{ $expense->payment_method }}">
                                {{ ucfirst($expense->payment_method) }}
                            </span>
                        </td>
                        <td>{{ $expense->addedBy->name ?? 'N/A' }}</td>
                        <td>
                            @if($expense->attachment)
                                <a href="{{ asset('storage/' . $expense->attachment) }}" target="_blank" style="color: #667eea; text-decoration: none;">📎 View</a>
                            @else
                                <span style="color: #999;">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                <a href="{{ route('expenses.show', $expense) }}" class="btn" style="background: #3498db; color: white; padding: 6px 12px; font-size: 12px;">View</a>
                                @if(auth()->user()->hasPermission('expenses.edit', session('hotel_id')) || auth()->user()->isSuperAdmin())
                                    <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-edit" style="padding: 6px 12px; font-size: 12px;">Edit</a>
                                @endif
                                @if(auth()->user()->hasPermission('expenses.delete', session('hotel_id')) || auth()->user()->isSuperAdmin())
                                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this expense?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 12px;">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #eee;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong>Total Expenses:</strong> <span style="color: #e74c3c; font-size: 18px; font-weight: 600;">${{ number_format($expenses->sum('amount'), 2) }}</span>
                </div>
                <div>
                    {{ $expenses->links() }}
                </div>
            </div>
        </div>
    @else
        <p style="color: #999; text-align: center; padding: 40px;">No expenses found.</p>
    @endif
</div>
@endsection
