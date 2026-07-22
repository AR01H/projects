<?php
namespace AHEcommerce\Commerce\Recommendations;

/**
 * Provides product recommendations: related, upsells, cross-sells, and "frequently bought together".
 * All data comes from product meta — fully theme-independent.
 */
class Recommendation_Service {

	/**
	 * Get related products (by shared categories or tags).
	 *
	 * @param int $product_id Current product ID.
	 * @param int $limit      Max results.
	 * @return array
	 */
	public static function get_related( $product_id, $limit = 4 ) {
		global $wpdb;
		$products = $wpdb->prefix . 'ah_ecommerce_products';
		$meta     = $wpdb->prefix . 'ah_ecommerce_product_meta';

		// Get this product's categories and tags.
		$cat_ids = self::get_meta_ids( $product_id, 'linked_categories' );
		$tag_ids = self::get_meta_ids( $product_id, 'linked_tags' );

		if ( empty( $cat_ids ) && empty( $tag_ids ) ) {
			return array();
		}

		$conditions = array();
		$args       = array();

		if ( ! empty( $cat_ids ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $cat_ids ), '%d' ) );
			$conditions[] = "m_cat.meta_value IN ({$placeholders})";
			$args         = array_merge( $args, $cat_ids );
		}
		if ( ! empty( $tag_ids ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $tag_ids ), '%d' ) );
			$conditions[] = "m_tag.meta_value IN ({$placeholders})";
			$args         = array_merge( $args, $tag_ids );
		}

		$where = implode( ' OR ', $conditions );
		$args[] = $product_id;
		$args[] = $limit;

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT p.* FROM {$products} p
				LEFT JOIN {$meta} m_cat ON p.id = m_cat.product_id AND m_cat.meta_key = 'linked_categories'
				LEFT JOIN {$meta} m_tag ON p.id = m_tag.product_id AND m_tag.meta_key = 'linked_tags'
				WHERE ({$where})
				AND p.id != %d
				AND p.status = 'published'
				ORDER BY RAND()
				LIMIT %d",
				...$args
			)
		);
	}

	/**
	 * Get upsell products.
	 */
	public static function get_upsells( $product_id ) {
		return self::get_linked_products( $product_id, 'linked_upsells' );
	}

	/**
	 * Get cross-sell products.
	 */
	public static function get_cross_sells( $product_id ) {
		return self::get_linked_products( $product_id, 'linked_crosssells' );
	}

	/**
	 * Get recently viewed products (from session/cookie).
	 *
	 * @param int $limit Max results.
	 * @return array
	 */
	public static function get_recently_viewed( $limit = 8 ) {
		$viewed = array();

		if ( is_user_logged_in() ) {
			$viewed = get_user_meta( get_current_user_id(), 'ah_recently_viewed', true );
		} elseif ( isset( $_COOKIE['ah_recently_viewed'] ) ) {
			$viewed = json_decode( sanitize_text_field( wp_unslash( $_COOKIE['ah_recently_viewed'] ) ), true );
		}

		if ( ! is_array( $viewed ) || empty( $viewed ) ) {
			return array();
		}

		$viewed = array_unique( array_filter( array_map( 'intval', $viewed ) ) );
		$viewed = array_slice( $viewed, 0, $limit );

		if ( empty( $viewed ) ) {
			return array();
		}

		global $wpdb;
		$table       = $wpdb->prefix . 'ah_ecommerce_products';
		$placeholders = implode( ',', array_fill( 0, count( $viewed ), '%d' ) );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id IN ({$placeholders}) AND status = 'published'",
				...$viewed
			)
		);
	}

	/**
	 * Track a product view (adds to recently viewed list).
	 */
	public static function track_view( $product_id ) {
		$product_id = (int) $product_id;
		if ( $product_id <= 0 ) {
			return;
		}

		if ( is_user_logged_in() ) {
			$viewed = get_user_meta( get_current_user_id(), 'ah_recently_viewed', true );
			if ( ! is_array( $viewed ) ) {
				$viewed = array();
			}
			$viewed = array_filter( array_merge( array( $product_id ), $viewed ) );
			$viewed = array_unique( array_map( 'intval', $viewed ) );
			$viewed = array_slice( $viewed, 0, 20 ); // Keep last 20.
			update_user_meta( get_current_user_id(), 'ah_recently_viewed', array_values( $viewed ) );
		} else {
			// "Recently viewed" is a personalization cookie, not strictly necessary for
			// checkout/cart to work - only write it once the visitor has granted the
			// theme's "analytics" cookie category (UK PECR requires consent for this
			// kind of tracking). Guests who haven't consented simply get no
			// recently-viewed history rather than an unconsented cookie.
			if ( ! function_exists( 'adn_visitor_has_cookie_category' ) || ! adn_visitor_has_cookie_category( 'analytics' ) ) {
				return;
			}

			$viewed = isset( $_COOKIE['ah_recently_viewed'] )
				? json_decode( sanitize_text_field( wp_unslash( $_COOKIE['ah_recently_viewed'] ) ), true )
				: array();
			if ( ! is_array( $viewed ) ) {
				$viewed = array();
			}
			$viewed = array_filter( array_merge( array( $product_id ), $viewed ) );
			$viewed = array_unique( array_map( 'intval', $viewed ) );
			$viewed = array_slice( $viewed, 0, 20 );
			setcookie( 'ah_recently_viewed', wp_json_encode( array_values( $viewed ) ), time() + ( 30 * DAY_IN_SECONDS ), '/' );
		}
	}

	/**
	 * Get best selling products.
	 *
	 * @param int $limit Max results.
	 * @return array
	 */
	public static function get_bestsellers( $limit = 8 ) {
		global $wpdb;
		$table  = $wpdb->prefix . 'ah_ecommerce_products';
		$orders = $wpdb->prefix . 'ah_ecommerce_orders';

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.*, COUNT(o.id) AS order_count
				FROM {$table} p
				INNER JOIN {$orders} o ON p.id = o.customer_id
				WHERE p.status = 'published'
				GROUP BY p.id
				ORDER BY order_count DESC
				LIMIT %d",
				$limit
			)
		);
	}

	// --- Helpers ---

	private static function get_linked_products( $product_id, $meta_key ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_product_meta';
		$meta  = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_value FROM {$table} WHERE product_id = %d AND meta_key = %s",
				$product_id,
				$meta_key
			)
		);

		if ( empty( $meta ) ) {
			return array();
		}

		$ids = array_filter( array_map( 'intval', explode( ',', maybe_unserialize( $meta ) ) ) );
		if ( empty( $ids ) ) {
			return array();
		}

		$products    = $wpdb->prefix . 'ah_ecommerce_products';
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$products} WHERE id IN ({$placeholders}) AND status = 'published'",
				...$ids
			)
		);
	}

	private static function get_meta_ids( $product_id, $meta_key ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_product_meta';
		$value = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_value FROM {$table} WHERE product_id = %d AND meta_key = %s",
				$product_id,
				$meta_key
			)
		);
		if ( empty( $value ) ) {
			return array();
		}
		return array_filter( array_map( 'intval', explode( ',', maybe_unserialize( $value ) ) ) );
	}
}
