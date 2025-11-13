<?php
class Nova_Sheets_Client {

	private $spreadsheetID;

	public function __construct() {
		$this->spreadsheetID = get_option( 'nova_google_sheets_spreadsheet_id' );
		add_action( 'set_user_role', array( $this, 'update_row' ), 99 );
		add_action( 'profile_update', array( $this, 'update_row' ), 99 );
		/** if woocommerce order is placed, update the row */
		add_action( 'woocommerce_new_order', array( $this, 'updateSheet' ), 99 );
		/** if post type nova quote is created, update the row */
		add_action( 'save_post_nova_quote', array( $this, 'updateSheet' ), 99 );
	}

	public function getClient( $credentials_path = '' ) {
		$client = new Google_Client();
		$client->setApplicationName( 'Google Sheets and PHP Integration' );
		$client->setScopes( array( Google\Service\Sheets::SPREADSHEETS ) );
		if ( empty( $credentials_path ) ) {
			$credentials_path = get_option( 'nova_google_sheets_credentials' );
		}
		if ( ! $credentials_path ) {
			throw new Exception( 'Google Sheets credentials file path is not set.' );
		}
		$client->setAuthConfig( $credentials_path );
		$client->setAccessType( 'offline' );
		return $client;
	}

	public function updateSheet() {
		try {
			$client        = $this->getClient();
			$service       = new Google\Service\Sheets( $client );
			$spreadsheetId = $this->spreadsheetID;
			$range         = 'Partners (Master Copy)!A1:Z';

			$this->clearSheet( $service, $spreadsheetId, $range );
			$this->populateSheet( $service, $spreadsheetId );

			return true;
		} catch ( Exception $e ) {
			// Log the error or handle it as per your requirement
			error_log( 'Error updating the sheet: ' . $e->getMessage() );
			return false;
		}
	}

	public function insert_row( $user_id ) {
		try {
			$client        = $this->getClient();
			$service       = new Google\Service\Sheets( $client );
			$spreadsheetId = $this->spreadsheetID;

			$this->appendRow( $service, $spreadsheetId, $user_id );

			return true;
		} catch ( Exception $e ) {
			// Log the error or handle it as per your requirement
			error_log( 'Error updating the sheet: ' . $e->getMessage() );
			return false;
		}
	}

	public function update_row( $user_id ) {
		try {
			$client        = $this->getClient();
			$service       = new Google\Service\Sheets( $client );
			$spreadsheetId = $this->spreadsheetID;
			$range         = 'Partners (Master Copy)!A1:Z';

			// Retrieve current sheet data
			$response = $service->spreadsheets_values->get( $spreadsheetId, $range );
			$values   = $response->getValues();

			if ( empty( $values ) ) {
				echo "No data found in the sheet.<br>\n";
				return false;
			}

			$user       = get_user_by( 'id', $user_id );
			$user_login = $user->user_login;
			$rowIndex   = -1;

			// Find the row with the matching user login
			foreach ( $values as $index => $row ) {
				if ( isset( $row[2] ) && $row[2] == $user_login ) { // Assuming the login is in the third column (index 2)
					$rowIndex = $index;
					break;
				}
			}

			// Prepare new row data
			$newValues = array(
				array(
					get_field( 'business_id', 'user_' . $user_id ),
					get_field( 'business_name', 'user_' . $user_id ),
					$user->user_login,
					$user->first_name . ' ' . $user->last_name,
					$user->user_email,
					get_field( 'business_phone_number', 'user_' . $user_id ),
					get_field( 'business_website', 'user_' . $user_id ),
					get_user_meta( $user_id, 'billing_address_1', true ),
					get_user_meta( $user_id, 'billing_city', true ),
					get_user_meta( $user_id, 'billing_postcode', true ),
					get_user_meta( $user_id, 'billing_state', true ) ?: 'NONE',
					get_user_meta( $user_id, 'billing_country', true ) ?: 'NONE',
					( new DateTime( $user->user_registered ) )->format( 'Y-m-d' ),
					self::user_orders_count( $user_id ),
					self::user_quotes_count( $user_id ),
					get_field( 'business_type', 'user_' . $user_id ),
					self::is_user_active( $user_id ),
					self::get_orders_before( $user_id ),
                    get_user_meta($user_id,'employee_emails',true) ?: 'NONE',
				),
			);

			$body   = new Google\Service\Sheets\ValueRange( array( 'values' => $newValues ) );
			$params = array( 'valueInputOption' => 'RAW' );

			if ( $rowIndex === -1 ) {
				// Append the row if the user is not found
				$result = $service->spreadsheets_values->append( $spreadsheetId, 'Partners (Master Copy)!A1', $body, $params );
			} else {
				// Update the row if the user is found
				$updateRange = 'Partners (Master Copy)!A' . ( $rowIndex + 1 ) . ':R' . ( $rowIndex + 1 ); // Adjust the range to the specific row and columns
				$result      = $service->spreadsheets_values->update( $spreadsheetId, $updateRange, $body, $params );
			}

			return true;
		} catch ( Exception $e ) {
			error_log( 'Error updating the row: ' . $e->getMessage() );
			return false;
		}
	}





