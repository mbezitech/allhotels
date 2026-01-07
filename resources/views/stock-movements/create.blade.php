<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Stock Movement - Hotel Management</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
        }
        .header {
            background: white;
            padding: 20px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 30px;
        }
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
</head>
<body>
    <div class="header">
        <h1>Add Stock Movement</h1>
    </div>

    <div class="container">
        <div class="card">
            <form method="POST" action="{{ route('stock-movements.store') }}">
                @csrf

                <div class="form-group">
                    <label for="product_id">Product *</label>
                    <select id="product_id" name="product_id" required>
                        <option value="">-- Select Product --</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} ({{ $product->category }})
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="type">Movement Type *</label>
                    <select id="type" name="type" required>
                        <option value="in" {{ old('type') == 'in' ? 'selected' : '' }}>Stock In (Add)</option>
                        <option value="out" {{ old('type') == 'out' ? 'selected' : '' }}>Stock Out (Remove)</option>
                    </select>
                    @error('type')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity *</label>
                    <input type="number" id="quantity" name="quantity" value="{{ old('quantity') }}" required min="1">
                    @error('quantity')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">Record Movement</button>
                    <a href="{{ route('stock-movements.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

