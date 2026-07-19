<?php
namespace AHEcommerce\Modules\Cart;

use AHEcommerce\Core\Abstract_Module;
use AHEcommerce\Core\Container;
use AHEcommerce\Core\Service_Provider;

class Cart_Module extends Abstract_Module implements Service_Provider {

	public function get_id() {
		return 'cart';
	}

	public function get_name() {
		return 'Cart System';
	}

	public function register( Container $container ) {
		// Register any cart dependencies here if needed
	}

	public function boot( Container $container ) {
		add_action( 'init', array( $this, 'init_session' ), 1 );
		add_shortcode( 'ah_ecommerce_cart', array( $this, 'render_cart_shortcode' ) );
		
		// AJAX Actions
		add_action( 'wp_ajax_ah_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_ah_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
		
		add_action( 'wp_ajax_ah_remove_from_cart', array( $this, 'ajax_remove_from_cart' ) );
		add_action( 'wp_ajax_nopriv_ah_remove_from_cart', array( $this, 'ajax_remove_from_cart' ) );
	}

	public function init_session() {
		if ( ! session_id() && ! headers_sent() ) {
			session_start();
		}
		if ( ! isset( $_SESSION['ah_cart'] ) ) {
			$_SESSION['ah_cart'] = array();
		}
	}

	public function get_cart() {
		return $_SESSION['ah_cart'] ?? array();
	}

	public function render_cart_shortcode( $atts ) {
		ob_start();
		require_once __DIR__ . '/views/shortcode-cart.php';
		return ob_get_clean();
	}

	public function ajax_add_to_cart() {
		check_ajax_referer( 'ah_cart_nonce', 'nonce' );
		
		$product_id = (int) $_POST['product_id'];
		$qty = (int) ( $_POST['qty'] ?? 1 );
		
		if ( $product_id > 0 && $qty > 0 ) {
			if ( isset( $_SESSION['ah_cart'][ $product_id ] ) ) {
				$_SESSION['ah_cart'][ $product_id ]['qty'] += $qty;
			} else {
				$_SESSION['ah_cart'][ $product_id ] = array(
					'id'  => $product_id,
					'qty' => $qty
				);
			}
			wp_send_json_success( array( 'message' => 'Product added to cart.', 'cart_count' => count( $_SESSION['ah_cart'] ) ) );
		}
		
		wp_send_json_error( array( 'message' => 'Invalid product.' ) );
	}

	public function ajax_remove_from_cart() {
		check_ajax_referer( 'ah_cart_nonce', 'nonce' );
		$product_id = (int) $_POST['product_id'];
		
		if ( isset( $_SESSION['ah_cart'][ $product_id ] ) ) {
			unset( $_SESSION['ah_cart'][ $product_id ] );
			wp_send_json_success( array( 'message' => 'Product removed.' ) );
		}
		
		wp_send_json_error( array( 'message' => 'Item not in cart.' ) );
	}
}
