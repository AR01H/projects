<?php
namespace AHEcommerce\Database;

/**
 * Repository for managing orders in the custom ah_ecommerce_orders table.
 */
class Order_Repository extends Abstract_Repository {

	protected function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'ah_ecommerce_orders';
	}

	/**
	 * Get paginated list of orders for the Admin UI.
	 */
	public function get_paginated( $page = 1, $per_page = 20, $search = '' ) {
		global $wpdb;
		$table = $this->get_table_name();
		
		$where = '1=1';
		$args = array();
		
		if ( ! empty( $search ) ) {
			// Search by guest email or billing name
			$where .= ' AND (guest_email LIKE %s OR billing_first_name LIKE %s OR billing_last_name LIKE %s)';
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$args[] = $like;
			$args[] = $like;
			$args[] = $like;
		}
		
		$offset = ( $page - 1 ) * $per_page;
		
		$query = "SELECT SQL_CALC_FOUND_ROWS * FROM {$table} WHERE {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d";
		$args[] = $per_page;
		$args[] = $offset;
		
		if ( ! empty( $args ) ) {
			$query = $wpdb->prepare( $query, ...$args );
		}
		
		$items = $wpdb->get_results( $query );
		$total = (int) $wpdb->get_var( "SELECT FOUND_ROWS()" );
		
		return array(
			'items' => $items,
			'meta'  => array(
				'current_page' => $page,
				'per_page'     => $per_page,
				'total_items'  => $total,
				'total_pages'  => ceil( $total / $per_page ),
			)
		);
	}
}
