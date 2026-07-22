<?php
namespace AHEcommerce\Commerce\Abandoned_Cart;

use AHEcommerce\Commerce\Notifications\Email_Service;

/**
 * Tracks and recovers abandoned carts.
 * Carts are logged on checkout page view; reminders sent after configurable delay.
 */
class Abandoned_Cart_Service {

	/**
	 * Log a cart (called when user views checkout).
	 *
	 * @param string $email    User email (from form or WP user).
	 * @param array  $cart     Cart items.
	 * @param float  $total    Cart total.
	 */
	public static function log_cart( $email, $cart, $total ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_abandoned_carts';

		$existing = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE email = %s AND status = 'pending'",
				$email
			)
		);

		$data = array(
			'email'          => sanitize_email( $email ),
			'cart_data'      => maybe_serialize( $cart ),
			'cart_total'     => (float) $total,
			'status'         => 'pending',
			'last_activity'  => current_time( 'mysql' ),
		);

		if ( $existing ) {
			$wpdb->update( $table, $data, array( 'id' => $existing->id ) );
		} else {
			$data['created_at'] = current_time( 'mysql' );
			$wpdb->insert( $table, $data );
		}
	}

	/**
	 * Mark a cart as recovered (on successful order).
	 */
	public static function mark_recovered( $email ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_abandoned_carts';
		$wpdb->update( $table,
			array( 'status' => 'recovered', 'recovered_at' => current_time( 'mysql' ) ),
			array( 'email' => $email, 'status' => 'pending' )
		);
	}

	/**
	 * Send reminders for abandoned carts (called by cron).
	 *
	 * @param int $delay_hours Hours after abandonment to send reminder.
	 * @return int Number of reminders sent.
	 */
	public static function send_reminders( $delay_hours = 24 ) {
		global $wpdb;
		$table      = $wpdb->prefix . 'ah_ecommerce_abandoned_carts';
		$threshold  = gmdate( 'Y-m-d H:i:s', strtotime( "-{$delay_hours} hours" ) );
		$max_remind = 3;

		$carts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE status = 'pending'
				AND reminder_count < %d
				AND last_activity < %s
				ORDER BY last_activity ASC",
				$max_remind,
				$threshold
			)
		);

		$sent = 0;
		foreach ( $carts as $cart ) {
			$items = maybe_unserialize( $cart->cart_data );
			if ( ! is_array( $items ) ) {
				continue;
			}

			$sent_ok = Email_Service::send_abandoned_cart_reminder(
				$cart->email,
				$items,
				(float) $cart->cart_total
			);

			if ( $sent_ok ) {
				$wpdb->update( $table,
					array(
						'reminder_count' => $cart->reminder_count + 1,
						'last_reminder'  => current_time( 'mysql' ),
					),
					array( 'id' => $cart->id )
				);
				$sent++;
			}
		}

		return $sent;
	}

	/**
	 * Get abandoned carts (admin listing).
	 */
	public static function get_carts( $page = 1, $per_page = 20, $status = '' ) {
		global $wpdb;
		$table  = $wpdb->prefix . 'ah_ecommerce_abandoned_carts';
		$offset = ( $page - 1 ) * $per_page;
		$where  = '1=1';
		$args   = array();

		if ( $status ) {
			$where    .= ' AND status = %s';
			$args[]    = $status;
		}

		$args[] = $per_page;
		$args[] = $offset;

		$items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE {$where} ORDER BY last_activity DESC LIMIT %d OFFSET %d",
				...$args
			)
		);

		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE {$where}" );

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
	 * Get abandonment stats.
	 */
	public static function get_stats() {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_abandoned_carts';

		$total   = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		$pending = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'pending'" );
		$recovered = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE status = 'recovered'" );
		$total_value = (float) $wpdb->get_var(
			"SELECT SUM(cart_total) FROM {$table} WHERE status = 'pending'"
		);

		return array(
			'total'        => $total,
			'pending'      => $pending,
			'recovered'    => $recovered,
			'total_value'  => round( $total_value, 2 ),
			'recovery_rate' => $total > 0 ? round( ( $recovered / $total ) * 100, 1 ) : 0,
		);
	}
}
