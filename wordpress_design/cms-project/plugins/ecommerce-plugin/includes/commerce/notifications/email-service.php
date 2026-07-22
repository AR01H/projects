<?php
namespace AHEcommerce\Commerce\Notifications;

/**
 * Handles transactional email notifications.
 * Uses wp_mail() — theme-independent, works with any mailer plugin.
 */
class Email_Service {

	/**
	 * Send an order confirmation email.
	 */
	public static function send_order_confirmation( $order_data, $cart_items ) {
		$to      = $order_data['guest_email'];
		$subject = sprintf( 'Order #%d Confirmation', $order_data['id'] );
		$body    = self::render_template( 'order-confirmation', array(
			'order'      => $order_data,
			'cart_items' => $cart_items,
		) );

		return self::send( $to, $subject, $body );
	}

	/**
	 * Send an order status update email.
	 */
	public static function send_order_status_update( $order_data, $old_status, $new_status ) {
		$to      = $order_data['guest_email'];
		$subject = sprintf( 'Order #%d Status Updated', $order_data['id'] );
		$body    = self::render_template( 'order-status-update', array(
			'order'      => $order_data,
			'old_status' => $old_status,
			'new_status' => $new_status,
		) );

		return self::send( $to, $subject, $body );
	}

	/**
	 * Send low stock alert to admin.
	 */
	public static function send_low_stock_alert( $product ) {
		$to      = get_option( 'admin_email' );
		$subject = sprintf( 'Low Stock Alert: %s', $product->title );
		$body    = self::render_template( 'low-stock-alert', array(
			'product' => $product,
		) );

		return self::send( $to, $subject, $body );
	}

	/**
	 * Send out of stock alert to admin.
	 */
	public static function send_out_of_stock_alert( $product ) {
		$to      = get_option( 'admin_email' );
		$subject = sprintf( 'Out of Stock: %s', $product->title );
		$body    = self::render_template( 'out-of-stock-alert', array(
			'product' => $product,
		) );

		return self::send( $to, $subject, $body );
	}

	/**
	 * Send abandoned cart reminder.
	 */
	public static function send_abandoned_cart_reminder( $email, $cart_items, $cart_total ) {
		$to      = $email;
		$subject = 'You left items in your cart!';
		$body    = self::render_template( 'abandoned-cart', array(
			'cart_items' => $cart_items,
			'cart_total' => $cart_total,
		) );

		return self::send( $to, $subject, $body );
	}

	/**
	 * Send a new review notification to admin.
	 */
	public static function send_new_review_alert( $review, $product ) {
		$to      = get_option( 'admin_email' );
		$subject = sprintf( 'New Review for: %s', $product->title );
		$body    = self::render_template( 'new-review', array(
			'review'  => $review,
			'product' => $product,
		) );

		return self::send( $to, $subject, $body );
	}

	/**
	 * Send a generic email.
	 */
	private static function send( $to, $subject, $body ) {
		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			'From: ' . get_bloginfo( 'name' ) . ' <' . get_option( 'admin_email' ) . '>',
		);
		return wp_mail( $to, $subject, $body, $headers );
	}

	/**
	 * Render an email template.
	 * Looks for override in theme: ah-ecommerce/emails/{template}.php
	 * Falls back to plugin defaults.
	 */
	private static function render_template( $template, $args = array() ) {
		extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract

		// Theme override first.
		$theme_file = locate_template( "ah-ecommerce/emails/{$template}.php" );
		if ( $theme_file ) {
			ob_start();
			include $theme_file;
			return ob_get_clean();
		}

		// Plugin default.
		$plugin_file = AH_ECOMMERCE_DIR . "includes/commerce/notifications/templates/{$template}.php";
		if ( file_exists( $plugin_file ) ) {
			ob_start();
			include $plugin_file;
			return ob_get_clean();
		}

		// Fallback plain text.
		return '<p>' . esc_html( $subject ?? 'Notification' ) . '</p>';
	}
}
