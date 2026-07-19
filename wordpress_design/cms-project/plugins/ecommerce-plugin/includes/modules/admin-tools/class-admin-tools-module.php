<?php
namespace AHEcommerce\Modules\Admin_Tools;

use AHEcommerce\Core\Abstract_Module;
use AHEcommerce\Database\Schema;

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
	}

	public function add_menu_page() {
		// Main Menu
		add_menu_page(
			'CMS Ecommerce',
			'CMS Ecommerce',
			'manage_options',
			'cms_ecommerce',
			array( $this, 'render_page' ),
			'dashicons-store',
			56
		);

		// 1. Dashboard (Fixes the duplicate "CMS Ecommerce" first item)
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

		// Note: "Products" will be injected here automatically by Product_Module

		// Scaffold Placeholder Modules
		$placeholders = array(
			'ah-orders'       => array( 'title' => 'Orders', 'pos' => 3 ),
			'ah-categories'   => array( 'title' => 'Categories', 'pos' => 4 ),
			'ah-customers'    => array( 'title' => 'Customers', 'pos' => 5 ),
			'ah-coupons'      => array( 'title' => 'Coupons', 'pos' => 6 ),
			'ah-pricing'      => array( 'title' => 'Pricing Rules', 'pos' => 7 ),
			'ah-inventory'    => array( 'title' => 'Inventory', 'pos' => 8 ),
			'ah-shipping'     => array( 'title' => 'Shipping', 'pos' => 9 ),
			'ah-marketing'    => array( 'title' => 'Marketing', 'pos' => 10 ),
			'ah-settings'     => array( 'title' => 'Settings', 'pos' => 12 ),
		);

		foreach ( $placeholders as $slug => $data ) {
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

	public function render_module_page() {
		$page = sanitize_key( $_GET['page'] ?? '' );
		$filename = str_replace( 'ah-', '', $page );
		$file = __DIR__ . '/views/html-' . $filename . '.php';
		
		if ( file_exists( $file ) ) {
			require_once $file;
		} else {
			echo '<div class="wrap ah-wrap"><h1><span class="dashicons dashicons-admin-generic"></span> ' . esc_html( get_admin_page_title() ) . '</h1><div class="ah-notice ah-notice-info">Module UI construction pending...</div></div>';
		}
	}

	public function render_page() {
		require_once __DIR__ . '/views/html-admin-tools.php';
	}

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
				add_settings_error( 'cms_ecommerce', 'schema_updated', 'Database schemas have been successfully created/updated.', 'updated' );
				break;
			case 'clear_cache':
				wp_cache_flush();
				add_settings_error( 'cms_ecommerce', 'cache_cleared', 'Object Cache and Transients have been cleared.', 'updated' );
				break;
		}
	}
}
