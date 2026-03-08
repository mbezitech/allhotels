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

		// Optionally grab check-in and check-out dates from URL query params
		$check_in = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '';
		$check_out = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '';

		$api_response = $api->get_rooms($check_in, $check_out);

		if ( is_wp_error( $api_response ) ) {
			return '<p>Error fetching rooms: ' . esc_html( $api_response->get_error_message() ) . '</p>';
		}

		// Expected payload logic based on an imaginary structured JSON from Laravel
		$rooms = isset($api_response['data']) ? $api_response['data'] : $api_response;

		ob_start();
		
		// Render form to filter by dates
		?>
		<form method="get" class="lhb-search-form">
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
}
