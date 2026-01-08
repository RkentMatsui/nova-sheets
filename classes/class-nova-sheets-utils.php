<?php
/**
 * Nova Sheets Utilities
 *
 * Shared utility functions for user data operations used across the Nova Sheets plugin.
 * This class provides helper methods for querying WooCommerce orders, user quotes,
 * and performing string operations.
 *
 * @package Nova_Sheets
 * @since   1.1.0
 */
class Nova_Sheets_Utils {

	/**
	 * Get the count of orders for a specific user.
	 *
	 * Retrieves the order count from user meta. Returns the count if the meta value
	 * is an array, otherwise returns the raw value.
	 *
	 * @since 1.1.0
	 *
	 * @param int $user_id The WordPress user ID.
	 * @return int|string The count of orders, or error message if WooCommerce is not active.
	 */
	public static function user_orders_count( $user_id ) {
		if ( ! class_exists( 'WC_Order_Query' ) ) {
			return 'WooCommerce is not active';
		}

		$result = get_user_meta( $user_id, 'nova_user_orders', true );
		return is_array( $result ) ? count( $result ) : $result;
	}

	/**
	 * Check if a user is active based on recent quote submissions.
	 *
	 * A user is considered active if they have submitted at least one quote
	 * in the last 4 weeks with status 'publish' or 'checked_out'.
	 *
	 * @since 1.1.0
	 *
	 * @param int $user_id The WordPress user ID.
	 * @return int The number of quotes found in the last 4 weeks.
	 */
	public static function is_user_active( $user_id ) {
		$four_weeks_ago = date( 'Y-m-d H:i:s', strtotime( '-4 weeks' ) );

		$quotes = new WP_Query( array(
			'post_type'      => 'nova_quote',
			'posts_per_page' => 1,
			'post_status'    => array( 'publish', 'checked_out' ),
			'author'         => $user_id,
			'date_query'     => array(
				'after' => $four_weeks_ago
			)
		) );

		return $quotes->found_posts;
	}

	/**
	 * Get the count of quotes for a specific user.
	 *
	 * Retrieves the quote count from user meta. Returns the count if the meta value
	 * is an array, otherwise returns the raw value.
	 *
	 * @since 1.1.0
	 *
	 * @param int $user_id The WordPress user ID.
	 * @return int|mixed The count of quotes, or the raw meta value.
	 */
	public static function user_quotes_count( $user_id ) {
		$user_quotes = get_user_meta( $user_id, 'nova_user_quotes', true );
		$quotes = is_array( $user_quotes ) ? count( $user_quotes ) : $user_quotes;
		return $quotes;
	}

	/**
	 * Get the count of non-hidden orders placed in the last 4 weeks.
	 *
	 * Queries WooCommerce orders for a specific user, excludes orders with
	 * the '_hide_order' meta flag set, and returns the count.
	 *
	 * @since 1.1.0
	 *
	 * @param int $user_id The WordPress user ID.
	 * @return int|string The count of visible orders in the last 4 weeks, or error message.
	 */
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

	/**
	 * Check if a string contains any of the specified keywords.
	 *
	 * Performs a case-insensitive search for each keyword in the given string.
	 * Returns true if any keyword is found.
	 *
	 * @since 1.1.0
	 *
	 * @param string $string   The string to search within.
	 * @param array  $keywords Array of keywords to search for.
	 * @return bool True if any keyword is found, false otherwise.
	 */
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
