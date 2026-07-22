<?php
namespace AHEcommerce\Modules\Orders;

use AHEcommerce\Core\Abstract_Repository;

/**
 * Repository for managing orders.
 */
class Order_Repository extends Abstract_Repository {

	protected function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'ah_ecommerce_orders';
	}

	public function get_paginated( $page = 1, $per_page = 20, $search = '', $search_columns = array() ) {
		return parent::get_paginated( $page, $per_page, $search, array( 'guest_email', 'billing_first_name', 'billing_last_name' ) );
	}
}
