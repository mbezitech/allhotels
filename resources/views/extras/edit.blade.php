<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Extra - Hotel Management</title>
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
</head>
<body>
    <div class="header">
        <h1>Edit Extra: {{ $extra->name }}</h1>
    </div>

    <div class="container">
        <div class="card">
            <form method="POST" action="{{ route('extras.update', $extra) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Name *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $extra->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        <option value="bar" {{ old('category', $extra->category) == 'bar' ? 'selected' : '' }}>Bar</option>
                        <option value="pool" {{ old('category', $extra->category) == 'pool' ? 'selected' : '' }}>Pool</option>
                        <option value="restaurant" {{ old('category', $extra->category) == 'restaurant' ? 'selected' : '' }}>Restaurant</option>
                        <option value="general" {{ old('category', $extra->category) == 'general' ? 'selected' : '' }}>General</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="price">Price *</label>
                    <input type="number" id="price" name="price" value="{{ old('price', $extra->price) }}" step="0.01" min="0" required>
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
                    <button type="submit" class="btn btn-primary">Update Extra</button>
                    <a href="{{ route('extras.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

