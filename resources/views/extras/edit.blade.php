@extends('layouts.app')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product')

@push('styles')
<style>
    .form-group {
        margin-bottom: 20px;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }
    input, select, textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
    }
    input:focus, select:focus, textarea:focus {
        outline: none;
        border-color: #667eea;
    }
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .checkbox-group input {
        width: auto;
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
    .btn-secondary {
        background: #95a5a6;
        color: white;
    }
    .error {
        color: #e74c3c;
        font-size: 13px;
        margin-top: 5px;
    }
</style>
@endpush

@section('content')
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <form method="POST" action="{{ route('extras.update', $extra) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $extra->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id', $extra->category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <span class="error">{{ $message }}</span>
                    @enderror
                    <small style="color: #666; display: block; margin-top: 5px;">
                        <a href="{{ route('extra-categories.create') }}" target="_blank">Create new category</a>
                    </small>
                </div>

                <div class="form-group">
                    <label for="price">Price *</label>
                    <input type="number" id="price" name="price" value="{{ old('price', $extra->price) }}" step="0.01" min="0" required>
                </div>

                <div class="form-group">
                    <label for="unit">Unit/Quantity *</label>
                    @php
                        $currentUnit = old('unit', $extra->unit ?? 'piece');
                        $isCustom = !in_array($currentUnit, ['piece', 'bottle', 'can', 'pack', 'box', 'kg', 'g', 'liter', 'ml', 'hour', 'session']);
                    @endphp
                    <select id="unit" name="unit" required>
                        <option value="piece" {{ $currentUnit == 'piece' && !$isCustom ? 'selected' : '' }}>Piece</option>
                        <option value="bottle" {{ $currentUnit == 'bottle' ? 'selected' : '' }}>Bottle</option>
                        <option value="can" {{ $currentUnit == 'can' ? 'selected' : '' }}>Can</option>
                        <option value="pack" {{ $currentUnit == 'pack' ? 'selected' : '' }}>Pack</option>
                        <option value="box" {{ $currentUnit == 'box' ? 'selected' : '' }}>Box</option>
                        <option value="kg" {{ $currentUnit == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                        <option value="g" {{ $currentUnit == 'g' ? 'selected' : '' }}>Gram (g)</option>
                        <option value="liter" {{ $currentUnit == 'liter' ? 'selected' : '' }}>Liter</option>
                        <option value="ml" {{ $currentUnit == 'ml' ? 'selected' : '' }}>Milliliter (ml)</option>
                        <option value="hour" {{ $currentUnit == 'hour' ? 'selected' : '' }}>Hour</option>
                        <option value="session" {{ $currentUnit == 'session' ? 'selected' : '' }}>Session</option>
                        <option value="custom" {{ $isCustom ? 'selected' : '' }}>Custom</option>
                    </select>
                    <input type="text" id="unit_custom" name="unit_custom" value="{{ $isCustom ? $currentUnit : old('unit_custom') }}" placeholder="Enter custom unit" style="margin-top: 10px; display: {{ $isCustom ? 'block' : 'none' }};">
                    @error('unit')
                        <span class="error">{{ $message }}</span>
                    @enderror
                    <small style="color: #666; display: block; margin-top: 5px;">Select the unit of measurement for this product</small>
                </div>

                <div class="form-group">
                    <label for="cost">Cost (for profit calculation)</label>
                    <input type="number" id="cost" name="cost" value="{{ old('cost', $extra->cost) }}" step="0.01" min="0">
                    <small style="color: #666;">Optional: Enter product cost to track profit margins</small>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3">{{ old('description', $extra->description) }}</textarea>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="stock_tracked" name="stock_tracked" {{ old('stock_tracked', $extra->stock_tracked) ? 'checked' : '' }}>
                        <label for="stock_tracked" style="margin: 0; font-weight: normal;">Track Stock</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="min_stock">Minimum Stock Level</label>
                    <input type="number" id="min_stock" name="min_stock" value="{{ old('min_stock', $extra->min_stock) }}" min="0">
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" {{ old('is_active', $extra->is_active) ? 'checked' : '' }}>
                        <label for="is_active" style="margin: 0; font-weight: normal;">Active</label>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">Update Product</button>
                    <a href="{{ route('extras.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
</div>

<script>
    // Handle custom unit input
    document.getElementById('unit').addEventListener('change', function() {
        const customInput = document.getElementById('unit_custom');
        if (this.value === 'custom') {
            customInput.style.display = 'block';
            customInput.required = true;
        } else {
            customInput.style.display = 'none';
            customInput.required = false;
            customInput.value = '';
        }
    });
</script>
@endsection

