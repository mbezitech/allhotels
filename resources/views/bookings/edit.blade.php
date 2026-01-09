<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking - Hotel Management</title>
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
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
    </style>
    <script>
        function toggleCancellationReason() {
            const status = document.getElementById('status').value;
            const reasonGroup = document.getElementById('cancellation_reason_group');
            const reasonField = document.getElementById('cancellation_reason');
            
            if (status === 'cancelled') {
                reasonGroup.style.display = 'block';
                reasonField.required = true;
            } else {
                reasonGroup.style.display = 'none';
                reasonField.required = false;
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleCancellationReason();
        });
    </script>
</head>
<body>
    <div class="header">
        <h1>Edit Booking</h1>
    </div>

    <div class="container">
        <div class="card">
            <form method="POST" action="{{ route('bookings.update', $booking) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="room_id">Room *</label>
                    <select id="room_id" name="room_id" required>
                        <option value="">-- Select Room --</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" {{ old('room_id', $booking->room_id) == $room->id ? 'selected' : '' }}>
                                {{ $room->room_number }} - {{ $room->room_type }}
                            </option>
                        @endforeach
                    </select>
                    @error('room_id')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="guest_name">Guest Name *</label>
                    <input type="text" id="guest_name" name="guest_name" value="{{ old('guest_name', $booking->guest_name) }}" required>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label for="guest_email">Guest Email</label>
                        <input type="email" id="guest_email" name="guest_email" value="{{ old('guest_email', $booking->guest_email) }}">
                    </div>

                    <div class="form-group">
                        <label for="guest_phone">Guest Phone</label>
                        <input type="text" id="guest_phone" name="guest_phone" value="{{ old('guest_phone', $booking->guest_phone) }}">
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label for="check_in">Check In *</label>
                        <input type="date" id="check_in" name="check_in" value="{{ old('check_in', $booking->check_in->format('Y-m-d')) }}" required>
                    </div>

                    <div class="form-group">
                        <label for="check_out">Check Out *</label>
                        <input type="date" id="check_out" name="check_out" value="{{ old('check_out', $booking->check_out->format('Y-m-d')) }}" required>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label for="adults">Adults *</label>
                        <input type="number" id="adults" name="adults" value="{{ old('adults', $booking->adults) }}" required min="1">
                    </div>

                    <div class="form-group">
                        <label for="children">Children</label>
                        <input type="number" id="children" name="children" value="{{ old('children', $booking->children) }}" min="0">
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required onchange="toggleCancellationReason()">
                        <option value="pending" {{ old('status', $booking->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ old('status', $booking->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="checked_in" {{ old('status', $booking->status) == 'checked_in' ? 'selected' : '' }}>Checked In</option>
                        <option value="checked_out" {{ old('status', $booking->status) == 'checked_out' ? 'selected' : '' }}>Checked Out</option>
                        <option value="cancelled" {{ old('status', $booking->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="form-group" id="cancellation_reason_group" style="display: {{ old('status', $booking->status) == 'cancelled' ? 'block' : 'none' }};">
                    <label for="cancellation_reason">Cancellation Reason *</label>
                    <textarea id="cancellation_reason" name="cancellation_reason" rows="3" placeholder="Please provide a reason for cancellation...">{{ old('cancellation_reason', $booking->cancellation_reason) }}</textarea>
                    <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">
                        @if($booking->cancellation_reason && str_starts_with($booking->cancellation_reason, 'System:'))
                            Current reason: {{ $booking->cancellation_reason }} (System cancelled)
                        @elseif($booking->cancellation_reason)
                            Current reason: {{ $booking->cancellation_reason }}
                        @else
                            Required when cancelling a booking
                        @endif
                    </small>
                    @error('cancellation_reason')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="total_amount">Total Amount *</label>
                    <input type="number" id="total_amount" name="total_amount" value="{{ old('total_amount', $booking->total_amount) }}" step="0.01" min="0" required>
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3">{{ old('notes', $booking->notes) }}</textarea>
                </div>

                <div style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">Update Booking</button>
                    <a href="{{ route('bookings.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

