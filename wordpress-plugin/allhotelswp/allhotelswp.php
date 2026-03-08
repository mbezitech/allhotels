<?php
/**
 * Plugin Name:       allhotelswp
 * Description:       A production-ready plugin to connect WordPress with your Laravel booking API. Contact: +255 718 248 257
 * Version:           1.1.1
 * Author:            Inocent Mhina
 * License:           GPL-2.0+
 * Text Domain:       laravel-hotel-booking
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'LHB_VERSION', '1.1.1' );
define( 'LHB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LHB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * The core plugin class.
 */
class Laravel_Hotel_Booking {

	public function __construct() {
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	private function load_dependencies() {
		require_once LHB_PLUGIN_DIR . 'includes/class-lhb-settings.php';
		require_once LHB_PLUGIN_DIR . 'includes/class-lhb-api.php';
		require_once LHB_PLUGIN_DIR . 'includes/class-lhb-shortcodes.php';
	}

	private function define_admin_hooks() {
		$settings_admin = new LHB_Settings();
		add_action( 'admin_menu', array( $settings_admin, 'add_plugin_admin_menu' ) );
		add_action( 'admin_init', array( $settings_admin, 'register_settings' ) );
	}

	private function define_public_hooks() {
		$shortcodes = new LHB_Shortcodes();
		add_shortcode( 'laravel_hotel_rooms', array( $shortcodes, 'render_rooms_shortcode' ) );
		add_shortcode( 'laravel_hotel_room_types', array( $shortcodes, 'render_room_types_shortcode' ) );
		
		// Enqueue scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function enqueue_scripts() {
		wp_enqueue_style( 'lhb-style', LHB_PLUGIN_URL . 'assets/css/style.css', array(), LHB_VERSION, 'all' );
		wp_enqueue_script( 'lhb-script', LHB_PLUGIN_URL . 'assets/js/booking.js', array( 'jquery' ), LHB_VERSION, true );

		wp_localize_script( 'lhb-script', 'lhb_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'lhb-booking-nonce' )
		));
	}

	public function handle_ajax_booking() {
		check_ajax_referer( 'lhb-booking-nonce', 'security' );

		$api = new LHB_API();
		if ( ! $api->is_configured() ) {
			wp_send_json_error( 'API not configured' );
		}

		// Collect form data
		$room_id = isset($_POST['room_id']) ? intval($_POST['room_id']) : 0;
		if ( ! $room_id ) {
			wp_send_json_error( 'Invalid room ID' );
		}

		$booking_data = array(
			'guest_name'  => sanitize_text_field( $_POST['guest_name'] ),
			'guest_email' => sanitize_email( $_POST['guest_email'] ),
			'guest_phone' => sanitize_text_field( $_POST['guest_phone'] ),
			'check_in'    => sanitize_text_field( $_POST['check_in'] ),
			'check_out'   => sanitize_text_field( $_POST['check_out'] ),
			'adults'      => intval( $_POST['adults'] ),
			'children'    => intval( $_POST['children'] ),
		);

		$response = $api->submit_booking( $room_id, $booking_data );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_message() );
		}

		wp_send_json_success( 'Booking created successfully! Reference: ' . ($response['booking_reference'] ?? 'Confirmed') );
	}
}

/**
 * Begins execution of the plugin.
 */
function run_laravel_hotel_booking() {
	$plugin = new Laravel_Hotel_Booking();
	
	// Register AJAX hooks
	add_action( 'wp_ajax_nopriv_lhb_submit_booking', array( $plugin, 'handle_ajax_booking' ) );
	add_action( 'wp_ajax_lhb_submit_booking', array( $plugin, 'handle_ajax_booking' ) );
}
run_laravel_hotel_booking();
