<?php
/**
 * Room list item template
 * Available variable: $room
 */
?>
<div class="lhb-room-card">
	<h3><?php echo esc_html( $room['room_number'] ?? 'Room Name' ); ?> - <?php echo esc_html( $room['room_type']['name'] ?? 'Type' ); ?></h3>
	
	<div class="lhb-room-details">
		<p><strong>Price per night:</strong> <?php echo esc_html( $room['price_per_night'] ?? '' ); ?></p>
		<p><strong>Capacity:</strong> <?php echo esc_html( $room['capacity'] ?? '' ); ?> Persons</p>
	</div>

	<!-- Booking Form -->
	<div class="lhb-booking-form-wrapper">
		<h4>Book this room</h4>
		<form class="lhb-booking-form" data-room-id="<?php echo esc_attr( $room['id'] ); ?>">
			<!-- Hidden parameters passed to Laravel API -->
			<input type="hidden" name="room_id" value="<?php echo esc_attr( $room['id'] ); ?>">
			
			<div class="lhb-form-group">
				<label>Name</label>
				<input type="text" name="guest_name" required>
			</div>
			<div class="lhb-form-group">
				<label>Email</label>
				<input type="email" name="guest_email" required>
			</div>
			<div class="lhb-form-group">
				<label>Phone</label>
				<input type="text" name="guest_phone" required>
			</div>
			<div class="lhb-form-group">
				<label>Check In</label>
				<input type="date" name="check_in" value="<?php echo esc_attr($check_in); ?>" required>
			</div>
			<div class="lhb-form-group">
				<label>Check Out</label>
				<input type="date" name="check_out" value="<?php echo esc_attr($check_out); ?>" required>
			</div>
			<div class="lhb-form-group">
				<label>Adults</label>
				<input type="number" name="adults" min="1" value="1" required>
			</div>
			<div class="lhb-form-group">
				<label>Children</label>
				<input type="number" name="children" min="0" value="0">
			</div>

			<button type="submit" class="lhb-submit-btn">Complete Booking</button>
			<div class="lhb-form-message"></div>
		</form>
	</div>
</div>
