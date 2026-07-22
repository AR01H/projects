<?php
namespace AHEcommerce\Commerce\Expiry;

/**
 * Handles product expiry tracking.
 * Products can have an expiry_date meta — after that date they auto-expire.
 */
class Expiry_Service {

	/**
	 * Check if a product has expired.
	 */
	public static function is_expired( $product_id ) {
		$expiry = self::get_expiry_date( $product_id );
		if ( ! $expiry ) {
			return false;
		}
		return strtotime( $expiry ) < time();
	}

	/**
	 * Get the expiry date for a product.
	 *
	 * @return string|null MySQL datetime or null.
	 */
	public static function get_expiry_date( $product_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_product_meta';
		$value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_value FROM {$table} WHERE product_id = %d AND meta_key = 'expiry_date'",
				$product_id
			)
		);
		return $value ? maybe_unserialize( $value ) : null;
	}

	/**
	 * Set the expiry date for a product.
	 */
	public static function set_expiry_date( $product_id, $date ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_product_meta';
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_id FROM {$table} WHERE product_id = %d AND meta_key = 'expiry_date'",
				$product_id
			)
		);
		if ( $exists ) {
			$wpdb->update( $table, array( 'meta_value' => $date ), array( 'meta_id' => $exists ) );
		} else {
			$wpdb->insert( $table, array(
				'product_id' => $product_id,
				'meta_key'   => 'expiry_date',
				'meta_value' => $date,
			) );
		}
	}

	/**
	 * Get time remaining until expiry.
	 *
	 * @return array{days: int, hours: int, minutes: int, seconds: int, expired: bool, total_seconds: int}
	 */
	public static function get_time_remaining( $product_id ) {
		$expiry = self::get_expiry_date( $product_id );
		if ( ! $expiry ) {
			return array( 'days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 0, 'expired' => true, 'total_seconds' => 0 );
		}
		$diff = strtotime( $expiry ) - time();
		if ( $diff <= 0 ) {
			return array( 'days' => 0, 'hours' => 0, 'minutes' => 0, 'seconds' => 0, 'expired' => true, 'total_seconds' => 0 );
		}
		return array(
			'days'          => (int) floor( $diff / 86400 ),
			'hours'         => (int) floor( ( $diff % 86400 ) / 3600 ),
			'minutes'       => (int) floor( ( $diff % 3600 ) / 60 ),
			'seconds'       => (int) ( $diff % 60 ),
			'expired'       => false,
			'total_seconds' => $diff,
		);
	}

	/**
	 * Get all products expiring within a given number of days.
	 *
	 * @return array
	 */
	public static function get_expiring_soon( $days = 7 ) {
		global $wpdb;
		$table       = $wpdb->prefix . 'ah_ecommerce_product_meta';
		$products    = $wpdb->prefix . 'ah_ecommerce_products';
		$future_date = gmdate( 'Y-m-d H:i:s', strtotime( "+{$days} days" ) );
		$now         = gmdate( 'Y-m-d H:i:s' );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.*, m.meta_value AS expiry_date
				FROM {$products} p
				INNER JOIN {$table} m ON p.id = m.product_id
				WHERE m.meta_key = 'expiry_date'
				AND m.meta_value BETWEEN %s AND %s
				AND p.status != 'expired'
				ORDER BY m.meta_value ASC",
				$now,
				$future_date
			)
		);
	}

	/**
	 * Mark expired products (called by cron).
	 */
	public static function process_expired_products() {
		global $wpdb;
		$products = $wpdb->prefix . 'ah_ecommerce_products';
		$meta     = $wpdb->prefix . 'ah_ecommerce_product_meta';
		$now      = gmdate( 'Y-m-d H:i:s' );

		$expired = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.id FROM {$products} p
				INNER JOIN {$meta} m ON p.id = m.product_id
				WHERE m.meta_key = 'expiry_date'
				AND m.meta_value < %s
				AND p.status != 'expired'",
				$now
			)
		);

		foreach ( $expired as $product ) {
			$wpdb->update( $products, array( 'status' => 'expired' ), array( 'id' => $product->id ) );
		}

		return count( $expired );
	}
}
