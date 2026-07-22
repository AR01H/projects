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

			// ── Core Tables ──────────────────────────────────────────

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
				coupon_code varchar(100) DEFAULT NULL,
				status varchar(20) NOT NULL DEFAULT 'pending',
				subtotal decimal(19,4) NOT NULL DEFAULT 0.0000,
				tax_total decimal(19,4) NOT NULL DEFAULT 0.0000,
				shipping_total decimal(19,4) NOT NULL DEFAULT 0.0000,
				discount_total decimal(19,4) NOT NULL DEFAULT 0.0000,
				total decimal(19,4) NOT NULL DEFAULT 0.0000,
				notes text DEFAULT NULL,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY customer_id (customer_id),
				KEY status (status),
				KEY guest_email (guest_email(191))
			) $charset_collate;",

			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_order_items (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				order_id bigint(20) unsigned NOT NULL,
				product_id bigint(20) unsigned NOT NULL,
				quantity int(11) NOT NULL DEFAULT 1,
				price decimal(19,4) NOT NULL DEFAULT 0.0000,
				total decimal(19,4) NOT NULL DEFAULT 0.0000,
				PRIMARY KEY  (id),
				KEY order_id (order_id),
				KEY product_id (product_id)
			) $charset_collate;",

			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_customers (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				wp_user_id bigint(20) unsigned DEFAULT NULL,
				first_name varchar(100) DEFAULT NULL,
				last_name varchar(100) DEFAULT NULL,
				email varchar(255) NOT NULL,
				phone varchar(50) DEFAULT NULL,
				total_spent decimal(19,4) NOT NULL DEFAULT 0.0000,
				order_count int(11) NOT NULL DEFAULT 0,
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
				minimum_spend decimal(19,4) NOT NULL DEFAULT 0.0000,
				expiry_date datetime DEFAULT NULL,
				bogo_buy_qty int(11) DEFAULT NULL,
				bogo_get_qty int(11) DEFAULT NULL,
				bogo_get_discount decimal(5,2) DEFAULT NULL,
				tiered_rules longtext DEFAULT NULL,
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
			) $charset_collate;",

			// ── Feature Tables ────────────────────────────────────────

			// Sales & Promotions.
			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_sales (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				product_id bigint(20) unsigned NOT NULL,
				sale_price decimal(19,4) NOT NULL DEFAULT 0.0000,
				start_date datetime NOT NULL,
				end_date datetime NOT NULL,
				status varchar(20) NOT NULL DEFAULT 'scheduled',
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY product_id (product_id),
				KEY status (status),
				KEY start_date (start_date),
				KEY end_date (end_date)
			) $charset_collate;",

			// Reviews.
			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_reviews (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				product_id bigint(20) unsigned NOT NULL,
				reviewer_name varchar(100) NOT NULL,
				reviewer_email varchar(255) DEFAULT NULL,
				rating tinyint(1) NOT NULL DEFAULT 5,
				comment text DEFAULT NULL,
				image_url varchar(500) DEFAULT NULL,
				status varchar(20) NOT NULL DEFAULT 'pending',
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY product_id (product_id),
				KEY status (status)
			) $charset_collate;",

			// Wishlist.
			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_wishlist (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				product_id bigint(20) unsigned NOT NULL,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				UNIQUE KEY user_product (user_id, product_id),
				KEY user_id (user_id),
				KEY product_id (product_id)
			) $charset_collate;",

			// Shipping Zones.
			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_shipping_zones (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(255) NOT NULL,
				regions longtext DEFAULT NULL,
				sort_order int(11) NOT NULL DEFAULT 0,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;",

			// Shipping Methods.
			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_shipping_methods (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				zone_id bigint(20) unsigned NOT NULL,
				method_type varchar(50) NOT NULL DEFAULT 'flat_rate',
				method_title varchar(255) NOT NULL,
				cost decimal(19,4) NOT NULL DEFAULT 0.0000,
				min_order decimal(19,4) NOT NULL DEFAULT 0.0000,
				max_order decimal(19,4) NOT NULL DEFAULT 0.0000,
				settings longtext DEFAULT NULL,
				enabled tinyint(1) NOT NULL DEFAULT 1,
				PRIMARY KEY  (id),
				KEY zone_id (zone_id)
			) $charset_collate;",

			// Tax Rules.
			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_tax_rules (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(255) NOT NULL,
				rate decimal(19,4) NOT NULL DEFAULT 0.0000,
				type varchar(20) NOT NULL DEFAULT 'percent',
				country varchar(10) DEFAULT NULL,
				state varchar(100) DEFAULT NULL,
				postcode varchar(50) DEFAULT NULL,
				city varchar(100) DEFAULT NULL,
				apply_to varchar(20) NOT NULL DEFAULT 'shipping',
				status varchar(20) NOT NULL DEFAULT 'active',
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) $charset_collate;",

			// Abandoned Carts.
			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_abandoned_carts (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				email varchar(255) NOT NULL,
				cart_data longtext DEFAULT NULL,
				cart_total decimal(19,4) NOT NULL DEFAULT 0.0000,
				status varchar(20) NOT NULL DEFAULT 'pending',
				reminder_count int(11) NOT NULL DEFAULT 0,
				last_reminder datetime DEFAULT NULL,
				recovered_at datetime DEFAULT NULL,
				last_activity datetime DEFAULT NULL,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY email (email(191)),
				KEY status (status)
			) $charset_collate;",

			// Price Rules (wholesale, role-based).
			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_price_rules (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(255) NOT NULL,
				rule_type varchar(50) NOT NULL DEFAULT 'bulk',
				product_id bigint(20) unsigned DEFAULT NULL,
				min_qty int(11) NOT NULL DEFAULT 0,
				max_qty int(11) DEFAULT NULL,
				discount_type varchar(20) NOT NULL DEFAULT 'percent',
				discount_value decimal(19,4) NOT NULL DEFAULT 0.0000,
				user_role varchar(50) DEFAULT NULL,
				start_date datetime DEFAULT NULL,
				end_date datetime DEFAULT NULL,
				status varchar(20) NOT NULL DEFAULT 'active',
				priority int(11) NOT NULL DEFAULT 10,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY product_id (product_id),
				KEY status (status)
			) $charset_collate;",

			// Email Log.
			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_email_log (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				to_email varchar(255) NOT NULL,
				subject varchar(500) NOT NULL,
				template varchar(100) DEFAULT NULL,
				status varchar(20) NOT NULL DEFAULT 'sent',
				sent_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY to_email (to_email(191))
			) $charset_collate;",

			// Stock Alerts ("Notify when back in stock").
			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_stock_alerts (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				product_id bigint(20) unsigned NOT NULL,
				email varchar(255) NOT NULL,
				status varchar(20) NOT NULL DEFAULT 'pending',
				notified_at datetime DEFAULT NULL,
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY product_id (product_id),
				KEY email (email(191)),
				KEY status (status)
			) $charset_collate;",

			// Product Q&A — Questions.
			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_product_questions (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				product_id bigint(20) unsigned NOT NULL,
				questioner_name varchar(100) NOT NULL,
				questioner_email varchar(255) NOT NULL,
				question text NOT NULL,
				status varchar(20) NOT NULL DEFAULT 'pending',
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY product_id (product_id),
				KEY status (status)
			) $charset_collate;",

			// Product Q&A — Answers.
			"CREATE TABLE {$wpdb->prefix}ah_ecommerce_product_answers (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				question_id bigint(20) unsigned NOT NULL,
				answerer_name varchar(100) NOT NULL,
				answerer_email varchar(255) DEFAULT NULL,
				answer text NOT NULL,
				is_admin tinyint(1) NOT NULL DEFAULT 0,
				status varchar(20) NOT NULL DEFAULT 'approved',
				created_at datetime DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id),
				KEY question_id (question_id)
			) $charset_collate;",
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

		update_option( 'ah_ecommerce_db_version', '1.1.0' );
	}

	/**
	 * Safely update the schema without dropping tables.
	 */
	public static function update_schema() {
		self::install();
	}
}
