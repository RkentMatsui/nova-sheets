<?php
class Nova_Google_Sheets_Integration {
	private $client;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'register_plugin_settings' ) );
		$this->client = new Nova_Sheets_Client();
	}

	public function register_plugin_settings() {
		register_setting( 'google_sheets_integration', 'nova_google_sheets_credentials' );
		register_setting( 'google_sheets_integration', 'nova_google_sheets_spreadsheet_id' ); // Register a new setting for Spreadsheet ID

		add_settings_section(
			'google_sheets_integration_section',
			'Google Sheets API Settings',
			null,
			'google_sheets_integration'
		);

		add_settings_field(
			'google_sheets_credentials_field',
			'Google API Credentials File',
			array( $this, 'google_sheets_credentials_field_callback' ),
			'google_sheets_integration',
			'google_sheets_integration_section'
		);

		// Add a new field for entering the Spreadsheet ID
		add_settings_field(
			'google_sheets_spreadsheet_id_field',
			'Spreadsheet ID',
			array( $this, 'google_sheets_spreadsheet_id_field_callback' ),
			'google_sheets_integration',
			'google_sheets_integration_section'
		);
	}

	public function google_sheets_credentials_field_callback() {
		$option = get_option( 'nova_google_sheets_credentials' );
		echo '<input type="file" name="nova_google_sheets_credentials" /> <br>';
		if ( ! empty( $option ) ) {
			echo '<span>Current File: ' . esc_html( $option ) . '</span>';
		}
	}

	public function google_sheets_spreadsheet_id_field_callback() {
		$option = get_option( 'nova_google_sheets_spreadsheet_id' );
		echo '<input type="text" name="nova_google_sheets_spreadsheet_id" value="' . esc_attr( $option ) . '" />';
	}


	public function add_plugin_page() {
		add_menu_page(
			'Google Sheets Integration', // Page title
			'Google Sheets',             // Menu title
			'manage_options',            // Capability
			'google_sheets_integration', // Menu slug
			array( $this, 'display_page' ) // Callback function
		);
	}

	public function display_page() {
		echo '<div class="wrap"><h1>Google Sheets Integration</h1>';

		// Check for file upload
		if ( isset( $_FILES['nova_google_sheets_credentials'] ) ) {
			$this->handle_file_upload( $_FILES['nova_google_sheets_credentials'] );
		}

		if ( isset( $_POST['nova_google_sheets_spreadsheet_id'] ) ) {
			update_option( 'nova_google_sheets_spreadsheet_id', sanitize_text_field( $_POST['nova_google_sheets_spreadsheet_id'] ) );
		}

		// Update other settings if form is submitted
		if ( isset( $_POST['update_sheet'] ) ) {
			$updated = $this->client->updateSheet();
			if ( $updated ) {
				echo '<div class="updated"><p>Sheet updated.</p></div>';
			}
		}

		echo '<form method="post" enctype="multipart/form-data">';
		settings_fields( 'google_sheets_integration' );
		do_settings_sections( 'google_sheets_integration' );
		submit_button( 'Save Settings' );
		echo '</form></div>';

		echo '<form method="post">
                <input type="submit" name="update_sheet" value="Update Sheet" class="button button-primary">
              </form></div>';

		echo '<p><a href="https://docs.google.com/spreadsheets/d/1YVQ1JurS--wlh2CKxj1Oihbt1dE7N4OSLYixsWmTh_0/edit#gid=0" target="_blank" class="button">View Google Sheet</a></p>';
	}

	private function handle_file_upload( $file ) {
		if ( $file['size'] > 0 ) {
			$upload = wp_handle_upload( $file, array( 'test_form' => false ) );
			if ( ! isset( $upload['error'] ) && isset( $upload['file'] ) ) {
				update_option( 'nova_google_sheets_credentials', $upload['file'] );
				echo '<div class="updated"><p>Successful upload.</p></div>';
			} else {
				echo '<div class="error"><p>Upload error: ' . $upload['error'] . '</p></div>';
			}
		}
	}
}
