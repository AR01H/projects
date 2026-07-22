<?php
namespace AHEcommerce\Modules\Categories;

use AHEcommerce\Core\Abstract_Repository;

class Category_Repository extends Abstract_Repository {

	protected function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'ah_ecommerce_categories';
	}

	public function get_paginated( $page = 1, $per_page = 20, $search = '', $search_columns = array() ) {
		return parent::get_paginated( $page, $per_page, $search, array( 'name', 'slug' ) );
	}
}
