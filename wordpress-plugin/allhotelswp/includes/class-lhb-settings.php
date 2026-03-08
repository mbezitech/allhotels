<?php

class LHB_Settings {

	public function add_plugin_admin_menu() {
		add_options_page(
			'allhotelswp Settings', 
			'allhotelswp', 
			'manage_options', 
			'allhotelswp', 
			array( $this, 'display_plugin_setup_page' )
		);
	}

	public function display_plugin_setup_page() {
		?>
		<div class="wrap">
			<h2>allhotelswp Integration</h2>
			<form action="options.php" method="post">
				<?php 
				settings_fields( 'lhb_options' );
				do_settings_sections( 'allhotelswp' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function register_settings() {
		register_setting( 'lhb_options', 'lhb_api_url' );
		register_setting( 'lhb_options', 'lhb_hotel_slug' );
		register_setting( 'lhb_options', 'lhb_rooms_page_url' );

		add_settings_section(
			'lhb_section_api',
			'API Configuration',
			array( $this, 'section_api_callback' ),
			'allhotelswp'
		);

		add_settings_field(
			'lhb_api_url',
			'Laravel API Base URL',
			array( $this, 'field_api_url_callback' ),
			'allhotelswp',
			'lhb_section_api'
		);

		add_settings_field(
			'lhb_hotel_slug',
			'Hotel Slug',
			array( $this, 'field_hotel_slug_callback' ),
			'allhotelswp',
			'lhb_section_api'
		);

		add_settings_field(
			'lhb_rooms_page_url',
			'Rooms Page URL (Optional)',
			array( $this, 'field_rooms_page_url_callback' ),
			'allhotelswp',
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

	public function field_rooms_page_url_callback() {
		$val = get_option( 'lhb_rooms_page_url', '' );
		echo '<input type="url" name="lhb_rooms_page_url" value="' . esc_attr( $val ) . '" class="regular-text" placeholder="https://yourwebsite.com/rooms" />';
		echo '<p class="description">Optional: If your [laravel_hotel_rooms] shortcode is on a different page, enter its URL here.</p>';
	}
}
