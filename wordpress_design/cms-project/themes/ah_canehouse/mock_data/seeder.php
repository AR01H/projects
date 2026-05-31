<?php
defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/schema/class-schema.php';
require_once get_template_directory() . '/schema/class-data.php';

/**
 * CH Theme Seeder
 * Populates all CMS tables and WordPress options with demo data.
 * Triggered from Theme Admin → Install Mock Data.
 */
class CH_Theme_Seeder {

	// ── Table creation ────────────────────────────────────────────────────────

	public static function create_tables(): void {
		CH_Schema::create_all();
	}

	/** @return array{inserted:int, updated:int, errors:string[]} */
	public static function seed_all(): array {
		self::create_tables();
		$results = [ 'inserted' => 0, 'updated' => 0, 'errors' => [] ];
		// Navigation, footer and FAQs are managed by the CMS plugin and are
		// intentionally NOT seeded here (the theme reads them from the plugin).
		$methods = [
			'seed_settings',
			'seed_home_settings',
			'seed_section_visibility',
			'seed_contact_settings',
			'seed_menu_sizes',
			'seed_cane_types',
			'seed_textures',
			'seed_flavours',
			'seed_order_steps',
			'seed_marquee_items',
			'seed_benefits',
			'seed_hire_packages',
			'seed_hire_features',
			'seed_franchise_locations',
			'seed_juice_showcase',
			'seed_story_settings',
			'seed_story_cards',
			'seed_certifications',
			'seed_reviews',
			'seed_news_bar',
			'seed_journal_page',
			'seed_journal_posts',
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

	/** @return array{deleted:int} */
	public static function cleanup_all(): array {
		global $wpdb;
		$deleted = 0;

		// Truncate CMS tables
		foreach ( [ 'reviews', 'faqs', 'news_bar' ] as $t ) {
			$table = ch_theme_table( $t );
			$wpdb->query( "TRUNCATE TABLE `{$table}`" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$deleted++;
		}

		// Remove all ch_ options
		$options = [
			'ch_site_settings', 'ch_home_settings', 'ch_section_visibility',
			'ch_theme_navigation', 'ch_nav_cta', 'ch_theme_footer', 'ch_contact_settings',
			'ch_menu_sizes', 'ch_cane_types', 'ch_textures', 'ch_flavours',
			'ch_order_steps', 'ch_marquee_items', 'ch_benefits',
			'ch_hire_packages', 'ch_hire_features', 'ch_franchise_locations',
			'ch_juice_showcase', 'ch_story_settings', 'ch_story_cards', 'ch_faqs_manual',
		];
		foreach ( $options as $opt ) {
			if ( delete_option( $opt ) ) {
				$deleted++;
			}
		}

		// Remove seeded WP pages
		foreach ( [ 'journal' ] as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				wp_delete_post( $page->ID, true );
				$deleted++;
			}
		}

		return [ 'deleted' => $deleted ];
	}

	/**
	 * Schema-only install: creates tables + basic settings. Safe to run anytime - idempotent.
	 * @return array{inserted:int, updated:int, errors:string[]}
	 */
	public static function seed_schema_only(): array {
		self::create_tables();
		$results = [ 'inserted' => 0, 'updated' => 0, 'errors' => [] ];
		foreach ( [ 'seed_settings', 'seed_home_settings', 'seed_section_visibility', 'seed_contact_settings' ] as $method ) {
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

	/**
	 * Seed only the explicitly selected content types.
	 * @param string[] $types  Keys from the seed_types[] checkbox array
	 * @return array{inserted:int, updated:int, errors:string[]}
	 */
	public static function seed_selected( array $types ): array {
		// Navigation, footer and FAQs are owned by the CMS plugin - the theme
		// must NOT seed them (it would duplicate/overwrite the plugin's data).
		$map = [
			'story-cards'    => 'seed_story_cards',
			'certifications' => 'seed_certifications',
			'reviews'        => 'seed_reviews',
			'news-bar'       => 'seed_news_bar',
			'journal'        => 'seed_journal_posts',
		];
		$results = [ 'inserted' => 0, 'updated' => 0, 'errors' => [] ];
		foreach ( $types as $type ) {
			$type = sanitize_key( $type );
			if ( ! isset( $map[ $type ] ) ) continue;
			$method = $map[ $type ];
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

	/** Returns DB row counts for the status table on the admin install page. */
	public static function table_counts(): array {
		global $wpdb;
		$counts = [];
		foreach ( [ 'reviews', 'faqs', 'news_bar' ] as $name ) {
			$table           = ch_theme_table( $name );
			$exists          = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table;
			$counts[ $name ] = $exists ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" ) : null; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
		$ct                          = $wpdb->prefix . 'ch_contact_submissions';
		$counts['contact_submissions'] = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $ct ) ) === $ct
			? (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$ct}`" ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			: null;
		return $counts;
	}

	// ── Individual seed methods ───────────────────────────────────────────────

	private static function seed_settings(): array {
		update_option( 'ch_site_settings', wp_json_encode( CH_Data::settings() ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_home_settings(): array {
		update_option( 'ch_home_settings', wp_json_encode( CH_Data::home_settings() ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_section_visibility(): array {
		update_option( 'ch_section_visibility', wp_json_encode( [
			'news_ticker'  => 1,
			'hero'         => 1,
			'marquee'      => 1,
			'how_to_order' => 1,
			'reviews'      => 1,
			'menu_builder' => 1,
			'benefits'     => 1,
			'story'        => 1,
			'hire'         => 1,
			'franchise'    => 1,
			'faqs'         => 1,
			'contact'      => 1,
		] ) );
		return [ 'updated' => 1 ];
	}

	// seed_navigation() and seed_footer() removed - navigation & footer are
	// owned by the CMS plugin (ah_cms_navigation / ah_cms_footer). The theme
	// reads them via ch_get_theme_navigation() / ch_get_theme_footer().

	private static function seed_story_cards(): array {
		$cards = CH_Data::story_cards();
		if ( empty( $cards ) ) return [ 'updated' => 0 ];
		update_option( 'ch_story_cards', wp_json_encode( $cards ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_certifications(): array {
		$certs = CH_Data::certifications();
		if ( empty( $certs ) ) return [ 'updated' => 0 ];
		$settings = get_option( 'ch_site_settings', [] );
		if ( is_string( $settings ) ) $settings = json_decode( $settings, true ) ?: [];
		$settings['certifications'] = wp_json_encode( $certs );
		update_option( 'ch_site_settings', $settings );
		return [ 'updated' => 1 ];
	}

	private static function seed_contact_settings(): array {
		update_option( 'ch_contact_settings', wp_json_encode( CH_Data::contact_settings() ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_menu_sizes(): array {
		update_option( 'ch_menu_sizes', wp_json_encode( CH_Data::menu_sizes() ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_cane_types(): array {
		update_option( 'ch_cane_types', wp_json_encode( CH_Data::cane_types() ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_textures(): array {
		update_option( 'ch_textures', wp_json_encode( CH_Data::textures() ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_flavours(): array {
		update_option( 'ch_flavours', wp_json_encode( CH_Data::flavours() ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_order_steps(): array {
		update_option( 'ch_order_steps', wp_json_encode( CH_Data::order_steps() ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_marquee_items(): array {
		update_option( 'ch_marquee_items', wp_json_encode( CH_Data::marquee_items() ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_benefits(): array {
		update_option( 'ch_benefits', wp_json_encode( CH_Data::benefits() ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_hire_packages(): array {
		update_option( 'ch_hire_packages', wp_json_encode( CH_Data::hire_packages() ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_hire_features(): array {
		update_option( 'ch_hire_features', wp_json_encode( CH_Data::hire_features() ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_franchise_locations(): array {
		update_option( 'ch_franchise_locations', wp_json_encode( CH_Data::franchise_locations() ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_juice_showcase(): array {
		update_option( 'ch_juice_showcase', wp_json_encode( CH_Data::juice_showcase() ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_story_settings(): array {
		update_option( 'ch_story_settings', wp_json_encode( CH_Data::story_settings() ) );
		return [ 'updated' => 1 ];
	}

	private static function seed_reviews(): array {
		global $wpdb;
		$table    = ch_theme_table( 'reviews' );
		$inserted = 0;

		foreach ( CH_Data::reviews() as $row ) {
			$ok = $wpdb->insert(
				$table,
				[
					'author_name' => $row['author_name'] ?? '',
					'location'    => $row['location']    ?? '',
					'review_text' => $row['review_text'] ?? '',
					'rating'      => (float) ( $row['rating'] ?? 5.0 ),
					'result'      => $row['result']      ?? '',
					'status'      => $row['status']      ?? 'active',
				],
				[ '%s', '%s', '%s', '%f', '%s', '%s' ]
			);
			if ( $ok ) {
				$inserted++;
			}
		}

		return [ 'inserted' => $inserted ];
	}

	// seed_faqs() removed - FAQs are managed by the CMS plugin (AH_Model_FAQs).
	// ch_get_faqs() reads them from the plugin; the theme does not seed them.

	private static function seed_news_bar(): array {
		global $wpdb;
		$table    = ch_theme_table( 'news_bar' );
		$inserted = 0;

		foreach ( CH_Data::news_bar() as $row ) {
			$ok = $wpdb->insert(
				$table,
				[
					'message'    => $row['message']    ?? '',
					'status'     => $row['status']     ?? 'active',
					'sort_order' => (int) ( $row['sort_order'] ?? 0 ),
				],
				[ '%s', '%s', '%d' ]
			);
			if ( $ok ) {
				$inserted++;
			}
		}

		return [ 'inserted' => $inserted ];
	}

	private static function seed_journal_page(): array {
		$slug     = 'journal';
		$existing = get_page_by_path( $slug );
		if ( ! $existing ) {
			$id = wp_insert_post( [
				'post_title'   => 'The Cane Journal',
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			] );
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_wp_page_template', 'page-blog.php' );
				return [ 'inserted' => 1, 'updated' => 0 ];
			}
		} else {
			update_post_meta( $existing->ID, '_wp_page_template', 'page-blog.php' );
		}
		return [ 'inserted' => 0, 'updated' => 0 ];
	}

	private static function seed_journal_posts(): array {
		$count = 0;
		foreach ( CH_Data::journal_posts() as $p ) {
			$existing = get_page_by_title( $p['title'], OBJECT, 'post' );
			if ( $existing ) continue;
			$post_id = wp_insert_post( [
				'post_title'   => $p['title'],
				'post_content' => $p['content'] ?? '',
				'post_excerpt' => $p['excerpt'] ?? '',
				'post_status'  => 'publish',
				'post_type'    => 'post',
			] );
			if ( $post_id && ! is_wp_error( $post_id ) ) {
				if ( ! empty( $p['category'] ) ) {
					wp_set_object_terms( $post_id, $p['category'], 'category' );
				}
				$count++;
			}
		}
		return [ 'inserted' => $count, 'updated' => 0 ];
	}
}
