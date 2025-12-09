<?php
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
			if ( self::containsKeywords( $first_name, $keywords ) ||
				self::containsKeywords( $last_name, $keywords ) ||
				self::containsKeywords( $email, $keywords ) ) {
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
				'# of Orders'       => self::user_orders_count( $user->ID ),
				'# of Quotes'       => self::user_quotes_count( $user->ID ),
				'Business Type'     => get_field( 'business_type', 'user_' . $user->ID ),
				'Quotes Submitted (last 4 weeks)' => self::is_user_active( $user->ID ),
				'Orders Submitted (last 4 weeks)' => self::get_orders_before( $user->ID ),
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
            if ( self::containsKeywords( $first_name, $keywords ) ||
                self::containsKeywords( $last_name, $keywords ) ||
                self::containsKeywords( $email, $keywords ) ) {
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

	public static function user_orders_count( $user_id ) {
		if ( ! class_exists( 'WC_Order_Query' ) ) {
			return 'WooCommerce is not active';
		}

		$result = get_user_meta( $user_id, 'nova_user_orders', true );
		return is_array( $result ) ? count( $result ) : $result;
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
	
	public static function is_user_active( $user_id ) {
		$four_weeks_ago = date( 'Y-m-d H:i:s', strtotime( '-4 weeks' ) );

		$quotes = new WP_Query( array(
			'post_type' => 'nova_quote',
			'post_status' => array('publish', 'checked_out'),
			'posts_per_page' => 1,
			'author' => $user_id,
			'date_query' => array(
				'after' => $four_weeks_ago
			)
		) );

		return $quotes->found_posts;
	}

	public static function user_quotes_count( $user_id ) {
		$nova_user_quotes = get_user_meta( $user_id, 'nova_user_quotes', true );
		return is_array( $nova_user_quotes ) ? count( $nova_user_quotes ) : $nova_user_quotes;
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

}
