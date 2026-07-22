<?php
namespace AHEcommerce\Commerce\Wishlist;

/**
 * Manages customer wishlists.
 * Guests get session-based wishlists; logged-in users get persistent DB storage.
 */
class Wishlist_Service {

	/**
	 * Get the current user's wishlist product IDs.
	 *
	 * @return array<int>
	 */
	public static function get_wishlist() {
		if ( is_user_logged_in() ) {
			return self::get_db_wishlist( get_current_user_id() );
		}
		return self::get_session_wishlist();
	}

	/**
	 * Add a product to the wishlist.
	 */
	public static function add( $product_id ) {
		$product_id = (int) $product_id;
		if ( is_user_logged_in() ) {
			return self::add_db( get_current_user_id(), $product_id );
		}
		return self::add_session( $product_id );
	}

	/**
	 * Remove a product from the wishlist.
	 */
	public static function remove( $product_id ) {
		$product_id = (int) $product_id;
		if ( is_user_logged_in() ) {
			return self::remove_db( get_current_user_id(), $product_id );
		}
		return self::remove_session( $product_id );
	}

	/**
	 * Check if a product is in the wishlist.
	 */
	public static function is_in_wishlist( $product_id ) {
		return in_array( (int) $product_id, self::get_wishlist(), true );
	}

	/**
	 * Get wishlist count.
	 */
	public static function count() {
		return count( self::get_wishlist() );
	}

	/**
	 * Clear the entire wishlist.
	 */
	public static function clear() {
		if ( is_user_logged_in() ) {
			global $wpdb;
			$table = $wpdb->prefix . 'ah_ecommerce_wishlist';
			$wpdb->delete( $table, array( 'user_id' => get_current_user_id() ) );
			return true;
		}
		if ( session_status() === PHP_SESSION_ACTIVE ) {
			$_SESSION['ah_wishlist'] = array();
		}
		return true;
	}

	// --- Database operations (logged-in users) ---

	private static function get_db_wishlist( $user_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_wishlist';
		$results = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT product_id FROM {$table} WHERE user_id = %d ORDER BY created_at DESC",
				$user_id
			)
		);
		return array_map( 'intval', $results );
	}

	private static function add_db( $user_id, $product_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_wishlist';
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE user_id = %d AND product_id = %d",
				$user_id,
				$product_id
			)
		);
		if ( $exists ) {
			return false;
		}
		$wpdb->insert( $table, array(
			'user_id'    => $user_id,
			'product_id' => $product_id,
			'created_at' => current_time( 'mysql' ),
		) );
		return true;
	}

	private static function remove_db( $user_id, $product_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_wishlist';
		return $wpdb->delete( $table, array(
			'user_id'    => $user_id,
			'product_id' => $product_id,
		) ) > 0;
	}

	// --- Session operations (guests) ---

	private static function get_session_wishlist() {
		if ( session_status() !== PHP_SESSION_ACTIVE ) {
			return array();
		}
		return isset( $_SESSION['ah_wishlist'] ) ? array_map( 'intval', $_SESSION['ah_wishlist'] ) : array();
	}

	private static function add_session( $product_id ) {
		if ( session_status() !== PHP_SESSION_ACTIVE ) {
			return false;
		}
		if ( ! isset( $_SESSION['ah_wishlist'] ) ) {
			$_SESSION['ah_wishlist'] = array();
		}
		if ( ! in_array( $product_id, $_SESSION['ah_wishlist'], true ) ) {
			$_SESSION['ah_wishlist'][] = $product_id;
		}
		return true;
	}

	private static function remove_session( $product_id ) {
		if ( session_status() !== PHP_SESSION_ACTIVE || ! isset( $_SESSION['ah_wishlist'] ) ) {
			return false;
		}
		$key = array_search( $product_id, $_SESSION['ah_wishlist'], true );
		if ( $key !== false ) {
			unset( $_SESSION['ah_wishlist'][ $key ] );
			$_SESSION['ah_wishlist'] = array_values( $_SESSION['ah_wishlist'] );
			return true;
		}
		return false;
	}
}
