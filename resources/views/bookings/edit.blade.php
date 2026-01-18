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
            
            // Guest capacity validation
            const roomSelect = document.getElementById('room_id');
            const adultsInput = document.getElementById('adults');
            const childrenInput = document.getElementById('children');
            const capacityInfo = document.getElementById('capacity-info');
            const guestWarning = document.getElementById('guest-warning');
            const submitBtn = document.querySelector('button[type="submit"]');
            
            const checkInInput = document.getElementById('check_in');
            const checkOutInput = document.getElementById('check_out');
            const totalAmountInput = document.getElementById('total_amount');
            const priceSummary = document.getElementById('price-summary');
            const pricePerNightSpan = document.getElementById('price-per-night');
            const nightsCountSpan = document.getElementById('nights-count');
            const calculatedTotalSpan = document.getElementById('calculated-total');
            const discountInput = document.getElementById('discount');
            const finalAmountDisplay = document.getElementById('final-amount-display');
            const finalAmountValue = document.getElementById('final-amount-value');
            
            function calculateFinalAmount() {
                const totalAmount = parseFloat(totalAmountInput.value) || 0;
                const discount = parseFloat(discountInput.value) || 0;
                const finalAmount = Math.max(0, totalAmount - discount);
                
                if (totalAmount > 0) {
                    finalAmountValue.textContent = finalAmount.toFixed(2);
                    finalAmountDisplay.style.display = discount > 0 ? 'block' : 'none';
                } else {
                    finalAmountDisplay.style.display = 'none';
                }
            }
            
            function calculateTotal() {
                const selectedOption = roomSelect.options[roomSelect.selectedIndex];
                const checkIn = checkInInput.value;
                const checkOut = checkOutInput.value;
                
                // Hide summary if room not selected or dates not filled
                if (!selectedOption.value || !checkIn || !checkOut) {
                    priceSummary.style.display = 'none';
                    return;
                }
                
                const checkInDate = new Date(checkIn);
                const checkOutDate = new Date(checkOut);
                
                // Validate dates
                if (checkOutDate <= checkInDate) {
                    priceSummary.style.display = 'none';
                    return;
                }
                
                // Calculate number of nights
                const diffTime = checkOutDate - checkInDate;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                
                // Get price per night
                const pricePerNight = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                
                // Calculate total
                const total = pricePerNight * diffDays;
                
                // Update display
                pricePerNightSpan.textContent = pricePerNight.toFixed(2);
                nightsCountSpan.textContent = diffDays;
                calculatedTotalSpan.textContent = total.toFixed(2);
                totalAmountInput.value = total.toFixed(2);
                
                // Show summary
                priceSummary.style.display = 'block';
                
                // Recalculate final amount
                calculateFinalAmount();
            }
            
            function updateCapacityInfo() {
                const selectedOption = roomSelect.options[roomSelect.selectedIndex];
                if (selectedOption.value) {
                    const capacity = parseInt(selectedOption.getAttribute('data-capacity'));
                    capacityInfo.textContent = `Room capacity: ${capacity} guests`;
                    capacityInfo.style.display = 'block';
                } else {
                    capacityInfo.style.display = 'none';
                }
                validateGuests();
                calculateTotal();
            }
            
            function validateGuests() {
                const selectedOption = roomSelect.options[roomSelect.selectedIndex];
                if (!selectedOption.value) {
                    guestWarning.style.display = 'none';
                    if (submitBtn) submitBtn.disabled = false;
                    return;
                }
                
                const capacity = parseInt(selectedOption.getAttribute('data-capacity'));
                const adults = parseInt(adultsInput.value) || 0;
                const children = parseInt(childrenInput.value) || 0;
                const totalGuests = adults + children;
                
                if (totalGuests > capacity) {
                    guestWarning.style.display = 'block';
                    guestWarning.innerHTML = `<strong>Warning:</strong> Total guests (${totalGuests}) exceed room capacity of ${capacity}!`;
                    if (submitBtn) submitBtn.disabled = true;
                } else {
                    guestWarning.style.display = 'none';
                    if (submitBtn) submitBtn.disabled = false;
                }
            }
            
            roomSelect.addEventListener('change', updateCapacityInfo);
            checkInInput.addEventListener('change', calculateTotal);
            checkOutInput.addEventListener('change', calculateTotal);
            if (discountInput) {
                discountInput.addEventListener('input', calculateFinalAmount);
                // Initialize on page load
                calculateFinalAmount();
            }
            adultsInput.addEventListener('input', validateGuests);
            childrenInput.addEventListener('input', validateGuests);
            
            // Initialize on page load
            updateCapacityInfo();
            
            // Calculate if values exist
            if (checkInInput.value && checkOutInput.value && roomSelect.value) {
                calculateTotal();
            }
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
                            <option value="{{ $room->id }}" 
                                    data-capacity="{{ $room->capacity }}"
                                    data-price="{{ $room->price_per_night }}"
                                    {{ old('room_id', $booking->room_id) == $room->id ? 'selected' : '' }}>
                                {{ $room->room_number }} - {{ $room->roomType->name ?? 'N/A' }} (Capacity: {{ $room->capacity }}, ${{ number_format($room->price_per_night, 2) }}/night)
                            </option>
                        @endforeach
                    </select>
                    <small id="capacity-info" style="color: #666; margin-top: 5px; display: block;"></small>
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
                        @php
                            // Extract country code and phone number from existing value
                            $phoneValue = old('guest_phone', $booking->guest_phone ?? '');
                            $countryCode = '+1'; // Default
                            $phoneNumber = $phoneValue;
                            
                            // Try to extract country code if it starts with +
                            if (preg_match('/^(\+\d{1,4})\s*(.+)$/', $phoneValue, $matches)) {
                                $countryCode = $matches[1];
                                $phoneNumber = $matches[2];
                            } elseif (preg_match('/^(\+\d{1,4})(.+)$/', $phoneValue, $matches)) {
                                $countryCode = $matches[1];
                                $phoneNumber = $matches[2];
                            }
                        @endphp
                        <div style="display: flex; gap: 10px;">
                            <div style="position: relative; width: 180px;">
                                <input type="text" id="country_code_search" placeholder="🔍 Search..." 
                                       style="width: 100%; padding: 12px 35px 12px 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;"
                                       onfocus="showCountryDropdown()" oninput="filterCountries()" onkeydown="handleCountrySearchKeydown(event)" autocomplete="off">
                                <span style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #999; pointer-events: none; font-size: 12px;">🔍</span>
                                <select id="country_code" name="country_code" 
                                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 2;"
                                        onchange="updateCountryDisplay()">
                                <!-- Countries will be populated dynamically from country-phone-data.js -->
                                <option value="+1" {{ old('country_code', $countryCode) == '+1' ? 'selected' : '' }} data-display="🇺🇸 +1 (United States/Canada)">🇺🇸 +1 (United States/Canada)</option>
                            </select>
                            <div id="country_dropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 2px solid #667eea; border-radius: 8px; max-height: 400px; overflow-y: auto; z-index: 1000; margin-top: 5px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"></div>
                            </div>
                            <input type="tel" id="guest_phone" name="guest_phone" value="{{ old('guest_phone', $phoneNumber) }}" placeholder="Phone number" style="flex: 1; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;" oninput="validatePhoneNumber()">
                        </div>
                        <div id="phone_validation_message" style="margin-top: 5px; font-size: 13px;"></div>
                        <small style="color: #666; display: block; margin-top: 5px;">Type country name or code to search, then enter phone number</small>
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
                        <input type="number" id="adults" name="adults" value="{{ old('adults', $booking->adults) }}" required min="1" max="100">
                        @error('adults')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="children">Children</label>
                        <input type="number" id="children" name="children" value="{{ old('children', $booking->children ?? 0) }}" min="0" max="100">
                        @error('children')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div id="guest-warning" style="display: none; padding: 10px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; margin-bottom: 20px; color: #856404;">
                    <strong>Warning:</strong> Total guests exceed room capacity!
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

                <!-- Price Calculation Summary -->
                <div id="price-summary" style="display: none; background: #f8f9fa; border: 2px solid #667eea; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                    <h3 style="color: #333; font-size: 18px; margin-bottom: 15px; margin-top: 0;">Price Summary</h3>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px;">
                        <span style="color: #666;">Price per night:</span>
                        <span style="font-weight: 600; color: #333;">$<span id="price-per-night">0.00</span></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px;">
                        <span style="color: #666;">Number of nights:</span>
                        <span style="font-weight: 600; color: #333;"><span id="nights-count">0</span> night(s)</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-top: 15px; border-top: 2px solid #667eea; margin-top: 10px;">
                        <span style="font-size: 18px; font-weight: 700; color: #333;">Total Amount:</span>
                        <span style="font-size: 20px; font-weight: 700; color: #667eea;">$<span id="calculated-total">0.00</span></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="total_amount">Total Amount *</label>
                    <input type="number" id="total_amount" name="total_amount" value="{{ old('total_amount', $booking->total_amount) }}" step="0.01" min="0" required readonly style="background: #f8f9fa; cursor: not-allowed;">
                    
                    <div class="form-group" style="margin-top: 20px;">
                        <label for="discount">Discount</label>
                        <input type="number" id="discount" name="discount" value="{{ old('discount', $booking->discount ?? 0) }}" step="0.01" min="0" oninput="calculateFinalAmount()">
                        <small style="color: #666; margin-top: 5px; display: block;">Enter discount amount (e.g., 50.00)</small>
                        @error('discount')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div id="final-amount-display" style="display: none; background: #e8f5e9; border: 2px solid #4caf50; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 16px; font-weight: 600; color: #333;">Final Amount (After Discount):</span>
                            <span style="font-size: 20px; font-weight: 700; color: #4caf50;">$<span id="final-amount-value">0.00</span></span>
                        </div>
                    </div>
                    <small style="color: #666; margin-top: 5px; display: block;">This amount is automatically calculated based on room price and number of nights.</small>
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
    
    <script src="{{ asset('js/country-phone-data.js') }}"></script>
    <script>
        // Country Code Search Functionality
        let allCountries = [];
        let selectedCountryIndex = -1;
        
        function initializeCountryDropdown() {
            const countrySelect = document.getElementById('country_code');
            const searchInput = document.getElementById('country_code_search');
            const dropdown = document.getElementById('country_dropdown');
            const phoneInput = document.getElementById('guest_phone');
            
            if (!countrySelect || !searchInput || !dropdown) return;
            
            // Load all countries from external data
            if (typeof getAllCountries === 'function') {
                allCountries = getAllCountries();
                
                // Get current selected country code
                const currentCode = countrySelect.value || '+1';
                
                // Populate select with all countries
                allCountries.forEach(country => {
                    const option = document.createElement('option');
                    option.value = country.code;
                    option.textContent = country.display;
                    option.setAttribute('data-display', country.display);
                    
                    if (country.code === currentCode) {
                        option.selected = true;
                        searchInput.value = country.display;
                        if (typeof getPhonePlaceholder === 'function') {
                            phoneInput.placeholder = getPhonePlaceholder(country.code);
                        }
                    }
                    
                    countrySelect.appendChild(option);
                });
            }
            
            // Set initial display value
            const selectedOption = countrySelect.options[countrySelect.selectedIndex];
            if (selectedOption) {
                searchInput.value = selectedOption.getAttribute('data-display') || selectedOption.text;
            }
            
            // Build dropdown list
            buildCountryDropdown();
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });
        }
        
        function showCountryDropdown() {
            const dropdown = document.getElementById('country_dropdown');
            if (dropdown) {
                dropdown.style.display = 'block';
                filterCountries();
            }
        }
        
        function filterCountries() {
            const searchInput = document.getElementById('country_code_search');
            const dropdown = document.getElementById('country_dropdown');
            
            if (!searchInput || !dropdown) return;
            
            const searchTerm = searchInput.value.toLowerCase().trim();
            dropdown.innerHTML = '';
            selectedCountryIndex = -1;
            
            if (searchTerm.length === 0) {
                // Show popular countries when empty
                const popularCodes = ['+1', '+44', '+61', '+33', '+49', '+39', '+34', '+86', '+91', '+81'];
                const popularCountries = allCountries.filter(c => popularCodes.includes(c.code));
                displayCountries(popularCountries.slice(0, 10), dropdown, searchInput);
                return;
            }
            
            // Filter from all countries data - improved search
            const filtered = allCountries.filter(country => {
                const nameMatch = country.name.toLowerCase().includes(searchTerm);
                const codeMatch = country.code.replace('+', '').includes(searchTerm.replace(/\+/g, ''));
                const displayMatch = country.display.toLowerCase().includes(searchTerm);
                
                // Check for country code without +
                const codeWithoutPlus = country.code.replace('+', '');
                const searchWithoutPlus = searchTerm.replace(/\+/g, '');
                
                return nameMatch || codeMatch || displayMatch || codeWithoutPlus.startsWith(searchWithoutPlus);
            });
            
            if (filtered.length === 0) {
                const noResults = document.createElement('div');
                noResults.style.padding = '15px 12px';
                noResults.style.color = '#999';
                noResults.style.textAlign = 'center';
                noResults.innerHTML = '🔍 No countries found<br><small style="font-size: 11px;">Try searching by country name or code (e.g., "US", "+1", "United States")</small>';
                dropdown.appendChild(noResults);
                return;
            }
            
            // Sort by relevance: exact matches first, then starts with, then contains
            filtered.sort((a, b) => {
                const aName = a.name.toLowerCase();
                const bName = b.name.toLowerCase();
                const aCode = a.code.replace('+', '');
                const bCode = b.code.replace('+', '');
                const search = searchTerm.replace(/\+/g, '');
                
                const aStarts = aName.startsWith(search) || aCode.startsWith(search) ? 1 : 0;
                const bStarts = bName.startsWith(search) || bCode.startsWith(search) ? 1 : 0;
                
                return bStarts - aStarts;
            });
            
            // Limit to 50 results for performance
            displayCountries(filtered.slice(0, 50), dropdown, searchInput);
        }

        function displayCountries(countries, dropdown, searchInput) {
            countries.forEach((country, index) => {
                const div = document.createElement('div');
                div.className = 'country-option';
                div.style.padding = '12px 15px';
                div.style.cursor = 'pointer';
                div.style.borderBottom = '1px solid #eee';
                div.style.transition = 'background 0.2s';
                div.setAttribute('data-index', index);
                
                // Highlight search term in display
                let displayText = country.display;
                const searchTerm = searchInput.value.toLowerCase();
                if (searchTerm) {
                    const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
                    displayText = country.display.replace(regex, '<mark style="background: #fff3cd; padding: 2px 0;">$1</mark>');
                }
                div.innerHTML = displayText;
                
                div.addEventListener('mouseenter', function() {
                    this.style.background = '#f0f4ff';
                    selectedCountryIndex = index;
                    updateSelectedCountry();
                });
                div.addEventListener('mouseleave', function() {
                    this.style.background = 'white';
                });
                
                div.addEventListener('click', function() {
                    selectCountry(country);
                });
                
                dropdown.appendChild(div);
            });
            
            updateSelectedCountry();
        }

        function selectCountry(country) {
            const countrySelect = document.getElementById('country_code');
            const searchInput = document.getElementById('country_code_search');
            const phoneInput = document.getElementById('guest_phone');
            const dropdown = document.getElementById('country_dropdown');
            
            countrySelect.value = country.code;
            searchInput.value = country.display;
            dropdown.style.display = 'none';
            
            // Update placeholder
            if (typeof getPhonePlaceholder === 'function') {
                phoneInput.placeholder = getPhonePlaceholder(country.code);
            }
            
            // Validate phone if already entered
            validatePhoneNumber();
        }

        function updateSelectedCountry() {
            const options = document.querySelectorAll('.country-option');
            options.forEach((opt, idx) => {
                if (idx === selectedCountryIndex) {
                    opt.style.background = '#e3f2fd';
                    opt.style.borderLeft = '3px solid #667eea';
                } else {
                    opt.style.background = opt.style.background === 'rgb(240, 244, 255)' ? '#f0f4ff' : 'white';
                    opt.style.borderLeft = 'none';
                }
            });
        }

        function handleCountrySearchKeydown(event) {
            const dropdown = document.getElementById('country_dropdown');
            const options = document.querySelectorAll('.country-option');
            
            if (!dropdown || dropdown.style.display === 'none' || options.length === 0) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    showCountryDropdown();
                }
                return;
            }
            
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                selectedCountryIndex = Math.min(selectedCountryIndex + 1, options.length - 1);
                updateSelectedCountry();
                options[selectedCountryIndex]?.scrollIntoView({ block: 'nearest' });
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                selectedCountryIndex = Math.max(selectedCountryIndex - 1, -1);
                updateSelectedCountry();
                if (selectedCountryIndex >= 0) {
                    options[selectedCountryIndex]?.scrollIntoView({ block: 'nearest' });
                }
            } else if (event.key === 'Enter' && selectedCountryIndex >= 0) {
                event.preventDefault();
                const selectedOption = options[selectedCountryIndex];
                if (selectedOption) {
                    const countryCode = document.getElementById('country_code').options[Array.from(options).indexOf(selectedOption) + 1]?.value;
                    if (countryCode && typeof getAllCountries === 'function') {
                        const country = allCountries.find(c => c.code === countryCode);
                        if (country) {
                            selectCountry(country);
                        }
                    }
                }
            } else if (event.key === 'Escape') {
                dropdown.style.display = 'none';
                selectedCountryIndex = -1;
            }
        }
        
        function updateCountryDisplay() {
            const countrySelect = document.getElementById('country_code');
            const searchInput = document.getElementById('country_code_search');
            const phoneInput = document.getElementById('guest_phone');
            
            if (!countrySelect || !searchInput) return;
            
            const selectedOption = countrySelect.options[countrySelect.selectedIndex];
            if (selectedOption) {
                searchInput.value = selectedOption.getAttribute('data-display') || selectedOption.text;
                
                // Update placeholder
                if (typeof getPhonePlaceholder === 'function') {
                    phoneInput.placeholder = getPhonePlaceholder(selectedOption.value);
                }
            }
            
            validatePhoneNumber();
        }
        
        function validatePhoneNumber() {
            const countrySelect = document.getElementById('country_code');
            const phoneInput = document.getElementById('guest_phone');
            const messageDiv = document.getElementById('phone_validation_message');
            
            if (!countrySelect || !phoneInput || !messageDiv) return;
            
            const countryCode = countrySelect.value;
            const phoneNumber = phoneInput.value;
            
            if (!phoneNumber) {
                messageDiv.innerHTML = '';
                phoneInput.style.borderColor = '#e0e0e0';
                return;
            }
            
            // Use the validation function from country-phone-data.js
            if (typeof window.validatePhoneNumber === 'function') {
                const result = window.validatePhoneNumber(countryCode, phoneNumber);
                
                if (result.valid) {
                    messageDiv.innerHTML = '<span style="color: #28a745;">✓ Valid phone number</span>';
                    phoneInput.style.borderColor = '#28a745';
                } else {
                    messageDiv.innerHTML = '<span style="color: #dc3545;">✗ ' + result.message + '</span>';
                    phoneInput.style.borderColor = '#dc3545';
                }
            } else {
                // Fallback validation
                const digits = phoneNumber.replace(/\D/g, '');
                if (digits.length < 5) {
                    messageDiv.innerHTML = '<span style="color: #dc3545;">✗ Phone number too short</span>';
                    phoneInput.style.borderColor = '#dc3545';
                } else if (digits.length > 15) {
                    messageDiv.innerHTML = '<span style="color: #dc3545;">✗ Phone number too long</span>';
                    phoneInput.style.borderColor = '#dc3545';
                } else {
                    messageDiv.innerHTML = '<span style="color: #28a745;">✓ Phone number looks valid</span>';
                    phoneInput.style.borderColor = '#28a745';
                }
            }
        }
        
        function buildCountryDropdown() {
            filterCountries();
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeCountryDropdown();
            
            // Validate phone on input
            const phoneInput = document.getElementById('guest_phone');
            if (phoneInput) {
                phoneInput.addEventListener('input', validatePhoneNumber);
            }
        });
    </script>
</body>
</html>

