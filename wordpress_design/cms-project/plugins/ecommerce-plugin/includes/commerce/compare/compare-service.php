<?php
namespace AHEcommerce\Commerce\Compare;

/**
 * Product comparison — compare up to N products side by side.
 * Session-based for guests, DB-backed for logged-in users.
 */
class Compare_Service {

	const MAX_COMPARE = 4;

	/**
	 * Get compared product IDs.
	 */
	public static function get_compared() {
		if ( is_user_logged_in() ) {
			return self::get_db( get_current_user_id() );
		}
		return self::get_session();
	}

	/**
	 * Add a product to comparison.
	 *
	 * @return array{success: bool, message: string, count: int}
	 */
	public static function add( $product_id ) {
		$products = self::get_compared();
		if ( count( $products ) >= self::MAX_COMPARE ) {
			return array( 'success' => false, 'message' => 'Maximum ' . self::MAX_COMPARE . ' products for comparison.', 'count' => count( $products ) );
		}
		$product_id = (int) $product_id;
		if ( in_array( $product_id, $products, true ) ) {
			return array( 'success' => false, 'message' => 'Product already in comparison.', 'count' => count( $products ) );
		}
		$products[] = $product_id;
		self::save( $products );
		return array( 'success' => true, 'message' => 'Added to comparison.', 'count' => count( $products ) );
	}

	/**
	 * Remove a product from comparison.
	 */
	public static function remove( $product_id ) {
		$products = self::get_compared();
		$key = array_search( (int) $product_id, $products, true );
		if ( $key !== false ) {
			unset( $products[ $key ] );
			self::save( array_values( $products ) );
		}
		return array( 'success' => true, 'count' => count( $products ) );
	}

	/**
	 * Check if a product is in comparison.
	 */
	public static function is_comparing( $product_id ) {
		return in_array( (int) $product_id, self::get_compared(), true );
	}

	/**
	 * Get count.
	 */
	public static function count() {
		return count( self::get_compared() );
	}

	/**
	 * Clear comparison.
	 */
	public static function clear() {
		self::save( array() );
	}

	/**
	 * Get full product data for comparison.
	 *
	 * @return array
	 */
	public static function get_comparison_data() {
		$ids = self::get_compared();
		if ( empty( $ids ) ) {
			return array();
		}
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_products';
		$meta  = $wpdb->prefix . 'ah_ecommerce_product_meta';
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		$products = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id IN ({$placeholders})",
				...$ids
			)
		);

		$result = array();
		foreach ( $products as $product ) {
			$meta_rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT meta_key, meta_value FROM {$meta} WHERE product_id = %d",
					$product->id
				)
			);
			$product->meta = array();
			foreach ( $meta_rows as $m ) {
				$product->meta[ $m->meta_key ] = maybe_unserialize( $m->meta_value );
			}
			$result[] = $product;
		}
		return $result;
	}

	/**
	 * Get comparison attributes (columns to compare).
	 *
	 * @return array
	 */
	public static function get_compare_attributes() {
		return array(
			'Price'        => 'price',
			'Type'         => 'type',
			'SKU'          => 'sku',
			'Status'       => 'status',
			'Description'  => 'description',
			'Barcode'      => 'barcode',
			'GTIN'         => 'gtin',
			'Material'     => 'material',
			'Features'     => 'features',
			'Specifications' => 'specifications',
			'Ingredients'  => 'ingredients',
			'Highlights'   => 'highlights',
			'Weight'       => 'weight',
			'Dimensions'   => 'dimensions',
			'Brand'        => 'linked_brands',
			'Categories'   => 'linked_categories',
		);
	}

	// ── Private helpers ──

	private static function save( $products ) {
		if ( is_user_logged_in() ) {
			update_user_meta( get_current_user_id(), 'ah_compare', $products );
		} elseif ( session_status() === PHP_SESSION_ACTIVE ) {
			$_SESSION['ah_compare'] = $products;
		}
	}

	private static function get_db( $user_id ) {
		$data = get_user_meta( $user_id, 'ah_compare', true );
		return is_array( $data ) ? array_map( 'intval', $data ) : array();
	}

	private static function get_session() {
		if ( session_status() !== PHP_SESSION_ACTIVE ) {
			return array();
		}
		return isset( $_SESSION['ah_compare'] ) ? array_map( 'intval', $_SESSION['ah_compare'] ) : array();
	}
}
