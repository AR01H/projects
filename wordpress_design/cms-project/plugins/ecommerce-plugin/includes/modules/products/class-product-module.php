<?php
namespace AHEcommerce\Modules\Products;

use AHEcommerce\Core\Abstract_Module;

class Product_Module extends Abstract_Module {

	public function get_id() {
		return 'products';
	}

	public function get_name() {
		return 'Product Management';
	}

	public function boot() {
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_shortcode( 'ah_ecommerce_products', array( $this, 'render_products_shortcode' ) );
	}

	public function render_products_shortcode( $atts ) {
		ob_start();
		require_once __DIR__ . '/views/shortcode-products.php';
		return ob_get_clean();
	}

	public function enqueue_scripts( $hook ) {
		if ( strpos( $hook, 'ah-products' ) !== false ) {
			wp_enqueue_media();
		}
	}

	public function register_admin_menu() {
		add_submenu_page(
			'cms_ecommerce',
			'Products',
			'All Products',
			'manage_options',
			'ah-products',
			array( $this, 'render_admin_page' ),
			2
		);
	}

	public function render_admin_page() {
		require_once __DIR__ . '/views/html-products.php';
	}
}
