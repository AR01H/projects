<?php
namespace AHEcommerce\Database;

/**
 * Repository for managing products in the custom ah_ecommerce_products table.
 */
class Product_Repository extends Abstract_Repository {

	protected function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'ah_ecommerce_products';
	}

	/**
	 * Get paginated list of products for the Admin UI.
	 */
	public function get_paginated( $page = 1, $per_page = 20, $search = '' ) {
		global $wpdb;
		$table = $this->get_table_name();
		
		$where = '1=1';
		$args = array();
		
		if ( ! empty( $search ) ) {
			$where .= ' AND (title LIKE %s OR sku LIKE %s)';
			$like = '%' . $wpdb->esc_like( $search ) . '%';
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

	public function get_meta_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'ah_ecommerce_product_meta';
	}

	public function get_meta( $product_id, $key = '', $single = true ) {
		global $wpdb;
		$table = $this->get_meta_table_name();
		
		if ( $key ) {
			$meta = $wpdb->get_results( $wpdb->prepare( "SELECT meta_value FROM {$table} WHERE product_id = %d AND meta_key = %s", $product_id, $key ) );
			if ( empty( $meta ) ) return $single ? '' : array();
			
			if ( $single ) {
				return maybe_unserialize( $meta[0]->meta_value );
			}
			return array_map( function($m) { return maybe_unserialize($m->meta_value); }, $meta );
		}
		
		$meta = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$table} WHERE product_id = %d", $product_id ) );
		$result = array();
		foreach ( $meta as $m ) {
			$result[$m->meta_key] = maybe_unserialize( $m->meta_value );
		}
		return $result;
	}
	
	public function update_meta( $product_id, $key, $value ) {
		global $wpdb;
		$table = $this->get_meta_table_name();
		
		$value = maybe_serialize( $value );
		
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT meta_id FROM {$table} WHERE product_id = %d AND meta_key = %s", $product_id, $key ) );
		if ( $exists ) {
			return $wpdb->update( $table, array( 'meta_value' => $value ), array( 'meta_id' => $exists ) );
		}
		return $wpdb->insert( $table, array( 'product_id' => $product_id, 'meta_key' => $key, 'meta_value' => $value ) );
	}
	
	public function delete_meta( $product_id, $key ) {
		global $wpdb;
		$table = $this->get_meta_table_name();
		return $wpdb->delete( $table, array( 'product_id' => $product_id, 'meta_key' => $key ) );
	}
	
	public function get( $id ) {
		$product = parent::get( $id );
		if ( $product ) {
			$product->meta = $this->get_meta( $id );
		}
		return $product;
	}
}
