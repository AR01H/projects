<?php
namespace AHEcommerce\Modules\Checkout;

use AHEcommerce\Core\Service_Provider;
use AHEcommerce\Modules\Cart\Cart_Module;
use AHEcommerce\Database\Order_Repository;
use AHEcommerce\Database\Product_Repository;

/**
 * Handles the Frontend Checkout Flow.
 */
class Checkout_Module implements Service_Provider {

	public function register( \AHEcommerce\Core\Container $container ) {
		// Module dependencies can be registered here.
	}

	public function boot( \AHEcommerce\Core\Container $container ) {
		add_shortcode( 'ah_ecommerce_checkout', array( $this, 'render_checkout_shortcode' ) );
		add_action( 'wp_ajax_ah_process_checkout', array( $this, 'process_checkout' ) );
		add_action( 'wp_ajax_nopriv_ah_process_checkout', array( $this, 'process_checkout' ) );
	}

	public function render_checkout_shortcode() {
		ob_start();
		require __DIR__ . '/views/shortcode-checkout.php';
		return ob_start();
	}

	public function process_checkout() {
		$cart_module = \AH_Ecommerce::container()->get( Cart_Module::class );
		$cart = $cart_module->get_cart();
		
		if ( empty( $cart ) ) {
			wp_send_json_error( array( 'message' => 'Your cart is empty.' ) );
		}

		// Calculate total
		$product_repo = new Product_Repository();
		$subtotal = 0;
		foreach ( $cart as $item ) {
			$product = $product_repo->get( $item['id'] );
			if ( $product ) {
				$subtotal += (float) $product->price * (int) $item['qty'];
			}
		}

		$order_repo = new Order_Repository();
		
		$order_data = array(
			'guest_email'        => sanitize_email( $_POST['guest_email'] ?? '' ),
			'guest_phone'        => sanitize_text_field( $_POST['guest_phone'] ?? '' ),
			'billing_first_name' => sanitize_text_field( $_POST['billing_first_name'] ?? '' ),
			'billing_last_name'  => sanitize_text_field( $_POST['billing_last_name'] ?? '' ),
			'billing_address'    => sanitize_text_field( $_POST['billing_address'] ?? '' ),
			'billing_city'       => sanitize_text_field( $_POST['billing_city'] ?? '' ),
			'billing_postcode'   => sanitize_text_field( $_POST['billing_postcode'] ?? '' ),
			'payment_method'     => sanitize_text_field( $_POST['payment_method'] ?? 'cod' ),
			'status'             => 'processing',
			'subtotal'           => $subtotal,
			'total'              => $subtotal,
		);

		$order_id = $order_repo->insert( $order_data );

		if ( $order_id ) {
			// Clear cart
			$_SESSION['ah_cart'] = array();
			wp_send_json_success( array( 'message' => 'Order #' . $order_id . ' placed successfully!' ) );
		}

		wp_send_json_error( array( 'message' => 'Failed to create order.' ) );
	}
}
