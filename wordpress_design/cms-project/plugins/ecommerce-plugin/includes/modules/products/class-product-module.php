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

		// Product grid shortcode (original).
		add_shortcode( 'ah_ecommerce_products', array( $this, 'render_products_shortcode' ) );

		// Advanced product listing with search/filter/sort.
		add_shortcode( 'ah_product_listing', array( $this, 'render_listing_shortcode' ) );

		// Product comparison page.
		add_shortcode( 'ah_product_compare', array( $this, 'render_compare_shortcode' ) );

		// Product stock alert widget (include in theme templates).
		add_shortcode( 'ah_stock_alert', array( $this, 'render_stock_alert_shortcode' ) );

		// Product Q&A widget.
		add_shortcode( 'ah_product_qa', array( $this, 'render_qa_shortcode' ) );
	}

	// ── Shortcode renderers ──

	public function render_products_shortcode( $atts ) {
		ob_start();
		require __DIR__ . '/views/shortcode-products.php';
		return ob_get_clean();
	}

	public function render_listing_shortcode( $atts ) {
		ob_start();
		require __DIR__ . '/views/shortcode-product-listing.php';
		return ob_get_clean();
	}

	public function render_compare_shortcode( $atts ) {
		ob_start();
		require __DIR__ . '/views/shortcode-compare.php';
		return ob_get_clean();
	}

	public function render_stock_alert_shortcode( $atts ) {
		$atts = shortcode_atts( array( 'product_id' => 0 ), $atts, 'ah_stock_alert' );
		$product_id = (int) $atts['product_id'];
		if ( ! $product_id ) {
			global $post;
			$product_id = $post ? $post->ID : 0;
		}
		ob_start();
		include __DIR__ . '/views/widget-stock-alert.php';
		return ob_get_clean();
	}

	public function render_qa_shortcode( $atts ) {
		$atts = shortcode_atts( array( 'product_id' => 0 ), $atts, 'ah_product_qa' );
		$product_id = (int) $atts['product_id'];
		if ( ! $product_id ) {
			global $post;
			$product_id = $post ? $post->ID : 0;
		}
		ob_start();
		include __DIR__ . '/views/widget-qa.php';
		return ob_get_clean();
	}

	// ── Admin ──

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
		require __DIR__ . '/views/html-products.php';
	}
}
