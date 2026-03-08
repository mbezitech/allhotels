<?php

class LHB_API {

	private $api_url;
	private $hotel_slug;

	public function __construct() {
		// Remove trailing slash if present
		$this->api_url = rtrim(get_option( 'lhb_api_url', '' ), '/');
		$this->hotel_slug = get_option( 'lhb_hotel_slug', '' );
	}

	public function is_configured() {
		return !empty($this->api_url) && !empty($this->hotel_slug);
	}

	/**
	 * Fetch available rooms from Laravel
	 * Expects a JSON response from /api/hotels/{slug}/rooms
	 */
	public function get_rooms($check_in = '', $check_out = '') {
		if ( ! $this->is_configured() ) {
			return new WP_Error( 'not_configured', 'Laravel API is not configured.' );
		}

		$endpoint = $this->api_url . '/api/hotels/' . $this->hotel_slug . '/rooms';
		
		// Optional: append dates for availability checking
		if ( $check_in && $check_out ) {
			$endpoint = add_query_arg( array(
				'check_in' => $check_in,
				'check_out' => $check_out
			), $endpoint );
		}

		$response = wp_remote_get( $endpoint, array(
			'timeout' => 15,
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return new WP_Error( 'api_error', isset($data['message']) ? $data['message'] : 'Failed to fetch rooms.' );
		}

		return $data;
	}

	/**
	 * Fetch room types from Laravel
	 * Expects a JSON response from /api/hotels/{slug}/room-types
	 */
	public function get_room_types() {
		if ( ! $this->is_configured() ) {
			return new WP_Error( 'not_configured', 'Laravel API is not configured.' );
		}

		$endpoint = $this->api_url . '/api/hotels/' . $this->hotel_slug . '/room-types';

		$response = wp_remote_get( $endpoint, array(
			'timeout' => 15,
			'headers' => array(
				'Accept' => 'application/json',
			),
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return new WP_Error( 'api_error', isset($data['message']) ? $data['message'] : 'Failed to fetch room types.' );
		}

		return $data;
	}

	/**
	 * Submit a booking to Laravel
	 * Expects POST request to /api/hotels/{slug}/rooms/{id}/book
	 */
	public function submit_booking( $room_id, $booking_data ) {
		if ( ! $this->is_configured() ) {
			return new WP_Error( 'not_configured', 'Laravel API is not configured.' );
		}

		$endpoint = $this->api_url . '/api/hotels/' . $this->hotel_slug . '/rooms/' . $room_id . '/book';

		$response = wp_remote_post( $endpoint, array(
			'timeout' => 15,
			'headers' => array(
				'Content-Type' => 'application/json',
				'Accept'       => 'application/json',
			),
			'body' => wp_json_encode( $booking_data )
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( wp_remote_retrieve_response_code( $response ) > 201 ) {
			return new WP_Error( 'api_error', isset($data['message']) ? $data['message'] : 'Booking failed.', $data );
		}

		return $data;
	}
}
