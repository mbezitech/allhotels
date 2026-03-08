<?php

class LHB_Shortcodes {

	public function render_rooms_shortcode( $atts ) {
		$api = new LHB_API();

		// Shortcode Attributes
		$atts = shortcode_atts(
			array(
				'hotel' => get_option( 'lhb_hotel_slug', '' ),
			),
			$atts,
			'laravel_hotel_rooms'
		);

		if ( ! $api->is_configured() ) {
			return '<p>Laravel Hotel Booking API is not configured. Please open WP Admin Settings.</p>';
		}

		// Optionally grab check-in, check-out, and room_type dates from URL query params
		$check_in = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '';
		$check_out = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '';
		$room_type_id = isset($_GET['room_type_id']) ? sanitize_text_field($_GET['room_type_id']) : '';

		$api_response = $api->get_rooms($check_in, $check_out, $room_type_id);

		if ( is_wp_error( $api_response ) ) {
			return '<p>Error fetching rooms: ' . esc_html( $api_response->get_error_message() ) . '</p>';
		}

		$rooms = isset($api_response['data']) ? $api_response['data'] : $api_response;

		ob_start();

		if ( ! empty( $room_type_id ) ) {
			echo '<p style="margin-bottom:15px;">Showing results for selected room type. <a href="' . esc_url( remove_query_arg( 'room_type_id' ) ) . '">Show all types</a></p>';
		}
		
		// Render form to filter by dates
		?>
		<form method="get" class="lhb-search-form" id="lhb-rooms-search">
			<?php if ( ! empty( $room_type_id ) ) : ?>
				<input type="hidden" name="room_type_id" value="<?php echo esc_attr( $room_type_id ); ?>" />
			<?php endif; ?>
			<div class="lhb-form-group">
				<label>Check In</label>
				<input type="date" name="check_in" value="<?php echo esc_attr($check_in); ?>" required />
			</div>
			<div class="lhb-form-group">
				<label>Check Out</label>
				<input type="date" name="check_out" value="<?php echo esc_attr($check_out); ?>" required />
			</div>
			<button type="submit">Search Availability</button>
		</form>
		<?php

		if ( empty( $rooms ) || ! is_array( $rooms ) ) {
			echo '<p>No rooms available for the selected dates.</p>';
		} else {
			echo '<div class="lhb-rooms-container">';
			foreach ( $rooms as $room ) {
				include LHB_PLUGIN_DIR . 'templates/rooms-list.php';
			}
			echo '</div>';
		}

		return ob_get_clean();
	}

	public function render_room_types_shortcode( $atts ) {
		$api = new LHB_API();

		if ( ! $api->is_configured() ) {
			return '<p>Laravel Hotel Booking API is not configured. Please open WP Admin Settings.</p>';
		}

		$api_response = $api->get_room_types();

		if ( is_wp_error( $api_response ) ) {
			return '<p>Error fetching room types: ' . esc_html( $api_response->get_error_message() ) . '</p>';
		}

		$room_types = isset($api_response['data']) ? $api_response['data'] : $api_response;
		$layout = get_option( 'lhb_room_type_layout', 'grid' );

		ob_start();

		if ( empty( $room_types ) || ! is_array( $room_types ) ) {
			echo '<p>No room types found for this hotel.</p>';
		} else {
			echo '<div class="lhb-room-types-container lhb-layout-' . esc_attr( $layout ) . '">';
			foreach ( $room_types as $room_type ) {
				include LHB_PLUGIN_DIR . 'templates/room-types-list.php';
			}
			echo '</div>';
		}

		return ob_get_clean();
	}
}
