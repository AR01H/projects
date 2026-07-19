<?php
namespace AHEcommerce\Database;

/**
 * Manages the raw MySQL database schema for the Ecommerce Platform.
 */
class Schema {

	/**
	 * Get the complete array of table schema definitions.
	 */
	private static function get_schema() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		return array(
			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_products (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				title varchar(255) NOT NULL,
				description longtext DEFAULT NULL,
				type varchar(50) NOT NULL DEFAULT 'simple',
				sku varchar(100) DEFAULT NULL,
				price decimal(19,4) DEFAULT NULL,
				status varchar(20) NOT NULL DEFAULT 'draft',
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY sku (sku)
			) $charset_collate;",
			
			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_product_meta (
				meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				product_id bigint(20) unsigned NOT NULL DEFAULT '0',
				meta_key varchar(255) DEFAULT NULL,
				meta_value longtext,
				PRIMARY KEY  (meta_id),
				KEY product_id (product_id),
				KEY meta_key (meta_key(191))
			) $charset_collate;",
			
			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_orders (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				customer_id bigint(20) unsigned DEFAULT NULL,
				guest_email varchar(255) DEFAULT NULL,
				guest_phone varchar(50) DEFAULT NULL,
				billing_first_name varchar(100) DEFAULT NULL,
				billing_last_name varchar(100) DEFAULT NULL,
				billing_address text DEFAULT NULL,
				billing_city varchar(100) DEFAULT NULL,
				billing_state varchar(100) DEFAULT NULL,
				billing_postcode varchar(50) DEFAULT NULL,
				billing_country varchar(100) DEFAULT NULL,
				shipping_first_name varchar(100) DEFAULT NULL,
				shipping_last_name varchar(100) DEFAULT NULL,
				shipping_address text DEFAULT NULL,
				shipping_city varchar(100) DEFAULT NULL,
				shipping_state varchar(100) DEFAULT NULL,
				shipping_postcode varchar(50) DEFAULT NULL,
				shipping_country varchar(100) DEFAULT NULL,
				payment_method varchar(100) DEFAULT NULL,
				status varchar(20) NOT NULL DEFAULT 'pending',
				subtotal decimal(19,4) NOT NULL DEFAULT 0.0000,
				tax_total decimal(19,4) NOT NULL DEFAULT 0.0000,
				shipping_total decimal(19,4) NOT NULL DEFAULT 0.0000,
				discount_total decimal(19,4) NOT NULL DEFAULT 0.0000,
				total decimal(19,4) NOT NULL DEFAULT 0.0000,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY customer_id (customer_id)
			) $charset_collate;",
			
			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_customers (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				wp_user_id bigint(20) unsigned DEFAULT NULL,
				first_name varchar(100) DEFAULT NULL,
				last_name varchar(100) DEFAULT NULL,
				email varchar(255) NOT NULL,
				phone varchar(50) DEFAULT NULL,
				total_spent decimal(19,4) NOT NULL DEFAULT 0.0000,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY wp_user_id (wp_user_id),
				KEY email (email(191))
			) $charset_collate;",
			
			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_coupons (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				code varchar(100) NOT NULL,
				discount_type varchar(50) NOT NULL DEFAULT 'percent',
				amount decimal(19,4) NOT NULL DEFAULT 0.0000,
				usage_limit int(11) DEFAULT NULL,
				usage_count int(11) NOT NULL DEFAULT 0,
				expiry_date datetime DEFAULT NULL,
				status varchar(20) NOT NULL DEFAULT 'active',
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				UNIQUE KEY code (code)
			) $charset_collate;",

			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_categories (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(255) NOT NULL,
				slug varchar(255) NOT NULL,
				parent_id bigint(20) unsigned DEFAULT 0,
				description longtext DEFAULT NULL,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				UNIQUE KEY slug (slug(191)),
				KEY parent_id (parent_id)
			) $charset_collate;",

			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_sellers (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				store_name varchar(255) NOT NULL,
				store_slug varchar(255) NOT NULL,
				commission_rate decimal(5,2) DEFAULT 10.00,
				wallet_balance decimal(19,4) NOT NULL DEFAULT 0.0000,
				status varchar(20) NOT NULL DEFAULT 'pending',
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				UNIQUE KEY store_slug (store_slug(191)),
				KEY user_id (user_id)
			) $charset_collate;"
		);
	}

	/**
	 * Install or update the database tables.
	 */
	public static function install() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		
		$schemas = self::get_schema();
		foreach ( $schemas as $schema ) {
			dbDelta( $schema );
		}
	}

	/**
	 * Safely update the schema without dropping tables.
	 */
	public static function update_schema() {
		self::install();
	}
}
