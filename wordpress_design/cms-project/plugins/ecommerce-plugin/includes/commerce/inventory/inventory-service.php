<?php
namespace AHEcommerce\Commerce\Inventory;

/**
 * Manages stock levels, low-stock alerts, and backorder logic.
 */
class Inventory_Service {

	/**
	 * Get current stock for a product.
	 *
	 * @return int Stock quantity or -1 for unlimited.
	 */
	public static function get_stock( $product_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_product_meta';
		$value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_value FROM {$table} WHERE product_id = %d AND meta_key = 'stock_quantity'",
				$product_id
			)
		);
		return $value !== null ? (int) maybe_unserialize( $value ) : -1;
	}

	/**
	 * Set stock quantity for a product.
	 */
	public static function set_stock( $product_id, $quantity ) {
		global $wpdb;
		$table  = $wpdb->prefix . 'ah_ecommerce_product_meta';
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_id FROM {$table} WHERE product_id = %d AND meta_key = 'stock_quantity'",
				$product_id
			)
		);
		if ( $exists ) {
			$wpdb->update( $table, array( 'meta_value' => (int) $quantity ), array( 'meta_id' => $exists ) );
		} else {
			$wpdb->insert( $table, array(
				'product_id' => $product_id,
				'meta_key'   => 'stock_quantity',
				'meta_value' => (int) $quantity,
			) );
		}
	}

	/**
	 * Reduce stock by a given quantity (on order).
	 *
	 * @return bool Success.
	 */
	public static function reduce_stock( $product_id, $quantity = 1 ) {
		$current = self::get_stock( $product_id );
		if ( $current === -1 ) {
			return true; // Unlimited stock.
		}
		$new = $current - $quantity;
		if ( $new < 0 ) {
			$backorders = self::get_meta( $product_id, 'allow_backorders' );
			if ( $backorders !== 'yes' ) {
				return false;
			}
		}
		self::set_stock( $product_id, max( 0, $new ) );
		return true;
	}

	/**
	 * Increase stock (on order cancel/refund).
	 */
	public static function increase_stock( $product_id, $quantity = 1 ) {
		$current = self::get_stock( $product_id );
		if ( $current === -1 ) {
			return true;
		}
		self::set_stock( $product_id, $current + $quantity );
		return true;
	}

	/**
	 * Check if a product is in stock.
	 */
	public static function is_in_stock( $product_id ) {
		$stock = self::get_stock( $product_id );
		if ( $stock === -1 ) {
			return true;
		}
		return $stock > 0;
	}

	/**
	 * Check if stock is low (at or below threshold).
	 */
	public static function is_low_stock( $product_id, $threshold = null ) {
		if ( $threshold === null ) {
			$threshold = (int) self::get_meta( $product_id, 'low_stock_threshold' );
			$threshold = $threshold > 0 ? $threshold : 5;
		}
		$stock = self::get_stock( $product_id );
		return $stock !== -1 && $stock <= $threshold && $stock >= 0;
	}

	/**
	 * Get stock status label.
	 */
	public static function get_stock_status( $product_id ) {
		$stock = self::get_stock( $product_id );
		if ( $stock === -1 ) {
			return 'instock';
		}
		if ( $stock <= 0 ) {
			$backorders = self::get_meta( $product_id, 'allow_backorders' );
			return $backorders === 'yes' ? 'onbackorder' : 'outofstock';
		}
		$threshold = (int) self::get_meta( $product_id, 'low_stock_threshold' );
		$threshold = $threshold > 0 ? $threshold : 5;
		return $stock <= $threshold ? 'lowstock' : 'instock';
	}

	/**
	 * Get all products with low stock.
	 */
	public static function get_low_stock_products( $threshold = 5 ) {
		global $wpdb;
		$products = $wpdb->prefix . 'ah_ecommerce_products';
		$meta     = $wpdb->prefix . 'ah_ecommerce_product_meta';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.*, m.meta_value AS stock_quantity
				FROM {$products} p
				INNER JOIN {$meta} m ON p.id = m.product_id
				WHERE m.meta_key = 'stock_quantity'
				AND m.meta_value <= %d
				AND m.meta_value >= 0
				AND p.status = 'published'
				ORDER BY m.meta_value ASC",
				$threshold
			)
		);
	}

	/**
	 * Get a meta value for a product.
	 */
	private static function get_meta( $product_id, $key ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_product_meta';
		return $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_value FROM {$table} WHERE product_id = %d AND meta_key = %s",
				$product_id,
				$key
			)
		);
	}
}
