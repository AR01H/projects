<?php
namespace AHEcommerce\Commerce\Stock_Alerts;

use AHEcommerce\Commerce\Notifications\Email_Service;

/**
 * "Notify me when back in stock" — customers subscribe to stock alerts.
 */
class Stock_Alert_Service {

	/**
	 * Subscribe to a stock alert.
	 *
	 * @return array{success: bool, message: string}
	 */
	public static function subscribe( $product_id, $email ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_stock_alerts';

		$email = sanitize_email( $email );
		if ( ! is_email( $email ) ) {
			return array( 'success' => false, 'message' => 'Please enter a valid email.' );
		}

		$product_id = (int) $product_id;
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE product_id = %d AND email = %s AND status = 'pending'",
				$product_id,
				$email
			)
		);

		if ( $exists ) {
			return array( 'success' => false, 'message' => 'You are already subscribed for this product.' );
		}

		$wpdb->insert( $table, array(
			'product_id' => $product_id,
			'email'      => $email,
			'status'     => 'pending',
			'created_at' => current_time( 'mysql' ),
		) );

		return array( 'success' => true, 'message' => 'We will notify you when this product is back in stock.' );
	}

	/**
	 * Notify all subscribers when a product is back in stock.
	 *
	 * @return int Number of notifications sent.
	 */
	public static function notify_back_in_stock( $product_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_stock_alerts';
		$products_table = $wpdb->prefix . 'ah_ecommerce_products';

		$product_id = (int) $product_id;

		// Get product name.
		$product_name = $wpdb->get_var(
			$wpdb->prepare( "SELECT title FROM {$products_table} WHERE id = %d", $product_id )
		);

		$subscribers = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE product_id = %d AND status = 'pending'",
				$product_id
			)
		);

		$sent = 0;
		foreach ( $subscribers as $sub ) {
			$to      = $sub->email;
			$subject = sprintf( 'Back in Stock: %s', $product_name );
			$body    = '<p>Good news! <strong>' . esc_html( $product_name ) . '</strong> is back in stock.</p>';
			$body   .= '<p><a href="' . esc_url( home_url( '?p=' . $product_id ) ) . '">View Product →</a></p>';
			$body   .= '<hr><p style="color:#888;font-size:12px;">' . esc_html( get_bloginfo( 'name' ) ) . '</p>';

			$headers = array( 'Content-Type: text/html; charset=UTF-8' );
			if ( wp_mail( $to, $subject, $body, $headers ) ) {
				$sent++;
				$wpdb->update( $table,
					array( 'status' => 'notified', 'notified_at' => current_time( 'mysql' ) ),
					array( 'id' => $sub->id )
				);
			}
		}

		return $sent;
	}

	/**
	 * Get subscribers for a product (admin view).
	 */
	public static function get_subscribers( $product_id = 0, $page = 1, $per_page = 20 ) {
		global $wpdb;
		$table  = $wpdb->prefix . 'ah_ecommerce_stock_alerts';
		$offset = ( $page - 1 ) * $per_page;
		$where  = '1=1';
		$args   = array();

		if ( $product_id > 0 ) {
			$where .= ' AND a.product_id = %d';
			$args[] = $product_id;
		}

		$args[] = $per_page;
		$args[] = $offset;

		$items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT a.*, p.title AS product_name
				FROM {$table} a
				LEFT JOIN {$wpdb->prefix}ah_ecommerce_products p ON a.product_id = p.id
				WHERE {$where}
				ORDER BY a.created_at DESC
				LIMIT %d OFFSET %d",
				...$args
			)
		);

		$count_args = array();
		if ( $product_id > 0 ) {
			$count_args[] = $product_id;
		}
		$total = (int) $wpdb->get_var(
			empty( $count_args )
				? "SELECT COUNT(*) FROM {$table}"
				: $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE product_id = %d", ...$count_args )
		);

		return array(
			'items' => $items,
			'meta'  => array(
				'current_page' => $page,
				'per_page'     => $per_page,
				'total_items'  => $total,
				'total_pages'  => ceil( $total / $per_page ),
			),
		);
	}

	/**
	 * Delete a subscription.
	 */
	public static function unsubscribe( $id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_stock_alerts';
		return $wpdb->delete( $table, array( 'id' => (int) $id ) );
	}
}
