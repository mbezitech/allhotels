@extends('layouts.app')

@section('title', 'Create Product')
@section('page-title', 'Create Product')

@push('styles')
<style>
    .card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
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
        display: block;
        font-weight: 500;
    }
    .error-box {
        background: #f8d7da;
        color: #721c24;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        border-left: 4px solid #dc3545;
    }
    .error-box strong {
        display: block;
        margin-bottom: 10px;
        font-size: 14px;
    }
    .error-box ul {
        margin: 0;
        padding-left: 20px;
    }
    .error-box li {
        margin-bottom: 5px;
    }
</style>
@endpush

@section('content')
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            @if($errors->any())
                <div class="error-box">
                    <strong>⚠️ Please fix the following errors:</strong>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('extras.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    @error('name')
                        <span class="error" style="display: block; color: #e74c3c; font-size: 13px; margin-top: 5px; font-weight: 500;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="category_id">Category *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">-- Select Category --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                    <input type="number" id="price" name="price" value="{{ old('price') }}" step="0.01" min="0" required>
                    @error('price')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="unit">Unit/Quantity *</label>
                    <select id="unit" name="unit" required>
                        <option value="piece" {{ old('unit', 'piece') == 'piece' ? 'selected' : '' }}>Piece</option>
                        <option value="bottle" {{ old('unit') == 'bottle' ? 'selected' : '' }}>Bottle</option>
                        <option value="can" {{ old('unit') == 'can' ? 'selected' : '' }}>Can</option>
                        <option value="pack" {{ old('unit') == 'pack' ? 'selected' : '' }}>Pack</option>
                        <option value="box" {{ old('unit') == 'box' ? 'selected' : '' }}>Box</option>
                        <option value="kg" {{ old('unit') == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                        <option value="g" {{ old('unit') == 'g' ? 'selected' : '' }}>Gram (g)</option>
                        <option value="liter" {{ old('unit') == 'liter' ? 'selected' : '' }}>Liter</option>
                        <option value="ml" {{ old('unit') == 'ml' ? 'selected' : '' }}>Milliliter (ml)</option>
                        <option value="hour" {{ old('unit') == 'hour' ? 'selected' : '' }}>Hour</option>
                        <option value="session" {{ old('unit') == 'session' ? 'selected' : '' }}>Session</option>
                        <option value="custom" {{ old('unit') == 'custom' ? 'selected' : '' }}>Custom</option>
                    </select>
                    <input type="text" id="unit_custom" name="unit_custom" value="{{ old('unit_custom') }}" placeholder="Enter custom unit" style="margin-top: 10px; display: none;">
                    @error('unit')
                        <span class="error">{{ $message }}</span>
                    @enderror
                    <small style="color: #666; display: block; margin-top: 5px;">Select the unit of measurement for this product</small>
                </div>

                <div class="form-group">
                    <label for="cost">Cost (for profit calculation)</label>
                    <input type="number" id="cost" name="cost" value="{{ old('cost') }}" step="0.01" min="0">
                    @error('cost')
                        <span class="error">{{ $message }}</span>
                    @enderror
                    <small style="color: #666;">Optional: Enter product cost to track profit margins</small>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="images">Product Images (Max 5)</label>
                    <input type="file" id="images" name="images[]" multiple accept="image/*" onchange="validateAndPreviewImages(this)">
                    <small style="color: #666; display: block; margin-top: 5px;">Upload up to 5 product images (JPEG, PNG, JPG, GIF - Max 10MB each, Total max 20MB)</small>
                    <div id="imageError" style="display: none; color: #e74c3c; margin-top: 10px; padding: 10px; background: #f8d7da; border-radius: 6px;"></div>
                    <div id="imagePreview" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; margin-top: 15px;"></div>
                    @error('images')
                        <span class="error">{{ $message }}</span>
                    @enderror
                    @error('images.*')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="stock_tracked" name="stock_tracked" {{ old('stock_tracked') ? 'checked' : '' }}>
                        <label for="stock_tracked" style="margin: 0; font-weight: normal;">Track Stock</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="min_stock">Minimum Stock Level</label>
                    <input type="number" id="min_stock" name="min_stock" value="{{ old('min_stock') }}" min="0">
                    @error('min_stock')
                        <span class="error">{{ $message }}</span>
                    @enderror
                    <small style="color: #666;">Set minimum stock level for alerts (only if stock tracking enabled)</small>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="is_active" name="is_active" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label for="is_active" style="margin: 0; font-weight: normal;">Active</label>
                    </div>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">Create Product</button>
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
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        const unitSelect = document.getElementById('unit');
        if (unitSelect.value === 'custom') {
            document.getElementById('unit_custom').style.display = 'block';
        }
    });

    function validateAndPreviewImages(input) {
        const preview = document.getElementById('imagePreview');
        const errorDiv = document.getElementById('imageError');
        preview.innerHTML = '';
        errorDiv.style.display = 'none';
        errorDiv.innerHTML = '';
        
        if (!input.files || input.files.length === 0) {
            return;
        }
        
        const maxFiles = 5;
        const maxFileSize = 10 * 1024 * 1024; // 10MB in bytes
        const maxTotalSize = 20 * 1024 * 1024; // 20MB in bytes
        const errors = [];
        let totalSize = 0;
        
        // Validate file count
        if (input.files.length > maxFiles) {
            errors.push(`Maximum ${maxFiles} images allowed. You selected ${input.files.length}.`);
            input.value = ''; // Clear the input
            return;
        }
        
        // Validate each file
        Array.from(input.files).forEach((file, index) => {
            // Check file size
            if (file.size > maxFileSize) {
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                errors.push(`${file.name} is too large (${sizeMB}MB). Maximum size is 10MB per image.`);
            }
            
            // Check file type
            if (!file.type.match('image.*')) {
                errors.push(`${file.name} is not a valid image file.`);
            }
            
            totalSize += file.size;
        });
        
        // Check total size
        if (totalSize > maxTotalSize) {
            const totalMB = (totalSize / (1024 * 1024)).toFixed(2);
            errors.push(`Total file size (${totalMB}MB) exceeds the maximum limit of 20MB.`);
        }
        
        // Show errors if any
        if (errors.length > 0) {
            errorDiv.style.display = 'block';
            errorDiv.innerHTML = '<strong>⚠️ Upload Errors:</strong><ul style="margin: 10px 0 0 20px; padding: 0;"><li>' + errors.join('</li><li>') + '</li></ul>';
            input.value = ''; // Clear the input
            return;
        }
        
        // Preview images if validation passes
        Array.from(input.files).forEach((file, index) => {
            if (index >= maxFiles) return;
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.style.position = 'relative';
                const sizeMB = (file.size / (1024 * 1024)).toFixed(2);
                div.innerHTML = `
                    <img src="${e.target.result}" style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;">
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); color: white; padding: 4px; font-size: 11px; text-align: center;">${sizeMB}MB</div>
                    <button type="button" onclick="removeImage(${index})" style="position: absolute; top: 5px; right: 5px; background: #e74c3c; color: white; border: none; border-radius: 50%; width: 25px; height: 25px; cursor: pointer; font-size: 14px;">×</button>
                `;
                preview.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    }
    
    function previewImages(input) {
        validateAndPreviewImages(input);
    }
    
    function removeImage(index) {
        const input = document.getElementById('images');
        const dt = new DataTransfer();
        Array.from(input.files).forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });
        input.files = dt.files;
        previewImages(input);
    }
</script>
@endsection

