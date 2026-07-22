<?php
namespace AHEcommerce\Modules\Checkout;

use AHEcommerce\Core\Abstract_Module;
use AHEcommerce\Core\Container;
use AHEcommerce\Modules\Cart\Cart_Module;
use AHEcommerce\Modules\Orders\Order_Repository;
use AHEcommerce\Modules\Products\Product_Repository;
use AHEcommerce\Commerce\Tax\Tax_Service;
use AHEcommerce\Commerce\Coupons\Coupon_Engine;
use AHEcommerce\Commerce\Inventory\Inventory_Service;
use AHEcommerce\Commerce\Abandoned_Cart\Abandoned_Cart_Service;
use AHEcommerce\Commerce\Notifications\Email_Service;

/**
 * Handles the Frontend Checkout Flow with tax, coupons, inventory, and notifications.
 */
class Checkout_Module extends Abstract_Module {

	private $container;

	public function __construct( Container $container ) {
		$this->container = $container;
	}

	public function get_id() {
		return 'checkout';
	}

	public function get_name() {
		return 'Checkout';
	}

	public function boot() {
		add_shortcode( 'ah_ecommerce_checkout', array( $this, 'render_checkout_shortcode' ) );
		add_action( 'wp_ajax_ah_process_checkout', array( $this, 'process_checkout' ) );
		add_action( 'wp_ajax_nopriv_ah_process_checkout', array( $this, 'process_checkout' ) );
		add_action( 'wp_ajax_ah_apply_coupon', array( $this, 'ajax_apply_coupon' ) );
		add_action( 'wp_ajax_nopriv_ah_apply_coupon', array( $this, 'ajax_apply_coupon' ) );
	}

	public function render_checkout_shortcode() {
		ob_start();
		require __DIR__ . '/views/shortcode-checkout.php';
		return ob_get_clean();
	}

	/**
	 * Apply a coupon code via AJAX.
	 */
	public function ajax_apply_coupon() {
		check_ajax_referer( 'ah_cart_nonce', 'nonce' );

		$code = sanitize_text_field( $_POST['coupon_code'] ?? '' );
		if ( empty( $code ) ) {
			wp_send_json_error( array( 'message' => 'Please enter a coupon code.' ) );
		}

		$cart_module = $this->container->get( Cart_Module::class );
		$cart        = $cart_module->get_cart();
		$cart_total  = $this->calculate_subtotal( $cart );

		$validation = Coupon_Engine::validate( $code, $cart_total, $cart );
		if ( ! $validation['valid'] ) {
			wp_send_json_error( array( 'message' => $validation['message'] ) );
		}

		$discount = Coupon_Engine::calculate_discount( $validation['coupon'], $cart_total, $cart );

		// Store coupon in session.
		if ( session_status() !== PHP_SESSION_ACTIVE ) {
			session_start();
		}
		$_SESSION['ah_applied_coupon'] = array(
			'code'     => $code,
			'coupon_id' => $validation['coupon']->id,
			'discount' => $discount['discount'],
			'label'    => $discount['label'],
		);

		wp_send_json_success( array(
			'message'  => $validation['message'],
			'discount' => $discount['discount'],
			'label'    => $discount['label'],
			'new_total' => $cart_total - $discount['discount'],
		) );
	}

