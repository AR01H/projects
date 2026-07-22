<?php
namespace AHEcommerce\Commerce\Shipping;

/**
 * Manages shipping zones, methods, and rate calculation.
 */
class Shipping_Service {

	// --- Zones ---

	/**
	 * Create a shipping zone.
	 *
	 * @return int|false
	 */
	public static function create_zone( $name, $regions = array() ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_shipping_zones';
		$wpdb->insert( $table, array(
			'name'       => sanitize_text_field( $name ),
			'regions'    => maybe_serialize( $regions ),
			'sort_order' => 0,
			'created_at' => current_time( 'mysql' ),
		) );
		return $wpdb->insert_id;
	}

	/**
	 * Get all shipping zones.
	 */
	public static function get_zones() {
		global $wpdb;
		$table  = $wpdb->prefix . 'ah_ecommerce_shipping_zones';
		$zones  = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY sort_order ASC" );
		foreach ( $zones as &$zone ) {
			$zone->regions = maybe_unserialize( $zone->regions );
		}
		return $zones;
	}

	/**
	 * Update a shipping zone.
	 */
	public static function update_zone( $zone_id, $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_shipping_zones';
		$update = array();
		if ( isset( $data['name'] ) ) {
			$update['name'] = sanitize_text_field( $data['name'] );
		}
		if ( isset( $data['regions'] ) ) {
			$update['regions'] = maybe_serialize( $data['regions'] );
		}
		if ( isset( $data['sort_order'] ) ) {
			$update['sort_order'] = (int) $data['sort_order'];
		}
		return $wpdb->update( $table, $update, array( 'id' => $zone_id ) );
	}

	/**
	 * Delete a shipping zone and its methods.
	 */
	public static function delete_zone( $zone_id ) {
		global $wpdb;
		$wpdb->delete( $wpdb->prefix . 'ah_ecommerce_shipping_methods', array( 'zone_id' => $zone_id ) );
		return $wpdb->delete( $wpdb->prefix . 'ah_ecommerce_shipping_zones', array( 'id' => $zone_id ) );
	}

	// --- Methods ---

	/**
	 * Add a shipping method to a zone.
	 *
	 * @param array $data {zone_id, method_type, method_title, cost, min_order, max_order, settings}
	 * @return int|false
	 */
	public static function add_method( $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_shipping_methods';
		$wpdb->insert( $table, array(
			'zone_id'      => (int) $data['zone_id'],
			'method_type'  => sanitize_key( $data['method_type'] ),
			'method_title' => sanitize_text_field( $data['method_title'] ),
			'cost'         => (float) ( $data['cost'] ?? 0 ),
			'min_order'    => (float) ( $data['min_order'] ?? 0 ),
			'max_order'    => (float) ( $data['max_order'] ?? 0 ),
			'settings'     => maybe_serialize( $data['settings'] ?? array() ),
			'enabled'      => 1,
		) );
		return $wpdb->insert_id;
	}

	/**
	 * Get methods for a zone.
	 */
	public static function get_methods( $zone_id ) {
		global $wpdb;
		$table   = $wpdb->prefix . 'ah_ecommerce_shipping_methods';
		$methods = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE zone_id = %d ORDER BY id ASC",
				$zone_id
			)
		);
		foreach ( $methods as &$m ) {
			$m->settings = maybe_unserialize( $m->settings );
		}
		return $methods;
	}

	/**
	 * Calculate shipping rates for a given zone and cart total.
	 *
	 * @return array [{method_title, cost, method_type}]
	 */
	public static function calculate_rates( $zone_id, $cart_total = 0 ) {
		$methods = self::get_methods( $zone_id );
		$rates   = array();

		foreach ( $methods as $method ) {
			if ( ! $method->enabled ) {
				continue;
			}
			if ( $method->min_order > 0 && $cart_total < $method->min_order ) {
				continue;
			}
			if ( $method->max_order > 0 && $cart_total > $method->max_order ) {
				continue;
			}
			$rates[] = array(
				'method_title' => $method->method_title,
				'cost'         => (float) $method->cost,
				'method_type'  => $method->method_type,
			);
		}

		return $rates;
	}

	/**
	 * Delete a shipping method.
	 */
	public static function delete_method( $method_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_shipping_methods';
		return $wpdb->delete( $table, array( 'id' => $method_id ) );
	}
}
