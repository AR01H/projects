<?php
namespace AHEcommerce\Commerce\Sales;

/**
 * Manages scheduled sales, flash sales, and countdown timers.
 */
class Sale_Service {

	/**
	 * Create a scheduled sale.
	 *
	 * @param array $data {product_id, sale_price, start_date, end_date}
	 * @return int|false Insert ID or false.
	 */
	public static function create_sale( $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_sales';
		$wpdb->insert( $table, array(
			'product_id'  => (int) $data['product_id'],
			'sale_price'  => (float) $data['sale_price'],
			'start_date'  => sanitize_text_field( $data['start_date'] ),
			'end_date'    => sanitize_text_field( $data['end_date'] ),
			'status'      => 'scheduled',
			'created_at'  => current_time( 'mysql' ),
		) );
		return $wpdb->insert_id;
	}

	/**
	 * Get all sales (admin listing).
	 */
	public static function get_sales( $page = 1, $per_page = 20 ) {
		global $wpdb;
		$table  = $wpdb->prefix . 'ah_ecommerce_sales';
		$offset = ( $page - 1 ) * $per_page;
		$items  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT s.*, p.title AS product_name
				FROM {$table} s
				LEFT JOIN {$wpdb->prefix}ah_ecommerce_products p ON s.product_id = p.id
				ORDER BY s.start_date DESC
				LIMIT %d OFFSET %d",
				$per_page,
				$offset
			)
		);
		$total  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
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
	 * Get the active sale price for a product (if any).
	 *
	 * @return float|null Sale price or null if not on sale.
	 */
	public static function get_sale_price( $product_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_sales';
		$now   = gmdate( 'Y-m-d H:i:s' );

		$sale = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT sale_price FROM {$table}
				WHERE product_id = %d
				AND status = 'active'
				AND start_date <= %s
				AND end_date >= %s",
				$product_id,
				$now,
				$now
			)
		);

		return $sale ? (float) $sale->sale_price : null;
	}

	/**
	 * Check if a product is currently on sale.
	 */
	public static function is_on_sale( $product_id ) {
		return self::get_sale_price( $product_id ) !== null;
	}

	/**
	 * Get countdown data for a product's active sale.
	 *
	 * @return array|null
	 */
	public static function get_countdown( $product_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_sales';
		$now   = gmdate( 'Y-m-d H:i:s' );

		$sale = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE product_id = %d
				AND status = 'active'
				AND start_date <= %s
				AND end_date >= %s",
				$product_id,
				$now,
				$now
			)
		);

		if ( ! $sale ) {
			return null;
		}

		$end    = strtotime( $sale->end_date );
		$diff   = $end - time();
		$now_ts = time();

		return array(
			'sale_id'      => (int) $sale->id,
			'sale_price'   => (float) $sale->sale_price,
			'original_price' => null,
			'end_date'     => $sale->end_date,
			'days'         => (int) floor( $diff / 86400 ),
			'hours'        => (int) floor( ( $diff % 86400 ) / 3600 ),
			'minutes'      => (int) floor( ( $diff % 3600 ) / 60 ),
			'seconds'      => (int) ( $diff % 60 ),
			'timestamp'    => $end,
		);
	}

	/**
	 * Activate upcoming sales whose start_date has arrived (cron).
	 */
	public static function activate_sales() {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_sales';
		$now   = gmdate( 'Y-m-d H:i:s' );

		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET status = 'active' WHERE status = 'scheduled' AND start_date <= %s",
				$now
			)
		);

		// Expire sales past end_date.
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$table} SET status = 'expired' WHERE status = 'active' AND end_date < %s",
				$now
			)
		);
	}

	/**
	 * Delete a sale.
	 */
	public static function delete_sale( $sale_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_sales';
		return $wpdb->delete( $table, array( 'id' => $sale_id ) );
	}
}
