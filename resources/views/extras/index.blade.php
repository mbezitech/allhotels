@extends('layouts.app')

@section('title', 'Products')
@section('page-title', 'Products')

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
    <h2 style="color: #333; font-size: 24px;">All Products</h2>
    @if(auth()->user()->hasPermission('stock.manage') || auth()->user()->isSuperAdmin())
        <a href="{{ route('extras.create') }}" class="btn btn-primary">Add Product</a>
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
                        <th>Unit</th>
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
                                @if($extra->category && is_object($extra->category))
                                    <span class="category-badge">{{ $extra->category->name }}</span>
                                @else
                                    <span class="category-badge" style="background: #f8d7da; color: #721c24;">No Category</span>
                                @endif
                            </td>
                            <td>${{ number_format($extra->price, 2) }}</td>
                            <td>
                                <span style="color: #666; font-size: 13px;">{{ $extra->unit ?? 'piece' }}</span>
                            </td>
                            <td>{{ $extra->stock_tracked ? 'Yes' : 'No' }}</td>
                            <td>
                                @if($extra->stock_tracked)
                                    @php
                                        $stock = $extra->current_stock ?? $extra->getStockBalance();
                                        $isLow = $extra->is_low_stock ?? $extra->isLowStock();
                                        $minStock = $extra->min_stock ?? 0;
                                    @endphp
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <strong style="font-size: 16px; color: {{ $isLow ? '#dc3545' : ($stock > 0 ? '#28a745' : '#6c757d') }};">
                                            {{ $stock }} {{ $extra->unit ?? 'piece' }}
                                        </strong>
                                        @if($isLow)
                                            <span class="badge" style="background: #fff3cd; color: #856404; font-weight: 600;">⚠️ Low Stock</span>
                                        @elseif($stock == 0)
                                            <span class="badge" style="background: #f8d7da; color: #721c24; font-weight: 600;">Out of Stock</span>
                                        @else
                                            <span class="badge" style="background: #d4edda; color: #155724; font-weight: 600;">In Stock</span>
                                        @endif
                                    </div>
                                    @if($minStock > 0)
                                        <div style="font-size: 11px; color: #666; margin-top: 3px;">
                                            Min: {{ $minStock }} {{ $extra->unit ?? 'piece' }}
                                        </div>
                                    @endif
                                @else
                                    <span style="color: #999; font-style: italic;">Not Tracked</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $extra->is_active ? 'active' : 'inactive' }}">
                                    {{ $extra->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    @if($extra->stock_tracked && (auth()->user()->hasPermission('stock.manage') || auth()->user()->isSuperAdmin()))
                                        <button onclick="showAddStockModal({{ $extra->id }}, {{ json_encode($extra->name) }}, {{ $extra->current_stock ?? $extra->getStockBalance() }}, {{ json_encode($extra->unit ?? 'piece') }})" 
                                                class="btn" 
                                                style="background: #28a745; color: white; padding: 6px 12px; font-size: 12px;">
                                            Add Stock
                                        </button>
                                    @endif
                                    <a href="{{ route('extras.edit', $extra) }}" class="btn btn-edit" style="padding: 6px 12px; font-size: 12px;">Edit</a>
                                    @if(auth()->user()->hasPermission('stock.manage') || auth()->user()->isSuperAdmin())
                                        <form action="{{ route('extras.destroy', $extra) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?')">
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
                            <td colspan="8" style="text-align: center; color: #999; padding: 40px;">No products found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
</div>

<!-- Add Stock Modal -->
<div id="addStockModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; padding: 30px; max-width: 500px; width: 90%;">
        <h3 style="color: #333; font-size: 20px; margin-bottom: 20px;">Add Stock</h3>
        <form id="addStockForm" method="POST" action="{{ route('stock-movements.store') }}">
            @csrf
            <input type="hidden" id="stock_product_id" name="product_id" value="">
            <input type="hidden" name="type" value="in">
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Product</label>
                <div style="padding: 12px; background: #f8f9fa; border-radius: 8px; font-weight: 600;" id="stock_product_name"></div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">Current Stock</label>
                <div style="padding: 12px; background: #f8f9fa; border-radius: 8px;" id="stock_current_stock"></div>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="stock_quantity" style="display: block; margin-bottom: 8px; font-weight: 500;">Quantity to Add *</label>
                <input type="number" id="stock_quantity" name="quantity" value="1" min="1" required style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;">
                <small style="color: #666; display: block; margin-top: 5px;" id="stock_unit_display"></small>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label for="stock_notes" style="display: block; margin-bottom: 8px; font-weight: 500;">Notes (Optional)</label>
                <textarea id="stock_notes" name="notes" rows="3" placeholder="e.g., New shipment, Restocked, etc." style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;"></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" onclick="closeAddStockModal()" style="padding: 10px 20px; background: #95a5a6; color: white; border: none; border-radius: 6px; cursor: pointer;">Cancel</button>
                <button type="submit" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer;">Add Stock</button>
            </div>
        </form>
    </div>
</div>

<script>
    function showAddStockModal(productId, productName, currentStock, unit) {
        document.getElementById('stock_product_id').value = productId;
        document.getElementById('stock_product_name').textContent = productName;
        document.getElementById('stock_current_stock').textContent = currentStock + ' ' + unit;
        document.getElementById('stock_unit_display').textContent = 'Unit: ' + unit;
        document.getElementById('stock_quantity').value = 1;
        document.getElementById('stock_notes').value = '';
        document.getElementById('addStockModal').style.display = 'flex';
    }

    function closeAddStockModal() {
        document.getElementById('addStockModal').style.display = 'none';
    }

    // Close modal when clicking outside
    document.getElementById('addStockModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAddStockModal();
        }
    });
</script>
@endsection

