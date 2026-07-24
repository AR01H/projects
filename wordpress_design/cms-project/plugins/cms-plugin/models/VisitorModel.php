<?php
defined( 'ABSPATH' ) || exit;

/**
 * Visitor tracking model.
 * Reads/writes wp_ah_visitor_logs.
 */
class AH_Visitor_Model {

	private string $table;

	public function __construct() {
		global $wpdb;
		$this->table = $wpdb->prefix . 'ah_visitor_logs';
	}

	/* ── Write ─────────────────────────────────────────────────── */

	/**
	 * Record a single page visit.
	 * Deduplicates: same session_id + page_slug within the last 5 minutes = skip.
	 */
	public function record( array $data ): bool {
		global $wpdb;

		$session = sanitize_text_field( $data['session_id'] ?? '' );
		$slug    = sanitize_text_field( $data['page_slug']  ?? '' );

		// Skip bot-like user agents.
		$ua = strtolower( $data['user_agent'] ?? '' );
		foreach ( array( 'bot', 'crawl', 'spider', 'slurp', 'mediapartners' ) as $sig ) {
			if ( false !== strpos( $ua, $sig ) ) { return false; }
		}

		// Dedup: same session + same slug within 5 min.
		if ( $session && $slug ) {
			$recent = $wpdb->get_var( $wpdb->prepare(
				"SELECT id FROM `{$this->table}` WHERE session_id = %s AND page_slug = %s AND visited_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE) LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$session,
				$slug
			) );
			if ( $recent ) { return false; }
		}

		$wpdb->insert( $this->table, array(
			'ip_address' => substr( sanitize_text_field( $data['ip_address'] ?? '' ), 0, 45 ),
			'page_url'   => substr( esc_url_raw( $data['page_url']   ?? '' ), 0, 700 ),
			'page_slug'  => substr( $slug, 0, 300 ),
			'referrer'   => substr( esc_url_raw( $data['referrer']   ?? '' ), 0, 700 ),
			'user_agent' => substr( sanitize_text_field( $data['user_agent'] ?? '' ), 0, 500 ),
			'session_id' => substr( $session, 0, 64 ),
		), array( '%s', '%s', '%s', '%s', '%s', '%s' ) );

		return (bool) $wpdb->insert_id;
	}

	/* ── Stats ─────────────────────────────────────────────────── */

	/** Total visits ever. */
	public function total(): int {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$this->table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/** Unique IPs ever. */
	public function total_unique(): int {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(DISTINCT ip_address) FROM `{$this->table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/** Visits today. */
	public function today(): int {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$this->table}` WHERE DATE(visited_at) = CURDATE()" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/** Unique IPs today. */
	public function today_unique(): int {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(DISTINCT ip_address) FROM `{$this->table}` WHERE DATE(visited_at) = CURDATE()" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/** Visits this month. */
	public function this_month(): int {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$this->table}` WHERE YEAR(visited_at) = YEAR(NOW()) AND MONTH(visited_at) = MONTH(NOW())" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	/** Top N pages by visit count. */
	public function top_pages( int $limit = 20 ): array {
		global $wpdb;
		$limit = max( 1, min( 100, $limit ) );
		return $wpdb->get_results( // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare(
				"SELECT page_slug, page_url, COUNT(*) AS visits, COUNT(DISTINCT ip_address) AS unique_visitors
				 FROM `{$this->table}`
				 GROUP BY page_slug, page_url
				 ORDER BY visits DESC
				 LIMIT %d",
				$limit
			),
			ARRAY_A
		) ?: array();
	}

	/** Monthly visit totals for the last N months. */
	public function monthly( int $months = 12 ): array {
		global $wpdb;
		$months = max( 1, min( 24, $months ) );
		return $wpdb->get_results( // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare(
				"SELECT DATE_FORMAT(visited_at, '%%Y-%%m') AS month,
				        COUNT(*)                            AS visits,
				        COUNT(DISTINCT ip_address)          AS unique_visitors
				 FROM `{$this->table}`
				 WHERE visited_at >= DATE_SUB(NOW(), INTERVAL %d MONTH)
				 GROUP BY month
				 ORDER BY month ASC",
				$months
			),
			ARRAY_A
		) ?: array();
	}

	/** Recent visits with IP, page, date. */
	public function recent( int $limit = 50 ): array {
		global $wpdb;
		$limit = max( 1, min( 200, $limit ) );
		return $wpdb->get_results( // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare(
				"SELECT id, ip_address, page_slug, page_url, referrer, visited_at
				 FROM `{$this->table}`
				 ORDER BY visited_at DESC
				 LIMIT %d",
				$limit
			),
			ARRAY_A
		) ?: array();
	}

	/** Unique IP list with first/last seen and visit count. */
	public function ip_summary( int $limit = 100 ): array {
		global $wpdb;
		$limit = max( 1, min( 500, $limit ) );
		return $wpdb->get_results( // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->prepare(
				"SELECT ip_address,
				        COUNT(*)     AS visits,
				        MIN(visited_at) AS first_seen,
				        MAX(visited_at) AS last_seen
				 FROM `{$this->table}`
				 GROUP BY ip_address
				 ORDER BY visits DESC
				 LIMIT %d",
				$limit
			),
			ARRAY_A
		) ?: array();
	}

	/** Delete logs older than $days days. */
	public function prune( int $days = 90 ): int {
		global $wpdb;
		$days = max( 1, $days );
		$wpdb->query( $wpdb->prepare( // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"DELETE FROM `{$this->table}` WHERE visited_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
			$days
		) );
		return (int) $wpdb->rows_affected;
	}

	/** Delete ALL visitor logs (truncate). Returns row count removed. */
	public function truncate(): int {
		global $wpdb;
		$count = $this->total();
		$wpdb->query( "TRUNCATE TABLE `{$this->table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $count;
	}
}