	/**
	 * Process the checkout and create an order.
	 */
	public function process_checkout() {
		$cart_module = $this->container->get( Cart_Module::class );
		$cart        = $cart_module->get_cart();

		if ( empty( $cart ) ) {
			wp_send_json_error( array( 'message' => 'Your cart is empty.' ) );
		}

		$product_repo = $this->container->get( Product_Repository::class );
		$subtotal     = 0;
		$cart_items   = array();

		// Calculate subtotal and verify stock.
		foreach ( $cart as $item ) {
			$product = $product_repo->get( $item['id'] );
			if ( ! $product ) {
				wp_send_json_error( array( 'message' => 'Product not found: #' . $item['id'] ) );
			}
			if ( ! Inventory_Service::is_in_stock( $item['id'] ) ) {
				wp_send_json_error( array( 'message' => '"' . $product->title . '" is out of stock.' ) );
			}
			$line_total = (float) $product->price * (int) $item['qty'];
			$subtotal  += $line_total;
			$cart_items[] = array(
				'id'       => $item['id'],
				'name'     => $product->title,
				'price'    => (float) $product->price,
				'qty'      => (int) $item['qty'],
				'line_total' => $line_total,
			);
		}

		// Apply coupon discount.
		$discount_total = 0;
		$coupon_code    = '';
		if ( session_status() === PHP_SESSION_ACTIVE && isset( $_SESSION['ah_applied_coupon'] ) ) {
			$coupon_data  = $_SESSION['ah_applied_coupon'];
			$discount_total = (float) $coupon_data['discount'];
			$coupon_code  = $coupon_data['code'];
			Coupon_Engine::record_usage( $coupon_data['coupon_id'] );
			unset( $_SESSION['ah_applied_coupon'] );
		}

		// Calculate tax.
		$tax_total = 0;
		if ( get_option( 'ah_tax_enabled', '0' ) === '1' ) {
			$tax_result = Tax_Service::calculate_tax(
				$subtotal - $discount_total,
				0,
				sanitize_text_field( $_POST['billing_country'] ?? '' ),
				sanitize_text_field( $_POST['billing_state'] ?? '' ),
				sanitize_text_field( $_POST['billing_postcode'] ?? '' )
			);
			$tax_total = $tax_result['tax_total'];
		}

		$total = $subtotal - $discount_total + $tax_total;

		$order_repo = $this->container->get( Order_Repository::class );

		$order_data = array(
			'guest_email'        => sanitize_email( $_POST['guest_email'] ?? '' ),
			'guest_phone'        => sanitize_text_field( $_POST['guest_phone'] ?? '' ),
			'billing_first_name' => sanitize_text_field( $_POST['billing_first_name'] ?? '' ),
			'billing_last_name'  => sanitize_text_field( $_POST['billing_last_name'] ?? '' ),
			'billing_address'    => sanitize_text_field( $_POST['billing_address'] ?? '' ),
			'billing_city'       => sanitize_text_field( $_POST['billing_city'] ?? '' ),
			'billing_postcode'   => sanitize_text_field( $_POST['billing_postcode'] ?? '' ),
			'billing_country'    => sanitize_text_field( $_POST['billing_country'] ?? '' ),
			'payment_method'     => sanitize_text_field( $_POST['payment_method'] ?? 'cod' ),
			'coupon_code'        => $coupon_code,
			'status'             => 'processing',
			'subtotal'           => $subtotal,
			'tax_total'          => $tax_total,
			'discount_total'     => $discount_total,
			'total'              => $total,
		);

		$order_id = $order_repo->insert( $order_data );

		if ( ! $order_id ) {
			wp_send_json_error( array( 'message' => 'Failed to create order.' ) );
		}

		// Save order items.
		global $wpdb;
		$order_items_table = $wpdb->prefix . 'ah_ecommerce_order_items';
		foreach ( $cart_items as $item ) {
			$wpdb->insert( $order_items_table, array(
				'order_id'   => $order_id,
				'product_id' => $item['id'],
				'quantity'   => $item['qty'],
				'price'      => $item['price'],
				'total'      => $item['line_total'],
			) );

			// Reduce inventory.
			Inventory_Service::reduce_stock( $item['id'], $item['qty'] );
		}

		// Mark abandoned cart as recovered.
		$email = sanitize_email( $_POST['guest_email'] ?? '' );
		if ( $email ) {
			Abandoned_Cart_Service::mark_recovered( $email );
		}

		// Send order confirmation email.
		$order_data['id'] = $order_id;
		Email_Service::send_order_confirmation( $order_data, $cart_items );

		// Clear cart.
		$_SESSION['ah_cart'] = array();

		wp_send_json_success( array( 'message' => 'Order #' . $order_id . ' placed successfully!' ) );
	}

	/**
	 * Calculate cart subtotal.
	 */
	private function calculate_subtotal( $cart ) {
		$product_repo = $this->container->get( Product_Repository::class );
		$subtotal     = 0;
		foreach ( $cart as $item ) {
			$product = $product_repo->get( $item['id'] );
			if ( $product ) {
				$subtotal += (float) $product->price * (int) $item['qty'];
			}
		}
		return $subtotal;
	}
}
