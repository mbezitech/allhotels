@extends('layouts.app')

@section('title', 'Stock Movements')
@section('page-title', 'Stock Movements')

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
    .badge-in { background: #d4edda; color: #155724; }
    .badge-out { background: #f8d7da; color: #721c24; }
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
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
    <h2 style="color: #333; font-size: 24px;">All Stock Movements</h2>
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('stock-movements.balance') }}" class="btn" style="background: #3498db; color: white;">Stock Balance</a>
        @if(auth()->user()->hasPermission('stock.manage') || auth()->user()->isSuperAdmin())
            <a href="{{ route('stock-movements.create') }}" class="btn btn-primary">Add Movement</a>
        @endif
    </div>
</div>

<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Reference</th>
                        <th>Created By</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td>{{ $movement->created_at->format('M d, Y H:i') }}</td>
                            <td><strong>{{ $movement->product->name }}</strong></td>
                            <td>
                                <span class="badge badge-{{ $movement->type }}">
                                    {{ strtoupper($movement->type) }}
                                </span>
                            </td>
                            <td>{{ $movement->quantity }}</td>
                            <td>
                                @if($movement->reference_type)
                                    {{ class_basename($movement->reference_type) }}
                                    @if($movement->reference_id)
                                        #{{ $movement->reference_id }}
                                    @endif
                                @else
                                    Manual
                                @endif
                            </td>
                            <td>{{ $movement->creator->name }}</td>
                            <td>{{ $movement->notes ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #999; padding: 40px;">No stock movements found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

    <div style="margin-top: 20px;">
        {{ $movements->links() }}
    </div>
</div>
@endsection