	private function clearSheet( $service, $spreadsheetId, $range ) {
		try {
			$clearRequest = new Google\Service\Sheets\ClearValuesRequest();
			$service->spreadsheets_values->clear( $spreadsheetId, $range, $clearRequest );
			return true;
		} catch ( Exception $e ) {
			echo 'Error while clearing the sheet: ' . $e->getMessage() . "<br>\n";
		}
	}

	private function appendRow( $service, $spreadsheetId, $user_id ) {

		if ( empty( $user_id ) ) {
			echo 'No user data available to update.<br>\n';
			return;
		}

		$values = array();

		$user = get_user_by( 'id', $user_id );

		$first_name = $user->first_name;
		$last_name  = $user->last_name;
		$email      = $user->user_email;

		$keywords = array( 'test', 'demo' );

			// Skip user if any field contains the keywords
		if ( self::containsKeywords( $first_name, $keywords ) ||
				self::containsKeywords( $last_name, $keywords ) ||
				self::containsKeywords( $email, $keywords ) ) {
			return;
		}

			// Retrieve country or default to 'NONE'
		$country = get_user_meta( $user_id, 'billing_country', true );
		if ( empty( $country ) ) {
			$country = 'NONE';
		}

		$state = get_user_meta( $user_id, 'billing_state', true );
		if ( empty( $state ) ) {
			$state = 'NONE';
		}

		$values = array(
			'Business ID'       => get_field( 'business_id', 'user_' . $user_id ),
			'Business Name'     => get_field( 'business_name', 'user_' . $user_id ),
			'Username'          => $user->user_login,
			'Name'              => $first_name . ' ' . $last_name,
			'Email'             => $email,
			'Phone'             => get_field( 'business_phone_number', 'user_' . $user_id ),
			'Website'           => get_field( 'business_website', 'user_' . $user_id ),
			'Address'           => get_user_meta( $user_id, 'billing_address_1', true ),
			'City'              => get_user_meta( $user_id, 'billing_city', true ),
			'Postcode'          => get_user_meta( $user_id, 'billing_postcode', true ),
			'State'             => $state,
			'Country'           => $country,
			'Registration Date' => ( new DateTime( $user->user_registered ) )->format( 'Y-m-d' ),
			'# of Orders'       => self::user_orders_count( $user_id ),
			'# of Quotes'       => self::user_quotes_count( $user_id ),
			'Business Type'     => get_field( 'business_type', 'user_' . $user->ID ),
			'Quotes Submitted (last 4 weeks)' => self::is_user_active( $user_id ),
			'Orders Submitted (last 4 weeks)' => self::get_orders_before( $user_id ),
			'Company Emails' => get_user_meta($user_id,'employee_emails',true),
		);

		$body   = new Google\Service\Sheets\ValueRange( array( 'values' => $values ) );
		$params = array( 'valueInputOption' => 'RAW' );
		try {
			/** append to sheet */
			$result = $service->spreadsheets_values->append( $spreadsheetId, 'Partners (Master Copy)!A1', $body, $params );
			return true;
		} catch ( Exception $e ) {
			echo 'Error while updating data: ' . $e->getMessage() . "<br>\n";
			return;
		}
	}

	private function populateSheet( $service, $spreadsheetId ) {
		$partners = Nova_Partner_Data::get_all_partners();
		if ( empty( $partners ) ) {
			echo 'No partners data available to update.<br>\n';
			return;
		}

		$values = array();
		// Prepare header from the keys of the first partner's data
		$header   = array_keys( $partners[0] );
		$values[] = $header;

		// Append data rows
		foreach ( $partners as $partner ) {
			$values[] = array_values( $partner ); // Convert associative array to indexed array
		}

		$body   = new Google\Service\Sheets\ValueRange( array( 'values' => $values ) );
		$params = array( 'valueInputOption' => 'RAW' );

		try {
			$result = $service->spreadsheets_values->update( $spreadsheetId, 'Partners (Master Copy)!A1', $body, $params );
			$this->formatHeader( $service, $spreadsheetId );
			return true;
		} catch ( Exception $e ) {
			echo 'Error while updating data: ' . $e->getMessage() . "<br>\n";
			return;
		}
	}

	private function formatHeader( $service, $spreadsheetId ) {
		$requests = array(
			new Google\Service\Sheets\Request(
				array(
					'repeatCell' => array(
						'range'  => array(
							'sheetId'          => 0,
							'startRowIndex'    => 0,
							'endRowIndex'      => 1,
							'startColumnIndex' => 0,
						),
						'cell'   => array(
							'userEnteredFormat' => array( 'textFormat' => array( 'bold' => true ) ),
						),
						'fields' => 'userEnteredFormat.textFormat.bold',
					),
				)
			),
		);

		$batchUpdateRequest = new Google\Service\Sheets\BatchUpdateSpreadsheetRequest( array( 'requests' => $requests ) );

		try {
			$service->spreadsheets->batchUpdate( $spreadsheetId, $batchUpdateRequest );
		} catch ( Exception $e ) {
			echo 'Error while formatting the header: ' . $e->getMessage() . "<br>\n";
		}
	}

	public static function containsKeywords( $string, $keywords ) {
		$lowerString = strtolower( $string ); // Convert string to lower case once
		foreach ( $keywords as $keyword ) {
			if ( strpos( $lowerString, $keyword ) !== false ) {
				return true;
			}
		}
		return false;
	}

	public static function user_orders_count( $user_id ) {
		if ( ! class_exists( 'WC_Order_Query' ) ) {
			return 'WooCommerce is not active';
		}

		$result = get_user_meta( $user_id, 'nova_user_orders', true );
		return is_array( $result ) ? count( $result) : $result;
	}
	
	public static function is_user_active( $user_id ) {
		$four_weeks_ago = date( 'Y-m-d H:i:s', strtotime( '-4 weeks' ) );

		$quotes = new WP_Query( array(
			'post_type' => 'nova_quote',
			'posts_per_page' => 1,
			'post_status' => array('publish', 'checked_out'),
			'author' => $user_id,
			'date_query' => array(
				'after' => $four_weeks_ago
			)
		) );

		return $quotes->found_posts;
	}

	public static function user_quotes_count( $user_id ) {
		$user_quotes = get_user_meta( $user_id, 'nova_user_quotes', true );
		$quotes = is_array($user_quotes) ? count( $user_quotes ) : $user_quotes;
		return $quotes; // Return the count of matching posts
	}
	
	public static function get_orders_before( $user_id ) {
		if ( ! class_exists( 'WC_Order_Query' ) ) {
			return 'WooCommerce is not active';
		}

		$four_weeks_ago = date( 'Y-m-d H:i:s', strtotime( '-4 weeks' ) );

		// Initialize the query object
		$order_query = new WC_Order_Query( array(
			'customer_id'  => $user_id,
			'limit'        => -1, // Retrieve all matching orders
			'date_created' => '>' . $four_weeks_ago, // Only fetch orders created in the last 4 weeks
		) );

		// Fetch all orders
		$orders = $order_query->get_orders();
		$result = [];

		foreach ( $orders as $order ) {
			$hide = $order->get_meta( '_hide_order' );
			if ( $hide ) {
				continue; // Exclude hidden orders
			}
			$result[] = $order;
		}

		return count( $result );
	}
}
