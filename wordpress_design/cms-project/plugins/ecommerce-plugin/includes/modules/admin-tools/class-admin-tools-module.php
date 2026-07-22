<?php
namespace AHEcommerce\Modules\Admin_Tools;

use AHEcommerce\Core\Abstract_Module;
use AHEcommerce\Database\Schema;

/**
 * Registers the admin menu and renders admin pages.
 */
class Admin_Tools_Module extends Abstract_Module {

	public function get_id() {
		return 'admin_tools';
	}

	public function get_name() {
		return 'Admin Tools';
	}

	public function boot() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'handle_actions' ) );
		add_action( 'wp_ajax_ah_admin_action', array( $this, 'handle_ajax_actions' ) );
	}

	public function add_menu_page() {
		add_menu_page(
			'CMS Ecommerce',
			'CMS Ecommerce',
			'manage_options',
			'cms_ecommerce',
			array( $this, 'render_page' ),
			'dashicons-store',
			56
		);

		add_submenu_page(
			'cms_ecommerce',
			'Dashboard',
			'Dashboard',
			'manage_options',
			'cms_ecommerce',
			array( $this, 'render_page' )
		);

		add_submenu_page(
			'cms_ecommerce',
			'Sellers',
			'Sellers',
			'manage_options',
			'cms_ecommerce_sellers',
			array( $this, 'render_page' )
		);

		// All submenu pages.
		$pages = array(
			'ah-orders'       => array( 'title' => 'Orders',           'pos' => 3 ),
			'ah-categories'   => array( 'title' => 'Categories',       'pos' => 4 ),
			'ah-customers'    => array( 'title' => 'Customers',        'pos' => 5 ),
			'ah-coupons'      => array( 'title' => 'Coupons',          'pos' => 6 ),
			'ah-pricing'      => array( 'title' => 'Pricing Rules',    'pos' => 7 ),
			'ah-inventory'    => array( 'title' => 'Inventory',        'pos' => 8 ),
			'ah-shipping'     => array( 'title' => 'Shipping',         'pos' => 9 ),
			'ah-tax'          => array( 'title' => 'Tax',              'pos' => 10 ),
			'ah-sales'        => array( 'title' => 'Sales & Promos',   'pos' => 11 ),
			'ah-reviews'      => array( 'title' => 'Reviews',          'pos' => 12 ),
			'ah-wishlist'     => array( 'title' => 'Wishlist',         'pos' => 13 ),
			'ah-abandoned'    => array( 'title' => 'Abandoned Carts',  'pos' => 14 ),
			'ah-marketing'    => array( 'title' => 'Marketing',        'pos' => 15 ),
			'ah-recommendations' => array( 'title' => 'Recommendations', 'pos' => 16 ),
			'ah-notifications' => array( 'title' => 'Notifications',  'pos' => 17 ),
			'ah-stock-alerts'   => array( 'title' => 'Stock Alerts',    'pos' => 18 ),
			'ah-qa'             => array( 'title' => 'Product Q&A',     'pos' => 19 ),
			'ah-settings'     => array( 'title' => 'Settings',         'pos' => 20 ),
		);

		foreach ( $pages as $slug => $data ) {
			add_submenu_page(
				'cms_ecommerce',
				$data['title'],
				$data['title'],
				'manage_options',
				$slug,
				array( $this, 'render_module_page' ),
				$data['pos']
			);
		}
	}

	/**
	 * Render a module page by resolving its view file.
	 */
	public function render_module_page() {
		$page     = sanitize_key( $_GET['page'] ?? '' );
		$filename = str_replace( 'ah-', '', $page );
		$file     = __DIR__ . '/views/html-' . $filename . '.php';

		if ( file_exists( $file ) ) {
			require_once $file;
		} else {
			echo '<div class="wrap ah-wrap"><h1><span class="dashicons dashicons-admin-generic"></span> '
				. esc_html( get_admin_page_title() )
				. '</h1><div class="ah-notice ah-notice-info">Module UI construction pending...</div></div>';
		}
	}

	public function render_page() {
		require_once __DIR__ . '/views/html-admin-tools.php';
	}

	/**
	 * Handle admin form actions.
	 */
	public function handle_actions() {
		if ( ! isset( $_POST['ah_ecommerce_action'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'ah_ecommerce_tools_action' ) ) {
			wp_die( 'Security check failed.' );
		}

		$action = sanitize_text_field( $_POST['ah_ecommerce_action'] );

		switch ( $action ) {
			case 'update_schema':
				Schema::update_schema();
				add_settings_error( 'cms_ecommerce', 'schema_updated', 'Database schemas updated.', 'updated' );
				break;
			case 'clear_cache':
				wp_cache_flush();
				add_settings_error( 'cms_ecommerce', 'cache_cleared', 'Object Cache cleared.', 'updated' );
				break;
		}

		// Redirect back to same page.
		if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=' . sanitize_text_field( $_GET['page'] ?? 'cms_ecommerce' ) ) );
			exit;
		}
	}

	/**
	 * Handle AJAX admin actions (CRUD operations from admin views).
	 */
	public function handle_ajax_actions() {
		check_ajax_referer( 'ah_admin_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Permission denied.' ) );
		}

		$sub_action = sanitize_text_field( $_POST['sub_action'] ?? '' );
		$result     = false;

		switch ( $sub_action ) {

			// ── Sales ──
			case 'create_sale':
				$result = \AHEcommerce\Commerce\Sales\Sale_Service::create_sale( array(
					'product_id' => (int) $_POST['product_id'],
					'sale_price' => (float) $_POST['sale_price'],
					'start_date' => sanitize_text_field( $_POST['start_date'] ),
					'end_date'   => sanitize_text_field( $_POST['end_date'] ),
				) );
				break;

			case 'delete_sale':
				$result = \AHEcommerce\Commerce\Sales\Sale_Service::delete_sale( (int) $_POST['sale_id'] );
				break;

			// ── Coupons ──
			case 'create_coupon':
				$result = \AHEcommerce\Commerce\Coupons\Coupon_Engine::create_coupon( array(
					'code'           => sanitize_text_field( $_POST['code'] ),
					'discount_type'  => sanitize_key( $_POST['discount_type'] ),
					'amount'         => (float) $_POST['amount'],
					'usage_limit'    => $_POST['usage_limit'] !== '' ? (int) $_POST['usage_limit'] : null,
					'expiry_date'    => sanitize_text_field( $_POST['expiry_date'] ?? '' ),
					'minimum_spend'  => (float) ( $_POST['minimum_spend'] ?? 0 ),
				) );
				break;

			case 'delete_coupon':
				$result = \AHEcommerce\Commerce\Coupons\Coupon_Engine::delete_coupon( (int) $_POST['coupon_id'] );
				break;

			// ── Reviews ──
			case 'approve_review':
				$result = \AHEcommerce\Commerce\Reviews\Review_Service::approve( (int) $_POST['review_id'] );
				break;

			case 'reject_review':
				$result = \AHEcommerce\Commerce\Reviews\Review_Service::reject( (int) $_POST['review_id'] );
				break;

			case 'delete_review':
				$result = \AHEcommerce\Commerce\Reviews\Review_Service::delete_review( (int) $_POST['review_id'] );
				break;

			// ── Shipping ──
			case 'create_zone':
				$result = \AHEcommerce\Commerce\Shipping\Shipping_Service::create_zone(
					sanitize_text_field( $_POST['zone_name'] ),
					array_map( 'sanitize_text_field', explode( ',', $_POST['regions'] ?? '' ) )
				);
				break;

			case 'delete_zone':
				$result = \AHEcommerce\Commerce\Shipping\Shipping_Service::delete_zone( (int) $_POST['zone_id'] );
				break;

			case 'add_shipping_method':
				$result = \AHEcommerce\Commerce\Shipping\Shipping_Service::add_method( array(
					'zone_id'      => (int) $_POST['zone_id'],
					'method_type'  => sanitize_key( $_POST['method_type'] ),
					'method_title' => sanitize_text_field( $_POST['method_title'] ),
					'cost'         => (float) $_POST['cost'],
					'min_order'    => (float) ( $_POST['min_order'] ?? 0 ),
					'max_order'    => (float) ( $_POST['max_order'] ?? 0 ),
				) );
				break;

			// ── Tax ──
			case 'create_tax_rule':
				$result = \AHEcommerce\Commerce\Tax\Tax_Service::create_rule( array(
					'name'     => sanitize_text_field( $_POST['rule_name'] ),
					'rate'     => (float) $_POST['rate'],
					'type'     => sanitize_key( $_POST['rate_type'] ),
					'country'  => sanitize_text_field( $_POST['country'] ?? '' ),
					'state'    => sanitize_text_field( $_POST['state'] ?? '' ),
					'apply_to' => sanitize_key( $_POST['apply_to'] ),
				) );
				break;

			case 'delete_tax_rule':
				$result = \AHEcommerce\Commerce\Tax\Tax_Service::delete_rule( (int) $_POST['rule_id'] );
				break;

			// ── Categories ──
			case 'create_category':
				$result = \AHEcommerce\Modules\Categories\Category_Repository::class;
				$repo   = \AH_Ecommerce::container()->get( \AHEcommerce\Modules\Categories\Category_Repository::class );
				$result = $repo->insert( array(
					'name'        => sanitize_text_field( $_POST['name'] ),
					'slug'        => sanitize_title( $_POST['name'] ),
					'parent_id'   => (int) ( $_POST['parent_id'] ?? 0 ),
					'description' => wp_kses_post( $_POST['description'] ?? '' ),
				) );
				break;

			// ── Price Rules ──
			case 'create_price_rule':
				global $wpdb;
				$result = $wpdb->insert( $wpdb->prefix . 'ah_ecommerce_price_rules', array(
					'name'            => sanitize_text_field( $_POST['rule_name'] ),
					'rule_type'       => sanitize_key( $_POST['rule_type'] ),
					'min_qty'         => (int) $_POST['min_qty'],
					'max_qty'         => $_POST['max_qty'] !== '' ? (int) $_POST['max_qty'] : null,
					'discount_type'   => sanitize_key( $_POST['discount_type'] ),
					'discount_value'  => (float) $_POST['discount_value'],
					'user_role'       => sanitize_text_field( $_POST['user_role'] ?? '' ),
					'start_date'      => sanitize_text_field( $_POST['start_date'] ?? '' ),
					'end_date'        => sanitize_text_field( $_POST['end_date'] ?? '' ),
					'priority'        => (int) ( $_POST['priority'] ?? 10 ),
				) );
				break;

			// ── Stock ──
			case 'update_stock':
				\AHEcommerce\Commerce\Inventory\Inventory_Service::set_stock( (int) $_POST['product_id'], (int) $_POST['quantity'] );
				$result = true;
				break;
		}

		if ( $result || $result === 0 ) {
			wp_send_json_success( array( 'message' => 'Action completed.' ) );
		}
		wp_send_json_error( array( 'message' => 'Action failed.' ) );
	}
}
