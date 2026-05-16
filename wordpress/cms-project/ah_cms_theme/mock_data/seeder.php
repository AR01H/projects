<?php
defined( 'ABSPATH' ) || exit;

/**
 * AH Theme Seeder
 * Populates all CMS tables and WordPress options with realistic demo data.
 * Triggered from Theme Admin → Install Mock Data.
 */
class AH_Theme_Seeder {

	/** @return array{inserted:int, updated:int, errors:string[]} */
	public static function seed_all(): array {
		$results = [ 'inserted' => 0, 'updated' => 0, 'errors' => [] ];
		$methods = [
			'seed_settings',
			'seed_home_settings',
			'seed_guide_nav',
			'seed_guide_categories',
			'seed_nav_topics',
			'seed_process_steps',
			'seed_site_stats',
			'seed_news_bar',
			'seed_services',
			'seed_team',
			'seed_reviews',
			'seed_faqs',
		];
		foreach ( $methods as $method ) {
			try {
				$r = self::$method();
				$results['inserted'] += $r['inserted'] ?? 0;
				$results['updated']  += $r['updated']  ?? 0;
			} catch ( \Throwable $e ) {
				$results['errors'][] = "{$method}: " . $e->getMessage();
			}
		}
		return $results;
	}

	/** @return array{inserted:int,updated:int} */
	public static function seed_settings(): array {
		update_option( 'ah_site_settings', wp_json_encode( [
			'phone'            => '+44 7747 223762',
			'email'            => 'contact@advaithhomes.co.uk',
			'address'          => 'London & Nationwide',
			'facebook_url'     => 'https://facebook.com/advaithhomes',
			'instagram_url'    => 'https://instagram.com/advaithhomes',
			'twitter_url'      => 'https://twitter.com/advaithhomes',
			'linkedin_url'     => 'https://linkedin.com/company/advaithhomes',
			'youtube_url'      => '',
			'consultation_url' => '/contact/',
			'tagline'          => "The UK's buyer's agent — working exclusively for you.",
		] ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_home_settings(): array {
		update_option( 'ah_home_settings', wp_json_encode( [
			'hero_headline'      => "Your Expert on the<br><em>Buying Side</em>",
			'hero_subline'       => "The UK's only buyer's agent combining deep market access, expert negotiation, and end-to-end coordination — so you buy the right property at the right price.",
			'hero_cta_label'     => 'Book a Free Consultation',
			'hero_cta_url'       => '/contact/',
			'hero_stat_1'        => '£28M+',
			'hero_stat_1_label'  => 'Saved for clients',
			'hero_stat_2'        => '94%',
			'hero_stat_2_label'  => 'Off-market success rate',
			'hero_stat_3'        => '500+',
			'hero_stat_3_label'  => 'Homes secured',
			'hero_stat_4'        => '4.9★',
			'hero_stat_4_label'  => 'Average client rating',
		] ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_guide_nav(): array {
		update_option( 'ah_guide_nav', wp_json_encode( ah_mock_guide_nav() ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_guide_categories(): array {
		update_option( 'ah_guide_categories', wp_json_encode( ah_mock_guide_categories_array() ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_nav_topics(): array {
		update_option( 'ah_nav_buying_topics',  wp_json_encode( ah_mock_nav_buying_topics() ) );
		update_option( 'ah_nav_finance_topics', wp_json_encode( ah_mock_nav_finance_topics() ) );
		update_option( 'ah_nav_legal_topics',   wp_json_encode( ah_mock_nav_legal_topics() ) );
		return [ 'inserted' => 0, 'updated' => 3 ];
	}

	public static function seed_process_steps(): array {
		update_option( 'ah_process_steps', wp_json_encode( ah_mock_process_steps() ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_site_stats(): array {
		update_option( 'ah_site_stats', wp_json_encode( ah_mock_site_stats() ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_news_bar(): array {
		global $wpdb;
		$table = ah_theme_table( 'news_bar' );
		if ( ! self::table_exists( $table ) ) {
			update_option( 'ah_news_bar_items', wp_json_encode( ah_mock_news_bar_items() ) );
			return [ 'inserted' => 0, 'updated' => 1 ];
		}
		$items   = ah_mock_news_bar_items();
		$count   = 0;
		foreach ( $items as $i => $msg ) {
			$wpdb->insert( $table, [ 'message' => $msg, 'status' => 'active', 'sort_order' => $i + 1 ] );
			if ( $wpdb->rows_affected ) $count++;
		}
		return [ 'inserted' => $count, 'updated' => 0 ];
	}

	public static function seed_services(): array {
		global $wpdb;
		$table = ah_theme_table( 'services' );
		if ( ! self::table_exists( $table ) ) return self::skip( 'services table missing' );
		$rows  = ah_mock_services();
		$count = 0;
		foreach ( $rows as $row ) {
			$wpdb->insert( $table, [
				'title'      => $row->title,
				'summary'    => $row->summary,
				'icon'       => $row->icon,
				'status'     => 'active',
				'sort_order' => $row->sort_order,
			] );
			if ( $wpdb->rows_affected ) $count++;
		}
		return [ 'inserted' => $count, 'updated' => 0 ];
	}

	public static function seed_team(): array {
		global $wpdb;
		$table = ah_theme_table( 'team' );
		if ( ! self::table_exists( $table ) ) return self::skip( 'team table missing' );
		$rows  = ah_mock_team();
		$count = 0;
		foreach ( $rows as $i => $row ) {
			$wpdb->insert( $table, [
				'name'       => $row->name,
				'role'       => $row->role,
				'bio'        => $row->bio,
				'photo_url'  => '',
				'status'     => 'active',
				'sort_order' => $i + 1,
			] );
			if ( $wpdb->rows_affected ) $count++;
		}
		return [ 'inserted' => $count, 'updated' => 0 ];
	}

	public static function seed_reviews(): array {
		global $wpdb;
		$table = ah_theme_table( 'reviews' );
		if ( ! self::table_exists( $table ) ) return self::skip( 'reviews table missing' );
		$rows  = ah_mock_reviews();
		$count = 0;
		foreach ( $rows as $row ) {
			$wpdb->insert( $table, [
				'author_name' => $row->author_name,
				'location'    => $row->location,
				'review_text' => $row->review_text,
				'rating'      => $row->rating,
				'result'      => $row->result,
				'status'      => 'active',
			] );
			if ( $wpdb->rows_affected ) $count++;
		}
		return [ 'inserted' => $count, 'updated' => 0 ];
	}

	public static function seed_faqs(): array {
		global $wpdb;
		$table = ah_theme_table( 'faqs' );
		if ( ! self::table_exists( $table ) ) return self::skip( 'faqs table missing' );
		$rows  = ah_mock_faqs();
		$count = 0;
		foreach ( $rows as $row ) {
			$wpdb->insert( $table, [
				'topic'      => $row->topic,
				'question'   => $row->question,
				'answer'     => $row->answer,
				'status'     => 'active',
				'sort_order' => $row->sort_order ?? 0,
			] );
			if ( $wpdb->rows_affected ) $count++;
		}
		return [ 'inserted' => $count, 'updated' => 0 ];
	}

	// ── Cleanup ───────────────────────────────────────────────────────────────

	/** @return array{deleted:int} */
	public static function cleanup_all(): array {
		$deleted = 0;
		$deleted += self::cleanup_db_table( 'services' );
		$deleted += self::cleanup_db_table( 'team' );
		$deleted += self::cleanup_db_table( 'reviews' );
		$deleted += self::cleanup_db_table( 'faqs' );
		$deleted += self::cleanup_db_table( 'news_bar' );

		$options = [
			'ah_site_settings', 'ah_home_settings', 'ah_guide_nav',
			'ah_guide_categories', 'ah_nav_buying_topics', 'ah_nav_finance_topics',
			'ah_nav_legal_topics', 'ah_process_steps', 'ah_site_stats', 'ah_news_bar_items',
		];
		foreach ( $options as $opt ) {
			if ( get_option( $opt ) !== false ) {
				delete_option( $opt );
				$deleted++;
			}
		}
		return [ 'deleted' => $deleted ];
	}

	private static function cleanup_db_table( string $name ): int {
		global $wpdb;
		$table = ah_theme_table( $name );
		if ( ! self::table_exists( $table ) ) return 0;
		return (int) $wpdb->query( "TRUNCATE TABLE `{$table}`" );
	}

	// ── Row counts (for admin status display) ─────────────────────────────────

	public static function table_counts(): array {
		global $wpdb;
		$tables  = [ 'services', 'team', 'reviews', 'faqs', 'news_bar' ];
		$counts  = [];
		foreach ( $tables as $t ) {
			$table = ah_theme_table( $t );
			if ( self::table_exists( $table ) ) {
				$counts[ $t ] = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
			} else {
				$counts[ $t ] = null; // null = table doesn't exist (plugin not installed)
			}
		}
		$options = [ 'ah_site_settings', 'ah_home_settings', 'ah_guide_nav', 'ah_guide_categories', 'ah_process_steps', 'ah_site_stats' ];
		foreach ( $options as $opt ) {
			$counts[ $opt ] = get_option( $opt ) !== false ? '✓' : '—';
		}
		return $counts;
	}

	// ── Utilities ─────────────────────────────────────────────────────────────

	private static function table_exists( string $table ): bool {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
	}

	private static function skip( string $reason ): array {
		return [ 'inserted' => 0, 'updated' => 0, '_skip' => $reason ];
	}
}
