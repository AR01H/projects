<?php
namespace AHEcommerce\Commerce\Reviews;

/**
 * Manages product reviews and ratings.
 */
class Review_Service {

	/**
	 * Add a review for a product.
	 *
	 * @param array $data {product_id, reviewer_name, reviewer_email, rating, comment, image_url}
	 * @return int|false Review ID or false.
	 */
	public static function add_review( $data ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_reviews';

		$rating = max( 1, min( 5, (int) $data['rating'] ) );

		$wpdb->insert( $table, array(
			'product_id'     => (int) $data['product_id'],
			'reviewer_name'  => sanitize_text_field( $data['reviewer_name'] ),
			'reviewer_email' => sanitize_email( $data['reviewer_email'] ?? '' ),
			'rating'         => $rating,
			'comment'        => wp_kses_post( $data['comment'] ),
			'image_url'      => esc_url_raw( $data['image_url'] ?? '' ),
			'status'         => 'pending',
			'created_at'     => current_time( 'mysql' ),
		) );

		return $wpdb->insert_id;
	}

	/**
	 * Get approved reviews for a product.
	 */
	public static function get_reviews( $product_id, $page = 1, $per_page = 10 ) {
		global $wpdb;
		$table  = $wpdb->prefix . 'ah_ecommerce_reviews';
		$offset = ( $page - 1 ) * $per_page;

		$items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE product_id = %d AND status = 'approved'
				ORDER BY created_at DESC
				LIMIT %d OFFSET %d",
				$product_id,
				$per_page,
				$offset
			)
		);

		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE product_id = %d AND status = 'approved'",
				$product_id
			)
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
	 * Get average rating for a product.
	 *
	 * @return array{average: float, count: int}
	 */
	public static function get_rating( $product_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_reviews';

		$result = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT AVG(rating) AS average, COUNT(*) AS count
				FROM {$table}
				WHERE product_id = %d AND status = 'approved'",
				$product_id
			)
		);

		return array(
			'average' => $result ? round( (float) $result->average, 1 ) : 0,
			'count'   => $result ? (int) $result->count : 0,
		);
	}

	/**
	 * Get rating distribution (how many 1-star, 2-star, etc.).
	 *
	 * @return array{1: int, 2: int, 3: int, 4: int, 5: int}
	 */
	public static function get_rating_distribution( $product_id ) {
		global $wpdb;
		$table   = $wpdb->prefix . 'ah_ecommerce_reviews';
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT rating, COUNT(*) AS count
				FROM {$table}
				WHERE product_id = %d AND status = 'approved'
				GROUP BY rating",
				$product_id
			)
		);

		$dist = array( 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0 );
		foreach ( $results as $row ) {
			$dist[ (int) $row->rating ] = (int) $row->count;
		}
		return $dist;
	}

	/**
	 * Approve a review.
	 */
	public static function approve( $review_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_reviews';
		return $wpdb->update( $table, array( 'status' => 'approved' ), array( 'id' => $review_id ) );
	}

	/**
	 * Reject a review.
	 */
	public static function reject( $review_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_reviews';
		return $wpdb->update( $table, array( 'status' => 'rejected' ), array( 'id' => $review_id ) );
	}

	/**
	 * Delete a review.
	 */
	public static function delete_review( $review_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_ecommerce_reviews';
		return $wpdb->delete( $table, array( 'id' => $review_id ) );
	}

	/**
	 * Get all pending reviews (admin moderation queue).
	 */
	public static function get_pending_reviews( $page = 1, $per_page = 20 ) {
		global $wpdb;
		$table  = $wpdb->prefix . 'ah_ecommerce_reviews';
		$offset = ( $page - 1 ) * $per_page;

		$items = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT r.*, p.title AS product_name
				FROM {$table} r
				LEFT JOIN {$wpdb->prefix}ah_ecommerce_products p ON r.product_id = p.id
				WHERE r.status = 'pending'
				ORDER BY r.created_at DESC
				LIMIT %d OFFSET %d",
				$per_page,
				$offset
			)
		);

		$total = (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM {$table} WHERE status = 'pending'"
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
}
