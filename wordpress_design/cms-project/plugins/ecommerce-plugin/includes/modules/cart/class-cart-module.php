<?php
namespace AHEcommerce\Modules\Cart;

use AHEcommerce\Core\Abstract_Module;
use AHEcommerce\Commerce\Wishlist\Wishlist_Service;
use AHEcommerce\Commerce\Abandoned_Cart\Abandoned_Cart_Service;

class Cart_Module extends Abstract_Module {

	public function get_id() {
		return 'cart';
	}

	public function get_name() {
		return 'Cart System';
	}

	public function boot() {
		add_action( 'init', array( $this, 'init_session' ), 1 );
		add_shortcode( 'ah_ecommerce_cart', array( $this, 'render_cart_shortcode' ) );

		// Cart AJAX.
		add_action( 'wp_ajax_ah_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_ah_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
		add_action( 'wp_ajax_ah_remove_from_cart', array( $this, 'ajax_remove_from_cart' ) );
		add_action( 'wp_ajax_nopriv_ah_remove_from_cart', array( $this, 'ajax_remove_from_cart' ) );

		// Wishlist AJAX.
		add_action( 'wp_ajax_ah_add_to_wishlist', array( $this, 'ajax_add_to_wishlist' ) );
		add_action( 'wp_ajax_nopriv_ah_add_to_wishlist', array( $this, 'ajax_add_to_wishlist' ) );
		add_action( 'wp_ajax_ah_remove_from_wishlist', array( $this, 'ajax_remove_from_wishlist' ) );
		add_action( 'wp_ajax_nopriv_ah_remove_from_wishlist', array( $this, 'ajax_remove_from_wishlist' ) );

		// Track abandoned carts on checkout page view.
		add_action( 'ah_ecommerce_checkout_view', array( $this, 'track_abandoned_cart' ) );
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
		$qty        = (int) ( $_POST['qty'] ?? 1 );

		if ( $product_id > 0 && $qty > 0 ) {
			if ( isset( $_SESSION['ah_cart'][ $product_id ] ) ) {
				$_SESSION['ah_cart'][ $product_id ]['qty'] += $qty;
			} else {
				$_SESSION['ah_cart'][ $product_id ] = array(
					'id'  => $product_id,
					'qty' => $qty,
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

	// ── Wishlist AJAX ──

	public function ajax_add_to_wishlist() {
		check_ajax_referer( 'ah_cart_nonce', 'nonce' );
		$product_id = (int) $_POST['product_id'];

		if ( $product_id > 0 ) {
			$added = Wishlist_Service::add( $product_id );
			wp_send_json_success( array( 'message' => $added ? 'Added to wishlist.' : 'Already in wishlist.' ) );
		}

		wp_send_json_error( array( 'message' => 'Invalid product.' ) );
	}

	public function ajax_remove_from_wishlist() {
		check_ajax_referer( 'ah_cart_nonce', 'nonce' );
		$product_id = (int) $_POST['product_id'];

		if ( $product_id > 0 ) {
			Wishlist_Service::remove( $product_id );
			wp_send_json_success( array( 'message' => 'Removed from wishlist.' ) );
		}

		wp_send_json_error( array( 'message' => 'Invalid product.' ) );
	}

	// ── Abandoned Cart Tracking ──

	/**
	 * Track cart as potentially abandoned when user views checkout.
	 * Hooked to `ah_ecommerce_checkout_view`.
	 */
	public function track_abandoned_cart() {
		if ( get_option( 'ah_enable_abandoned_cart', '0' ) !== '1' ) {
			return;
		}

		$cart = $this->get_cart();
		if ( empty( $cart ) ) {
			return;
		}

		$email = sanitize_email( $_POST['guest_email'] ?? $_SESSION['ah_checkout_email'] ?? '' );
		if ( empty( $email ) || ! is_email( $email ) ) {
			return;
		}

		$_SESSION['ah_checkout_email'] = $email;

		// Calculate total.
		$product_repo = \AH_Ecommerce::container()->get( \AHEcommerce\Modules\Products\Product_Repository::class );
		$total        = 0;
		$items        = array();
		foreach ( $cart as $item ) {
			$product = $product_repo->get( $item['id'] );
			if ( $product ) {
				$total += (float) $product->price * (int) $item['qty'];
				$items[] = array(
					'id'    => $item['id'],
					'name'  => $product->title,
					'price' => (float) $product->price,
					'qty'   => (int) $item['qty'],
				);
			}
		}

		Abandoned_Cart_Service::log_cart( $email, $items, $total );
	}
}
