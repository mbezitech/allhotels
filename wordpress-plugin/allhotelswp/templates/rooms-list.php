<?php
/**
 * Room list item template
 * Available variable: $room, $check_in, $check_out
 */
$display_image = !empty($room['images']) ? $room['images'][0] : '';
?>
<div class="lhb-room-card">
    <?php if ($display_image): ?>
        <img src="<?php echo esc_url($display_image); ?>" class="lhb-room-image" alt="Room <?php echo esc_html($room['room_number']); ?>">
    <?php else: ?>
        <div class="lhb-room-image-placeholder">No Image Available</div>
    <?php endif; ?>

    <div class="lhb-room-details-content">
        <div class="lhb-room-header">
            <h3>Room <?php echo esc_html( $room['room_number'] ?? '' ); ?></h3>
            <span class="lhb-room-type-badge"><?php echo esc_html( $room['room_type']['name'] ?? 'Standard' ); ?></span>
        </div>
        
        <div class="lhb-room-meta">
            <div class="lhb-meta-item">
                <span class="lhb-meta-label">Capacity:</span>
                <span class="lhb-meta-value"><?php echo esc_html( $room['capacity'] ?? '2' ); ?> Persons</span>
            </div>
            <div class="lhb-meta-item">
                <span class="lhb-price-tag">$<?php echo esc_html( number_format($room['price_per_night'], 2) ); ?><small>/night</small></span>
            </div>
        </div>

        <?php if (!empty($room['description'])): ?>
            <p class="lhb-room-desc"><?php echo esc_html(wp_trim_words($room['description'], 20)); ?></p>
        <?php endif; ?>

        <div class="lhb-room-actions">
            <button type="button" class="lhb-toggle-booking-btn" onclick="jQuery(this).closest('.lhb-room-card').find('.lhb-booking-form-wrapper').slideToggle();">
                Check Dates & Book
            </button>
        </div>

        <!-- Hidden Booking Form - Toggleable -->
        <div class="lhb-booking-form-wrapper" style="display: none;">
            <div class="lhb-booking-form-inner">
                <h4>Reservation Details</h4>
                <form class="lhb-booking-form" data-room-id="<?php echo esc_attr( $room['id'] ); ?>">
                    <input type="hidden" name="room_id" value="<?php echo esc_attr( $room['id'] ); ?>">
                    
                    <div class="lhb-form-row">
                        <div class="lhb-form-group">
                            <label>Full Name</label>
                            <input type="text" name="guest_name" placeholder="John Doe" required>
                        </div>
                        <div class="lhb-form-group">
                            <label>Email Address</label>
                            <input type="email" name="guest_email" placeholder="john@example.com" required>
                        </div>
                    </div>

                    <div class="lhb-form-row">
                        <div class="lhb-form-group">
                            <label>Phone Number</label>
                            <input type="text" name="guest_phone" placeholder="+255..." required>
                        </div>
                        <div class="lhb-form-group">
                            <label>Adults</label>
                            <input type="number" name="adults" min="1" value="1" required>
                        </div>
                    </div>

                    <div class="lhb-form-row">
                        <div class="lhb-form-group">
                            <label>Check In</label>
                            <input type="date" name="check_in" value="<?php echo esc_attr($check_in); ?>" required>
                        </div>
                        <div class="lhb-form-group">
                            <label>Check Out</label>
                            <input type="date" name="check_out" value="<?php echo esc_attr($check_out); ?>" required>
                        </div>
                    </div>

                    <div class="lhb-form-group">
                        <label>Children</label>
                        <input type="number" name="children" min="0" value="0">
                    </div>

                    <div class="lhb-form-footer">
                        <button type="submit" class="lhb-submit-btn">Confirm My Booking</button>
                        <div class="lhb-form-message"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
