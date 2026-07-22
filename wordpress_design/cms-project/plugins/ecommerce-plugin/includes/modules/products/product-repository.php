<?php
namespace AHEcommerce\Modules\Products;

use AHEcommerce\Core\Abstract_Repository;

/**
 * Repository for managing products.
 */
class Product_Repository extends Abstract_Repository {

	protected function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'ah_ecommerce_products';
	}

	public function get_paginated( $page = 1, $per_page = 20, $search = '', $search_columns = array() ) {
		return parent::get_paginated( $page, $per_page, $search, array( 'title', 'sku' ) );
	}

	/**
	 * Get the product meta table name.
	 */
	public function get_meta_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'ah_ecommerce_product_meta';
	}

	/**
	 * Get meta for a product.
	 */
	public function get_meta( $product_id, $key = '', $single = true ) {
		global $wpdb;
		$table = $this->get_meta_table_name();

		if ( $key ) {
			$meta = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT meta_value FROM {$table} WHERE product_id = %d AND meta_key = %s",
					$product_id,
					$key
				)
			);
			if ( empty( $meta ) ) {
				return $single ? '' : array();
			}
			if ( $single ) {
				return maybe_unserialize( $meta[0]->meta_value );
			}
			return array_map( function ( $m ) {
				return maybe_unserialize( $m->meta_value );
			}, $meta );
		}

		$meta   = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key, meta_value FROM {$table} WHERE product_id = %d",
				$product_id
			)
		);
		$result = array();
		foreach ( $meta as $m ) {
			$result[ $m->meta_key ] = maybe_unserialize( $m->meta_value );
		}
		return $result;
	}

	/**
	 * Update or insert a meta value.
	 */
	public function update_meta( $product_id, $key, $value ) {
		global $wpdb;
		$table = $this->get_meta_table_name();
		$value = maybe_serialize( $value );

		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_id FROM {$table} WHERE product_id = %d AND meta_key = %s",
				$product_id,
				$key
			)
		);

		if ( $exists ) {
			return $wpdb->update( $table, array( 'meta_value' => $value ), array( 'meta_id' => $exists ) );
		}
		return $wpdb->insert( $table, array(
			'product_id' => $product_id,
			'meta_key'   => $key,
			'meta_value' => $value,
		) );
	}

	/**
	 * Delete a meta value.
	 */
	public function delete_meta( $product_id, $key ) {
		global $wpdb;
		$table = $this->get_meta_table_name();
		return $wpdb->delete( $table, array(
			'product_id' => $product_id,
			'meta_key'   => $key,
		) );
	}

	/**
	 * Get a product with its meta attached.
	 */
	public function get( $id ) {
		$product = parent::get( $id );
		if ( $product ) {
			$product->meta = $this->get_meta( $id );
		}
		return $product;
	}
}
