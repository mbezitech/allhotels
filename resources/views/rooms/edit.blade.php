@extends('layouts.app')

@section('title', 'Edit Room')
@section('page-title', 'Edit Room')

@push('styles')
<style>
    .form-group {
        margin-bottom: 20px;
    }
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }
    input, select, textarea {
        width: 100%;
        padding: 12px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.3s;
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
        display: block;
    }
    .grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    @media (max-width: 768px) {
        .grid-2 {
            grid-template-columns: 1fr;
        }
    }
    .image-preview {
        position: relative;
        width: 100%;
    }
    .image-preview img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e0e0e0;
    }
    .remove-image-btn {
        position: absolute;
        top: 5px;
        right: 5px;
        background: #e74c3c;
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        cursor: pointer;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }
    .remove-image-btn:hover {
        background: #c0392b;
    }
</style>
@endpush

@push('scripts')
<script>
    // Handle removal of existing images
    document.querySelectorAll('.remove-image-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const preview = this.closest('.image-preview');
            preview.remove();
        });
    });

    // Handle new image previews
    document.getElementById('images').addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        
        Array.from(e.target.files).forEach((file, index) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'image-preview';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Preview">
                    `;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>
@endpush

@section('content')
<div style="background: white; border-radius: 12px; padding: 30px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
    <form method="POST" action="{{ route('rooms.update', $room) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="room_number">Room Number *</label>
            <input type="text" id="room_number" name="room_number" value="{{ old('room_number', $room->room_number) }}" required>
            @error('room_number')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="room_type_id">Room Type *</label>
            <select id="room_type_id" name="room_type_id" required>
                <option value="">-- Select Room Type --</option>
                @if(isset($roomTypes) && $roomTypes->count() > 0)
                    @foreach($roomTypes as $roomType)
                        <option value="{{ $roomType->id }}" {{ old('room_type_id', $room->room_type_id) == $roomType->id ? 'selected' : '' }}>
                            {{ $roomType->name }} (${{ number_format($roomType->base_price, 2) }}/night)
                        </option>
                    @endforeach
                @endif
            </select>
            @error('room_type_id')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="status">Status *</label>
            <select id="status" name="status" required>
                <option value="available" {{ old('status', $room->status) == 'available' ? 'selected' : '' }}>Available</option>
                <option value="occupied" {{ old('status', $room->status) == 'occupied' ? 'selected' : '' }}>Occupied</option>
                <option value="maintenance" {{ old('status', $room->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="cleaning" {{ old('status', $room->status) == 'cleaning' ? 'selected' : '' }}>Cleaning</option>
            </select>
        </div>

        <div class="grid-2">
            <div class="form-group">
                <label for="floor">Floor</label>
                <input type="number" id="floor" name="floor" value="{{ old('floor', $room->floor) }}" min="1">
            </div>

            <div class="form-group">
                <label for="capacity">Capacity *</label>
                <input type="number" id="capacity" name="capacity" value="{{ old('capacity', $room->capacity) }}" required min="1">
            </div>
        </div>

        <div class="form-group">
            <label for="price_per_night">Price Per Night *</label>
            <input type="number" id="price_per_night" name="price_per_night" value="{{ old('price_per_night', $room->price_per_night) }}" step="0.01" min="0" required>
            @error('price_per_night')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3">{{ old('description', $room->description) }}</textarea>
        </div>

        <div class="form-group">
            <label>Existing Images</label>
            @if($room->images && count($room->images) > 0)
                <div id="existingImages" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; margin-bottom: 15px;">
                    @foreach($room->images as $index => $image)
                        <div class="image-preview" style="position: relative;">
                            <img src="{{ $image }}" alt="Room Image" style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;">
                            <button type="button" class="remove-image-btn" data-index="{{ $index }}" style="position: absolute; top: 5px; right: 5px; background: #e74c3c; color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer;">×</button>
                            <input type="hidden" name="existing_images[]" value="{{ $image }}">
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color: #999; margin-bottom: 15px;">No images uploaded yet.</p>
            @endif
        </div>

        <div class="form-group">
            <label for="images">Add New Images</label>
            <input type="file" id="images" name="images[]" accept="image/*" multiple>
            <small style="color: #666; display: block; margin-top: 5px;">
                You can upload multiple images. Supported formats: JPEG, PNG, JPG, GIF. Max size: 5MB per image.
            </small>
            @error('images.*')
                <span class="error">{{ $message }}</span>
            @enderror
            <div id="imagePreview" style="margin-top: 15px; display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px;"></div>
        </div>

        <div style="margin-top: 30px; display: flex; gap: 10px;">
            <button type="submit" class="btn btn-primary">Update Room</button>
            <a href="{{ route('rooms.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection
