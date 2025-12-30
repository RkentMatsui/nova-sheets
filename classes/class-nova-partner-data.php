<?php
/**
 * Nova Partner Data
 *
 * Handles retrieval and formatting of partner data for Google Sheets sync.
 * Provides methods to fetch all partners and their company emails with
 * filtering capabilities.
 *
 * @package Nova_Sheets
 * @since   1.0.0
 */
class Nova_Partner_Data {
	public static function get_all_partners() {
		$args    = array(
			'role'    => 'partner',
			'orderby' => 'registered',
			'order'   => 'ASC',
		);
		$users   = get_users( $args );
		$results = array();
        $novaPartnerDataObj = new Nova_Partner_Data();
		$keywords = array( 'test', 'demo' );

		foreach ( $users as $user ) {

			$first_name = $user->first_name;
			$last_name  = $user->last_name;
			$email      = $user->user_email;

			// Skip user if any field contains the keywords
			if ( Nova_Sheets_Utils::containsKeywords( $first_name, $keywords ) ||
				Nova_Sheets_Utils::containsKeywords( $last_name, $keywords ) ||
				Nova_Sheets_Utils::containsKeywords( $email, $keywords ) ) {
				continue;
			}

			// Retrieve country or default to 'NONE'
			$country = get_user_meta( $user->ID, 'billing_country', true );
			if ( empty( $country ) ) {
				$country = 'NONE';
			}

			$state = get_user_meta( $user->ID, 'billing_state', true );
			if ( empty( $state ) ) {
				$state = 'NONE';
			}

			$results[] = array(
				'Business ID'       => get_field( 'business_id', 'user_' . $user->ID ),
				'Business Name'     => get_field( 'business_name', 'user_' . $user->ID ),
				'Username'          => $user->user_login,
				'Name'              => $first_name . ' ' . $last_name,
				'Email'             => $email,
				'Phone'             => get_field( 'business_phone_number', 'user_' . $user->ID ),
				'Website'           => get_field( 'business_website', 'user_' . $user->ID ),
				'Address'			=> get_user_meta( $user->ID, 'billing_address_1',  true ),
				'City'				=> get_user_meta( $user->ID, 'billing_city',  true ),
				'Postcode'			=> get_user_meta( $user->ID, 'billing_postcode',  true ),
				'State'             => $state,
				'Country'           => $country,
				'Registration Date' =>  (new DateTime($user->user_registered))->format('Y-m-d'),
				'# of Orders'       => Nova_Sheets_Utils::user_orders_count( $user->ID ),
				'# of Quotes'       => Nova_Sheets_Utils::user_quotes_count( $user->ID ),
				'Business Type'     => get_field( 'business_type', 'user_' . $user->ID ),
				'Quotes Submitted (last 4 weeks)' => Nova_Sheets_Utils::is_user_active( $user->ID ),
				'Orders Submitted (last 4 weeks)' => Nova_Sheets_Utils::get_orders_before( $user->ID ),
                'Company Emails' => get_user_meta( $user->ID ,'employee_emails',true),
			);
		}

		return $results;
	}

    public static function get_all_partners_company_emails() {
        $args    = array(
            'role'    => 'partner',
            'orderby' => 'registered',
            'order'   => 'ASC',
        );
        $users   = get_users( $args );
        $results = array();
        $keywords = array( 'test', 'demo' );

        foreach ( $users as $user ) {

            $first_name = $user->first_name;
            $last_name  = $user->last_name;
            $email      = $user->user_email;

            // Skip user if any field contains the keywords
            if ( Nova_Sheets_Utils::containsKeywords( $first_name, $keywords ) ||
                Nova_Sheets_Utils::containsKeywords( $last_name, $keywords ) ||
                Nova_Sheets_Utils::containsKeywords( $email, $keywords ) ) {
                continue;
            }

            // Retrieve country or default to 'NONE'
            $country = get_user_meta( $user->ID, 'billing_country', true );
            if ( empty( $country ) ) {
                $country = 'NONE';
            }

            $state = get_user_meta( $user->ID, 'billing_state', true );
            if ( empty( $state ) ) {
                $state = 'NONE';
            }
            $employee_emails = get_user_meta( $user->ID ,'employee_emails',true);
            $employee_emails_arr = array_map('trim', explode(',',$employee_emails ) );
            if(count($employee_emails_arr) <= 1){
                $results[] = array(
                    'Business ID'       => get_field( 'business_id', 'user_' . $user->ID ),
                    'Business Name'     => get_field( 'business_name', 'user_' . $user->ID ),
                    'Username'          => $user->user_login,
                    'Name'              => $first_name . ' ' . $last_name,
                    'Email'             => $email,
                    'Phone'             => get_field( 'business_phone_number', 'user_' . $user->ID ),
                    'Website'           => get_field( 'business_website', 'user_' . $user->ID ),
                    'Address'			=> get_user_meta( $user->ID, 'billing_address_1',  true ),
                    'City'				=> get_user_meta( $user->ID, 'billing_city',  true ),
                    'Postcode'			=> get_user_meta( $user->ID, 'billing_postcode',  true ),
                    'State'             => $state,
                    'Country'           => $country,
                    'Registration Date' =>  (new DateTime($user->user_registered))->format('Y-m-d'),
                    'Business Type'     => get_field( 'business_type', 'user_' . $user->ID ),
                    'Company Name'      => '',
                    'Company Emails'    => '',
                );
            }else{
                foreach ($employee_emails_arr as $employee_email) {
                    $employee_data =  array_map('trim', explode(' ',$employee_email));
                    $employee_email_val = $employee_data[count($employee_data) - 1];//get the last one
                    $employee_name = implode(" ",array_slice($employee_data,0,count($employee_data) - 1));
                    $results[] = array(
                        'Business ID'       => get_field( 'business_id', 'user_' . $user->ID ),
                        'Business Name'     => get_field( 'business_name', 'user_' . $user->ID ),
                        'Username'          => $user->user_login,
                        'Name'              => $first_name . ' ' . $last_name,
                        'Email'             => $email,
                        'Phone'             => get_field( 'business_phone_number', 'user_' . $user->ID ),
                        'Website'           => get_field( 'business_website', 'user_' . $user->ID ),
                        'Address'			=> get_user_meta( $user->ID, 'billing_address_1',  true ),
                        'City'				=> get_user_meta( $user->ID, 'billing_city',  true ),
                        'Postcode'			=> get_user_meta( $user->ID, 'billing_postcode',  true ),
                        'State'             => $state,
                        'Country'           => $country,
                        'Registration Date' =>  (new DateTime($user->user_registered))->format('Y-m-d'),
                        'Business Type'     => get_field( 'business_type', 'user_' . $user->ID ),
                        'Company Name'      => $employee_name,
                        'Company Emails'    => $employee_email_val,
                    );
                }

            }
        }

        return $results;
    }
}
