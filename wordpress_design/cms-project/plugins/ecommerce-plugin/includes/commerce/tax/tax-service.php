<?php
namespace AHEcommerce\Commerce\Tax;

/**
 * Manages tax rules and calculates tax on orders.
 */
class Tax_Service {

	/**
	 * Create a tax rule.
	 *
	 * @param array $data {name, rate, type, country, state, postcode, city, apply_to}
	 * @return int|false
	 */
	public static function create_rule( $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_tax_rules';
		$wpdb->insert( $table, array(
			'name'       => sanitize_text_field( $data['name'] ),
			'rate'       => (float) $data['rate'],
			'type'       => sanitize_key( $data['type'] ?? 'percent' ),
			'country'    => sanitize_text_field( $data['country'] ?? '' ),
			'state'      => sanitize_text_field( $data['state'] ?? '' ),
			'postcode'   => sanitize_text_field( $data['postcode'] ?? '' ),
			'city'       => sanitize_text_field( $data['city'] ?? '' ),
			'apply_to'   => sanitize_key( $data['apply_to'] ?? 'shipping' ),
			'status'     => 'active',
			'created_at' => current_time( 'mysql' ),
		) );
		return $wpdb->insert_id;
	}

	/**
	 * Get all tax rules.
	 */
	public static function get_rules() {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_tax_rules';
		return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY name ASC" );
	}

	/**
	 * Calculate applicable tax for an order.
	 *
	 * @param float  $subtotal       Order subtotal.
	 * @param float  $shipping_total Shipping cost.
	 * @param string $country        Billing country code.
	 * @param string $state          Billing state code.
	 * @param string $postcode       Billing postcode.
	 * @return array{tax_total: float, tax_details: array}
	 */
	public static function calculate_tax( $subtotal, $shipping_total, $country = '', $state = '', $postcode = '' ) {
		$rules     = self::get_rules();
		$tax_total = 0;
		$details   = array();

		foreach ( $rules as $rule ) {
			if ( $rule->status !== 'active' ) {
				continue;
			}

			// Location matching.
			if ( $rule->country && strtoupper( $rule->country ) !== strtoupper( $country ) ) {
				continue;
			}
			if ( $rule->state && strtoupper( $rule->state ) !== strtoupper( $state ) ) {
				continue;
			}
			if ( $rule->postcode && $rule->postcode !== $postcode ) {
				continue;
			}

			// Calculate tax.
			$base = ( $rule->apply_to === 'shipping' ) ? $shipping_total : $subtotal;
			$tax  = 0;

			if ( $rule->type === 'percent' ) {
				$tax = $base * ( $rule->rate / 100 );
			} else {
				$tax = $rule->rate;
			}

			$tax_total += $tax;
			$details[]  = array(
				'name' => $rule->name,
				'rate' => $rule->rate,
				'type' => $rule->type,
				'tax'  => round( $tax, 2 ),
			);
		}

		return array(
			'tax_total'   => round( $tax_total, 2 ),
			'tax_details' => $details,
		);
	}

	/**
	 * Update a tax rule.
	 */
	public static function update_rule( $rule_id, $data ) {
		global $wpdb;
		$table  = $wpdb->prefix . 'ah_ecommerce_tax_rules';
		$update = array();
		$fields = array( 'name', 'rate', 'type', 'country', 'state', 'postcode', 'city', 'apply_to', 'status' );
		foreach ( $fields as $field ) {
			if ( isset( $data[ $field ] ) ) {
				$update[ $field ] = is_numeric( $data[ $field ] ) ? (float) $data[ $field ] : sanitize_text_field( $data[ $field ] );
			}
		}
		return $wpdb->update( $table, $update, array( 'id' => $rule_id ) );
	}

	/**
	 * Delete a tax rule.
	 */
	public static function delete_rule( $rule_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_tax_rules';
		return $wpdb->delete( $table, array( 'id' => $rule_id ) );
	}
}
