<?php
/**
 * Room type list item template
 * Available variable: $room_type
 */
?>
<div class="lhb-room-card lhb-room-type-card">
	<h3><?php echo esc_html( $room_type['name'] ?? 'Room Type' ); ?></h3>

	<?php if ( ! empty( $room_type['description'] ) ) : ?>
		<p class="lhb-room-type-description"><?php echo esc_html( $room_type['description'] ); ?></p>
	<?php endif; ?>
	
	<div class="lhb-room-details">
		<p><strong>Base Price:</strong> <?php echo esc_html( $room_type['base_price'] ?? 'N/A' ); ?></p>
		<p><strong>Capacity:</strong> <?php echo esc_html( $room_type['capacity'] ?? 'N/A' ); ?> Persons</p>
	</div>

	<!-- Action linking to the booking page (can be customized by user) -->
	<div style="margin-top:15px;">
		<a href="#book-now" class="lhb-submit-btn" style="text-decoration:none; display:inline-block;">Check Availability</a>
	</div>
</div>
