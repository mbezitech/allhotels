<?php

class LHB_Settings {

	public function add_plugin_admin_menu() {
		add_options_page(
			'Laravel Booking Settings', 
			'Laravel Booking', 
			'manage_options', 
			'laravel-hotel-booking', 
			array( $this, 'display_plugin_setup_page' )
		);
	}

	public function display_plugin_setup_page() {
		?>
		<div class="wrap">
			<h2>Laravel Hotel Booking Integration</h2>
			<form action="options.php" method="post">
				<?php 
				settings_fields( 'lhb_options' );
				do_settings_sections( 'laravel-hotel-booking' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function register_settings() {
		register_setting( 'lhb_options', 'lhb_api_url' );
		register_setting( 'lhb_options', 'lhb_hotel_slug' );

		add_settings_section(
			'lhb_section_api',
			'API Configuration',
			array( $this, 'section_api_callback' ),
			'laravel-hotel-booking'
		);

		add_settings_field(
			'lhb_api_url',
			'Laravel API Base URL',
			array( $this, 'field_api_url_callback' ),
			'laravel-hotel-booking',
			'lhb_section_api'
		);

		add_settings_field(
			'lhb_hotel_slug',
			'Hotel Slug',
			array( $this, 'field_hotel_slug_callback' ),
			'laravel-hotel-booking',
			'lhb_section_api'
		);
	}

	public function section_api_callback() {
		echo 'Enter your Laravel application details below. The API Base URL should be the root URL (e.g., https://yourlaravelapp.com).';
	}

	public function field_api_url_callback() {
		$val = get_option( 'lhb_api_url', '' );
		echo '<input type="url" name="lhb_api_url" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="https://yourlaravelapp.com" />';
	}

	public function field_hotel_slug_callback() {
		$val = get_option( 'lhb_hotel_slug', '' );
		echo '<input type="text" name="lhb_hotel_slug" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="ocean-view-resort" />';
	}
}
