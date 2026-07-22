<?php
namespace AHEcommerce\Modules\Sellers;

use AHEcommerce\Core\Abstract_Repository;

class Seller_Repository extends Abstract_Repository {

	protected function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'ah_ecommerce_sellers';
	}

	public function get_paginated( $page = 1, $per_page = 20, $search = '', $search_columns = array() ) {
		return parent::get_paginated( $page, $per_page, $search, array( 'store_name', 'store_slug' ) );
	}
}
