<?php
/**
 * Room type list item template
 * Available variable: $room_type
 */

$api_url = rtrim(get_option( 'lhb_api_url', '' ), '/');
$images = isset($room_type['images']) && is_array($room_type['images']) ? $room_type['images'] : [];
$featured_image = !empty($images) ? $api_url . $images[0] : '';
?>
<div class="lhb-room-card lhb-room-type-card">
	<?php if ( $featured_image ) : ?>
		<img src="<?php echo esc_url( $featured_image ); ?>" class="lhb-room-type-image" alt="<?php echo esc_attr( $room_type['name'] ); ?> Image" />
	<?php endif; ?>

	<h3><?php echo esc_html( $room_type['name'] ?? 'Room Type' ); ?></h3>

	<?php if ( ! empty( $room_type['description'] ) ) : ?>
		<p class="lhb-room-type-description"><?php echo esc_html( $room_type['description'] ); ?></p>
	<?php endif; ?>
	
	<div class="lhb-room-details" style="flex-grow: 1;">
		<p><strong>Base Price:</strong> <?php echo esc_html( $room_type['base_price'] ?? 'N/A' ); ?></p>
		<p><strong>Capacity:</strong> <?php echo esc_html( $room_type['capacity'] ?? 'N/A' ); ?> Persons</p>
	</div>

	<!-- Action linking to the booking page (can be customized by user) -->
	<div style="margin-top:auto; padding-top:15px;">
		<a href="<?php echo esc_url( add_query_arg( 'room_type_id', $room_type['id'] ) ); ?>#lhb-rooms-search" class="lhb-submit-btn" style="text-decoration:none; display:inline-block;">Check Availability</a>
	</div>
</div>
