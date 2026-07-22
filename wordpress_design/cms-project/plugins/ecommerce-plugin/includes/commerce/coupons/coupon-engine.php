<?php
namespace AHEcommerce\Commerce\Coupons;

/**
 * Advanced coupon engine — validates and applies coupon discounts.
 * Supports: percent, fixed, BOGO, tiered/bulk, free shipping, expiry.
 */
class Coupon_Engine {

	/**
	 * Validate a coupon code.
	 *
	 * @return array{valid: bool, message: string, coupon: ?object}
	 */
	public static function validate( $code, $cart_total = 0, $cart_items = array() ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_coupons';

		$coupon = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE code = %s",
				strtoupper( trim( $code ) )
			)
		);

		if ( ! $coupon ) {
			return array( 'valid' => false, 'message' => 'Coupon not found.', 'coupon' => null );
		}

		if ( $coupon->status !== 'active' ) {
			return array( 'valid' => false, 'message' => 'This coupon is inactive.', 'coupon' => null );
		}

		if ( $coupon->expiry_date && strtotime( $coupon->expiry_date ) < time() ) {
			return array( 'valid' => false, 'message' => 'This coupon has expired.', 'coupon' => null );
		}

		if ( $coupon->usage_limit !== null && $coupon->usage_count >= $coupon->usage_limit ) {
			return array( 'valid' => false, 'message' => 'This coupon usage limit has been reached.', 'coupon' => null );
		}

		// Minimum spend check.
		$min_spend = (float) ( $coupon->minimum_spend ?? 0 );
		if ( $min_spend > 0 && $cart_total < $min_spend ) {
			return array(
				'valid'  => false,
				'message' => sprintf( 'Minimum spend of $%s required.', number_format( $min_spend, 2 ) ),
				'coupon' => null,
			);
		}

		return array( 'valid' => true, 'message' => 'Coupon applied.', 'coupon' => $coupon );
	}

	/**
	 * Calculate the discount amount for a coupon.
	 *
	 * @param object $coupon     The coupon row.
	 * @param float  $cart_total Current cart total.
	 * @param array  $cart_items Cart items [{product_id, qty, price}].
	 * @return array{discount: float, discount_type: string, label: string}
	 */
	public static function calculate_discount( $coupon, $cart_total, $cart_items = array() ) {
		$discount    = 0;
		$label       = '';
		$raw_type    = $coupon->discount_type;

		switch ( $raw_type ) {
			case 'percent':
				$discount = $cart_total * ( (float) $coupon->amount / 100 );
				$label    = $coupon->amount . '% off';
				break;

			case 'fixed_cart':
				$discount = min( (float) $coupon->amount, $cart_total );
				$label    = '$' . number_format( (float) $coupon->amount, 2 ) . ' off';
				break;

			case 'fixed_product':
				foreach ( $cart_items as $item ) {
					$discount += min( (float) $coupon->amount, (float) $item['price'] ) * (int) $item['qty'];
				}
				$label = '$' . number_format( (float) $coupon->amount, 2 ) . ' off per item';
				break;

			case 'free_shipping':
				$discount = 0;
				$label    = 'Free shipping';
				break;

			case 'bogo':
				// Buy X Get Y — discount applies to cheapest qualifying items.
				$buy_qty    = max( 1, (int) ( $coupon->bogo_buy_qty ?? 2 ) );
				$get_qty    = max( 1, (int) ( $coupon->bogo_get_qty ?? 1 ) );
				$get_discount = (float) ( $coupon->bogo_get_discount ?? 100 );
				$total_items = 0;
				foreach ( $cart_items as $item ) {
					$total_items += (int) $item['qty'];
				}
				$sets  = (int) floor( $total_items / ( $buy_qty + $get_qty ) );
				$free  = $sets * $get_qty;
				if ( $free > 0 ) {
					// Apply discount to cheapest items.
					$prices = array();
					foreach ( $cart_items as $item ) {
						for ( $i = 0; $i < (int) $item['qty']; $i++ ) {
							$prices[] = (float) $item['price'];
						}
					}
					sort( $prices );
					for ( $i = 0; $i < $free && $i < count( $prices ); $i++ ) {
						$discount += $prices[ $i ] * ( $get_discount / 100 );
					}
				}
				$label = sprintf( 'Buy %d Get %d at %s%% off', $buy_qty, $get_qty, $get_discount );
				break;

			case 'tiered':
				// Tiered discount based on cart total.
				$tiers = maybe_unserialize( $coupon->tiered_rules ?? '' );
				if ( is_array( $tiers ) ) {
					krsort( $tiers );
					foreach ( $tiers as $min_total => $tier_discount ) {
						if ( $cart_total >= (float) $min_total ) {
							$discount = $cart_total * ( (float) $tier_discount / 100 );
							$label    = $tier_discount . '% off (tiered)';
							break;
						}
					}
				}
				break;
		}

		return array(
			'discount'      => round( max( 0, $discount ), 2 ),
			'discount_type' => $raw_type,
			'label'         => $label,
		);
	}

	/**
	 * Record coupon usage (increment count).
	 */
	public static function record_usage( $coupon_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_coupons';
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET usage_count = usage_count + 1 WHERE id = %d",
				$coupon_id
			)
		);
	}

	/**
	 * Create a new coupon.
	 *
	 * @return int|false
	 */
	public static function create_coupon( $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_coupons';
		$wpdb->insert( $table, array(
			'code'           => strtoupper( sanitize_text_field( $data['code'] ) ),
			'discount_type'  => sanitize_key( $data['discount_type'] ),
			'amount'         => (float) $data['amount'],
			'usage_limit'    => isset( $data['usage_limit'] ) ? (int) $data['usage_limit'] : null,
			'expiry_date'    => sanitize_text_field( $data['expiry_date'] ?? '' ),
			'minimum_spend'  => (float) ( $data['minimum_spend'] ?? 0 ),
			'bogo_buy_qty'   => isset( $data['bogo_buy_qty'] ) ? (int) $data['bogo_buy_qty'] : null,
			'bogo_get_qty'   => isset( $data['bogo_get_qty'] ) ? (int) $data['bogo_get_qty'] : null,
			'bogo_get_discount' => isset( $data['bogo_get_discount'] ) ? (float) $data['bogo_get_discount'] : null,
			'tiered_rules'   => isset( $data['tiered_rules'] ) ? maybe_serialize( $data['tiered_rules'] ) : null,
			'status'         => 'active',
			'created_at'     => current_time( 'mysql' ),
		) );
		return $wpdb->insert_id;
	}

	/**
	 * Get all coupons (admin listing).
	 */
	public static function get_coupons( $page = 1, $per_page = 20 ) {
		global $wpdb;
		$table  = $wpdb->prefix . 'ah_ecommerce_coupons';
		$offset = ( $page - 1 ) * $per_page;

		$items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
				$per_page,
				$offset
			)
		);

		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );

		return array(
			'items' => $items,
			'meta'  => array(
				'current_page' => $page,
				'per_page'     => $per_page,
				'total_items'  => $total,
				'total_pages'  => ceil( $total / $per_page ),
			),
		);
	}

	/**
	 * Delete a coupon.
	 */
	public static function delete_coupon( $coupon_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_coupons';
		return $wpdb->delete( $table, array( 'id' => $coupon_id ) );
	}
}
