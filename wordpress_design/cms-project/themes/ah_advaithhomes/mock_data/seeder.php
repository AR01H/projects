<?php
defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/schema/class-schema.php';
require_once get_template_directory() . '/schema/class-data.php';

/**
 * AH Theme Seeder
 * Populates all CMS tables and WordPress options with realistic demo data.
 * Triggered from Theme Admin → Install Mock Data.
 */
class AH_Theme_Seeder {

	// ── Table creation (delegated to schema/class-schema.php) ───────────────────

	public static function create_tables(): void {
		AH_Schema::create_all();
	}

	/** @return array{inserted:int, updated:int, skipped:int, errors:string[]} */
	public static function seed_all(): array {
		self::create_tables();
		$results = [ 'inserted' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => [] ];
		$methods = [
			'seed_mandatory_pages',
			'seed_extra_pages',
			'seed_taxonomy_terms',
			'seed_taxonomy_types',
			'seed_settings',
			'seed_home_settings',
			'seed_guide_nav',
			'seed_guide_categories',
			'seed_nav_topics',
			'seed_process_steps',
			'seed_site_stats',
			'seed_trust_signals',
			'seed_news_bar',
			'seed_services',
			'seed_team',
			'seed_reviews',
			'seed_faqs',
			'seed_properties',
			'seed_contact_settings',
			'seed_blog_posts',
			'seed_client_stories',
			'seed_guides_page',
			'seed_blog_page',
			'seed_static_pages',
		];
		$methods[] = 'seed_plugin_tables'; // populate CMS plugin tables if plugin is active
		foreach ( $methods as $method ) {
			try {
				$r = self::$method();
				$results['inserted'] += $r['inserted'] ?? 0;
				$results['updated']  += $r['updated']  ?? 0;
				$results['skipped']  += $r['skipped']  ?? 0;
			} catch ( \Throwable $e ) {
				$results['errors'][] = "{$method}: " . $e->getMessage();
			}
		}
		return $results;
	}

	/**
	 * Schema-only install: creates tables + mandatory pages + taxonomy structure + basic settings.
	 * Safe to run at any time — idempotent. Does NOT install demo content.
	 * @return array{inserted:int, updated:int, skipped:int, errors:string[]}
	 */
	public static function seed_schema_only(): array {
		self::create_tables();
		$results = [ 'inserted' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => [] ];
		foreach ( [ 'seed_mandatory_pages', 'seed_extra_pages', 'seed_taxonomy_types', 'seed_settings' ] as $method ) {
			try {
				$r = self::$method();
				$results['inserted'] += $r['inserted'] ?? 0;
				$results['updated']  += $r['updated']  ?? 0;
				$results['skipped']  += $r['skipped']  ?? 0;
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
			'facebook_url'     => '',
			'instagram_url'    => '',
			'twitter_url'      => '',
			'linkedin_url'     => '',
			'youtube_url'      => '',
			'consultation_url' => '/contact/',
			'tagline'          => "The UK's buyer's agent - working exclusively for you.",
		] ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_home_settings(): array {
		update_option( 'ah_home_settings', wp_json_encode( [
			'hero_headline'      => "Make Smarter<br><em>Property Decisions</em>",
			'hero_subline'       => "Navigating the UK housing market can be complex, but having access to the right information makes all the difference. With unbiased market data, expert guidance, and practical tools, you can make confident property decisions based on facts rather than speculation. Whether you're buying your first home, investing, or simply exploring the market, our insights help you better understand trends, pricing, and opportunities across the UK.",
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
		$navigation = [
			[
			],
			[
			],
		];
		$footer = [
			'brand_description' => "",
			'badge_text'        => '',
			'columns'           => [],
			'contact'           => [],
			'cta'               => [],
			'legal_links'       => [],
		];
		update_option( 'ah_nav_buying_topics',  wp_json_encode( ah_mock_nav_buying_topics() ) );
		update_option( 'ah_nav_finance_topics', wp_json_encode( ah_mock_nav_finance_topics() ) );
		update_option( 'ah_nav_legal_topics',   wp_json_encode( ah_mock_nav_legal_topics() ) );
		update_option( 'ah_cms_navigation', wp_json_encode( $navigation ) );
		update_option( 'ah_theme_navigation', wp_json_encode( $navigation ) );
		update_option( 'ah_cms_footer', wp_json_encode( $footer ) );
		update_option( 'ah_theme_footer', wp_json_encode( $footer ) );
		update_option(
			'ah_cms_nav_cta',
			wp_json_encode(
				[
					'label' => 'Book Free Consultation ->',
					'url'   => '/contact/',
				]
			)
		);
		update_option(
			'ah_nav_cta',
			wp_json_encode(
				[
					'label' => 'Book Free Consultation ->',
					'url'   => '/contact/',
				]
			)
		);
		return [ 'inserted' => 0, 'updated' => 5 ];
	}

	public static function seed_process_steps(): array {
		update_option( 'ah_process_steps', wp_json_encode( ah_mock_process_steps() ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_site_stats(): array {
		update_option( 'ah_site_stats', wp_json_encode( ah_mock_site_stats() ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_trust_signals(): array {
		update_option( 'ah_trust_signals', wp_json_encode( ah_mock_trust_signals() ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	public static function seed_news_bar(): array {
		global $wpdb;
		$csv = AH_Data::load_csv( 'news-bar' );
		if ( empty( $csv ) ) return self::skip( 'news-bar.csv has no rows' );

		$table = ah_theme_table( 'news_bar' );
		if ( ! self::table_exists( $table ) ) {
			// Fallback: store as option when table absent
			update_option( 'ah_news_bar_items', wp_json_encode( array_column( $csv, 'message' ) ) );
			return [ 'inserted' => 0, 'updated' => 1, 'skipped' => 0 ];
		}
		$count = $skipped = 0;
		foreach ( $csv as $row ) {
			$msg = $row['message'] ?? '';
			if ( ! $msg ) continue;
			$snippet = substr( $msg, 0, 120 );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE LEFT(message,120) = %s LIMIT 1", $snippet ) );
			if ( $exists ) { $skipped++; continue; }
			$wpdb->insert( $table, [
				'message'    => $msg,
				'status'     => 'active',
				'sort_order' => (int) ( $row['sort_order'] ?? 0 ),
			] );
			if ( $wpdb->rows_affected ) $count++;
		}
		return [ 'inserted' => $count, 'updated' => 0, 'skipped' => $skipped ];
	}

	public static function seed_services(): array {
		global $wpdb;
		$table = ah_theme_table( 'services' );
		if ( ! self::table_exists( $table ) ) return self::skip( 'services table missing' );
		$csv = AH_Data::load_csv( 'services' );
		if ( empty( $csv ) ) return self::skip( 'services.csv has no rows' );
		$count = $skipped = 0;
		foreach ( $csv as $i => $row ) {
			$title = $row['title'] ?? '';
			if ( ! $title ) continue;
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE title = %s LIMIT 1", $title ) ) ) {
				$skipped++; continue;
			}
			$wpdb->insert( $table, [
				'title'      => $title,
				'summary'    => $row['summary'] ?? '',
				'icon'       => $row['icon'] ?? '',
				'status'     => 'active',
				'sort_order' => (int) ( $row['sort_order'] ?? ( $i + 1 ) ),
			] );
			if ( $wpdb->rows_affected ) $count++;
		}
		return [ 'inserted' => $count, 'updated' => 0, 'skipped' => $skipped ];
	}

	public static function seed_team(): array {
		global $wpdb;
		$table = ah_theme_table( 'team' );
		if ( ! self::table_exists( $table ) ) return self::skip( 'team table missing' );
		$csv = AH_Data::load_csv( 'team' );
		if ( empty( $csv ) ) return self::skip( 'team.csv has no rows' );
		$count = $skipped = 0;
		foreach ( $csv as $i => $row ) {
			$name = $row['name'] ?? '';
			if ( ! $name ) continue;
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE name = %s LIMIT 1", $name ) ) ) {
				$skipped++; continue;
			}
			$wpdb->insert( $table, [
				'name'       => $name,
				'role'       => $row['role'] ?? '',
				'bio'        => $row['bio'] ?? '',
				'photo_url'  => '',
				'status'     => 'active',
				'sort_order' => (int) ( $row['sort_order'] ?? ( $i + 1 ) ),
			] );
			if ( $wpdb->rows_affected ) $count++;
		}
		return [ 'inserted' => $count, 'updated' => 0, 'skipped' => $skipped ];
	}

	public static function seed_reviews(): array {
		global $wpdb;
		$table = ah_theme_table( 'reviews' );
		if ( ! self::table_exists( $table ) ) return self::skip( 'reviews table missing' );
		$csv = AH_Data::load_csv( 'reviews' );
		if ( empty( $csv ) ) return self::skip( 'reviews.csv has no rows' );
		$count = $skipped = 0;
		foreach ( $csv as $row ) {
			$name = $row['author_name'] ?? '';
			if ( ! $name ) continue;
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE author_name = %s LIMIT 1", $name ) ) ) {
				$skipped++; continue;
			}
			$wpdb->insert( $table, [
				'author_name' => $name,
				'location'    => $row['location']    ?? '',
				'review_text' => $row['review_text'] ?? '',
				'rating'      => (float) ( $row['rating'] ?? 5 ),
				'result'      => $row['result']      ?? '',
				'status'      => 'active',
			] );
			if ( $wpdb->rows_affected ) $count++;
		}
		return [ 'inserted' => $count, 'updated' => 0, 'skipped' => $skipped ];
	}

	public static function seed_faqs(): array {
		global $wpdb;
		$table = ah_theme_table( 'faqs' );
		if ( ! self::table_exists( $table ) ) return self::skip( 'faqs table missing' );
		$csv = AH_Data::load_csv( 'faqs' );
		if ( empty( $csv ) ) return self::skip( 'faqs.csv has no rows' );
		$count = $skipped = 0;
		foreach ( $csv as $i => $row ) {
			$question = $row['question'] ?? '';
			if ( ! $question ) continue;
			$snippet = substr( $question, 0, 100 );
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$table}` WHERE LEFT(question,100) = %s LIMIT 1", $snippet ) ) ) {
				$skipped++; continue;
			}
			$wpdb->insert( $table, [
				'topic'      => $row['topic']      ?? '',
				'question'   => $question,
				'answer'     => $row['answer']     ?? '',
				'status'     => 'active',
				'sort_order' => (int) ( $row['sort_order'] ?? ( $i + 1 ) ),
			] );
			if ( $wpdb->rows_affected ) $count++;
		}
		return [ 'inserted' => $count, 'updated' => 0, 'skipped' => $skipped ];
	}

	public static function seed_properties(): array {
		$csv = AH_Data::load_csv( 'properties' );
		if ( empty( $csv ) ) return self::skip( 'properties.csv has no rows' );
		$props = [];
		foreach ( $csv as $row ) {
			$loc = $row['location'] ?? '';
			if ( ! $loc ) continue;
			$props[] = [
				'emoji'    => $row['emoji']   ?? '🏠',
				'price'    => $row['price']   ?? '',
				'location' => $loc,
				'area'     => $row['area']    ?? '',
				'saved'    => $row['saved']   ?? '',
				'type'     => $row['type']    ?? '',
				'beds'     => (int) ( $row['beds'] ?? 0 ),
				'result'   => $row['result']  ?? '',
			];
		}
		update_option( 'ah_featured_properties', wp_json_encode( $props ) );
		return [ 'inserted' => 0, 'updated' => 1, 'skipped' => 0 ];
	}

	public static function seed_contact_settings(): array {
		update_option( 'ah_contact_settings', wp_json_encode( [
			'recipient_email' => get_option( 'admin_email' ),
			'subject_prefix'  => '[Advaith Homes Enquiry]',
			'thank_you_msg'   => "Thanks for getting in touch! We'll respond within one working day.",
		] ) );
		return [ 'inserted' => 0, 'updated' => 1 ];
	}

	// ── Taxonomy types + default terms ───────────────────────────────────────

	public static function seed_taxonomy_types(): array {
		global $wpdb;

		// Uses the CMS plugin tables — skip silently when plugin is not active.
		if ( ! class_exists( 'AH_DB_Helper' ) ) {
			return self::skip( 'CMS plugin not active — taxonomy types not seeded' );
		}

		$tt = AH_DB_Helper::table( 'taxonomy_types' ); // wp_ah_taxonomy_types
		$tm = AH_DB_Helper::table( 'taxonomies' );      // wp_ah_taxonomies

		if ( ! self::table_exists( $tt ) || ! self::table_exists( $tm ) ) {
			return self::skip( 'Plugin taxonomy tables not found — activate plugin first' );
		}

		$inserted = 0;
		foreach ( AH_Data::taxonomy_types() as $type ) {
			// Get or create the type row
			$type_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$tt}` WHERE slug = %s", $type['slug'] ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( ! $type_id ) {
				$wpdb->insert( $tt, [
					'name'        => $type['name'],
					'slug'        => $type['slug'],
					'description' => $type['description'] ?? '',
				] );
				$type_id = (int) $wpdb->insert_id;
				$inserted++;
			}

			foreach ( $type['terms'] ?? [] as $term ) {
				$exists = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$tm}` WHERE type_id = %d AND slug = %s", $type_id, $term['slug'] ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				if ( ! $exists ) {
					$wpdb->insert( $tm, [
						'type_id'    => $type_id,
						'name'       => $term['name'],
						'slug'       => $term['slug'],
						'sort_order' => $term['sort_order'] ?? 0,
						'status'     => 'active',
					] );
					$inserted++;
				}
			}
		}

		return [ 'inserted' => $inserted, 'updated' => 0 ];
	}

	/** Creates policy, legal, and utility pages defined in AH_Data::extra_pages() / pages.csv. */
	public static function seed_extra_pages(): array {
		$count = 0;
		foreach ( AH_Data::extra_pages() as $slug => $cfg ) {
			$existing = get_page_by_path( $slug );
			if ( ! $existing ) {
				$id = wp_insert_post( [
					'post_title'   => $cfg['title'],
					'post_name'    => $slug,
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_content' => $cfg['content'] ?? '',
				] );
				if ( $id && ! is_wp_error( $id ) ) {
					if ( ! empty( $cfg['template'] ) ) {
						update_post_meta( $id, '_wp_page_template', $cfg['template'] );
					}
					$count++;
				}
			} elseif ( ! empty( $cfg['template'] ) ) {
				update_post_meta( $existing->ID, '_wp_page_template', $cfg['template'] );
			}
		}
		return [ 'inserted' => $count, 'updated' => 0 ];
	}

	// ── Mandatory WP pages ───────────────────────────────────────────────────

	public static function seed_mandatory_pages(): array {
		$pages = [
			'home'           => [ 'title' => 'Home',          'template' => '' ],
			'about'          => [ 'title' => 'About Us',       'template' => 'page-about.php' ],
			'services'       => [ 'title' => 'Our Services',   'template' => 'page-services.php' ],
			'client-stories' => [ 'title' => 'Client Stories', 'template' => 'page-client-stories.php' ],
			'reviews'        => [ 'title' => 'Client Stories', 'template' => 'page-client-stories.php' ],
			'contact'        => [ 'title' => 'Contact',        'template' => 'page-contact.php' ],
			'contact-us'        => [ 'title' => 'Contact',        'template' => 'page-contact.php' ],
			'guides'         => [ 'title' => 'Buying Guides',  'template' => 'page-guides.php' ],
			'blog'           => [ 'title' => 'Blog',           'template' => 'page-blog.php' ],
			'news'           => [ 'title' => 'News',           'template' => 'page-news.php' ],
			'faq'            => [ 'title' => 'FAQ',            'template' => 'page-faq.php' ],
		];
		$count = 0;
		foreach ( $pages as $slug => $cfg ) {
			$existing = get_page_by_path( $slug );
			if ( ! $existing ) {
				$id = wp_insert_post( [
					'post_title'   => $cfg['title'],
					'post_name'    => $slug,
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_content' => '',
				] );
				if ( $id && ! is_wp_error( $id ) ) {
					if ( $cfg['template'] ) {
						update_post_meta( $id, '_wp_page_template', $cfg['template'] );
					}
					$count++;
				}
			} elseif ( $cfg['template'] ) {
				update_post_meta( $existing->ID, '_wp_page_template', $cfg['template'] );
			}
		}

		// Set Home as the static front page and Blog as the posts archive page.
		$home_page = get_page_by_path( 'home' );
		if ( $home_page ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $home_page->ID );
		}
		$blog_page = get_page_by_path( 'blog' );
		if ( $blog_page ) {
			update_option( 'page_for_posts', $blog_page->ID );
		}

		return [ 'inserted' => $count, 'updated' => 0 ];
	}

	// ── Taxonomy terms ────────────────────────────────────────────────────────

	public static function seed_taxonomy_terms(): array {
		$count = 0;

		// WP native categories used by seed_blog_posts and seed_client_stories
		foreach ( [ 'Buying Guides', 'Finance & Mortgages', 'Legal & Conveyancing', 'Market Updates', 'Client Stories' ] as $name ) {
			if ( ! term_exists( $name, 'category' ) ) {
				$t = wp_insert_term( $name, 'category' );
				if ( ! is_wp_error( $t ) ) $count++;
			}
		}

		// All other taxonomy types and terms are managed in ah_taxonomy_types / ah_taxonomy_terms
		// via seed_taxonomy_types() — no WP register_taxonomy() calls needed.

		return [ 'inserted' => $count, 'updated' => 0 ];
	}

	public static function seed_blog_posts(): array {
		$csv = AH_Data::load_csv( 'blog-posts' );
		if ( empty( $csv ) ) return self::skip( 'blog-posts.csv has no rows' );

		// Ensure WP categories for posts exist
		foreach ( [ 'Buying Guides', 'Finance & Mortgages', 'Legal & Conveyancing', 'Market Updates', 'Client Stories' ] as $cat ) {
			if ( ! term_exists( $cat, 'category' ) ) wp_insert_term( $cat, 'category' );
		}

		$count = $skipped = 0;
		foreach ( $csv as $row ) {
			$title = $row['title'] ?? '';
			$slug  = $row['slug']  ?? sanitize_title( $title );
			if ( ! $title ) continue;
			if ( get_page_by_path( $slug, OBJECT, 'post' ) ) { $skipped++; continue; }
			$post_id = wp_insert_post( [
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_content' => $row['content'] ?? '',
				'post_excerpt' => $row['excerpt'] ?? '',
				'post_status'  => 'publish',
				'post_type'    => 'post',
			] );
			if ( $post_id && ! is_wp_error( $post_id ) ) {
				if ( ! empty( $row['category'] ) ) wp_set_object_terms( $post_id, $row['category'], 'category' );
				if ( ! empty( $row['featured'] ) && $row['featured'] === '1' ) {
					update_post_meta( $post_id, '_ah_featured', '1' );
				}
				$count++;
			}
		}
		return [ 'inserted' => $count, 'updated' => 0, 'skipped' => $skipped ];
	}

	/** Seed client stories as WP posts with 'Client Stories' category. */
	public static function seed_client_stories(): array {
		$csv = AH_Data::load_csv( 'client-stories' );
		if ( empty( $csv ) ) return self::skip( 'client-stories.csv has no rows' );

		if ( ! term_exists( 'Client Stories', 'category' ) ) wp_insert_term( 'Client Stories', 'category' );

		$count = $skipped = 0;
		foreach ( $csv as $row ) {
			$title = $row['title'] ?? '';
			$slug  = $row['slug']  ?? sanitize_title( $title );
			if ( ! $title ) continue;
			if ( get_page_by_path( $slug, OBJECT, 'post' ) ) { $skipped++; continue; }
			$post_id = wp_insert_post( [
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_content' => $row['content']  ?? '',
				'post_excerpt' => $row['excerpt']  ?? '',
				'post_status'  => 'publish',
				'post_type'    => 'post',
			] );
			if ( $post_id && ! is_wp_error( $post_id ) ) {
				$cat = ! empty( $row['category'] ) ? $row['category'] : 'Client Stories';
				wp_set_object_terms( $post_id, $cat, 'category' );
				if ( ! empty( $row['featured'] ) && $row['featured'] === '1' ) {
					update_post_meta( $post_id, '_ah_featured', '1' );
				}
				// Store structured meta for template use
				foreach ( [ 'buyer_name', 'property_type', 'purchase_price', 'amount_saved', 'result_headline' ] as $key ) {
					if ( ! empty( $row[ $key ] ) ) update_post_meta( $post_id, "_ah_{$key}", $row[ $key ] );
				}
				$count++;
			}
		}
		return [ 'inserted' => $count, 'updated' => 0, 'skipped' => $skipped ];
	}

	public static function seed_guides_page(): array {
		$slug = 'guides';
		$existing = get_page_by_path( $slug );
		if ( ! $existing ) {
			$id = wp_insert_post( [
				'post_title'   => 'Buying Guides',
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			] );
			if ( $id && ! is_wp_error( $id ) ) {
				update_post_meta( $id, '_wp_page_template', 'page-guides.php' );
				return [ 'inserted' => 1, 'updated' => 0 ];
			}
		} else {
			update_post_meta( $existing->ID, '_wp_page_template', 'page-guides.php' );
		}
		return [ 'inserted' => 0, 'updated' => 0 ];
	}

	public static function seed_blog_page(): array {
		$slug = 'blog';
		$existing = get_page_by_path( $slug );
		if ( ! $existing ) {
			$id = wp_insert_post( [
				'post_title'   => 'Blog',
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

	public static function seed_static_pages(): array {
		$dir = trailingslashit( get_template_directory() ) . 'static/';
		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
		}
		$defs = self::static_page_defs();
		$fc   = 0;
		$pc   = 0;
		foreach ( $defs as $slug => $page ) {
			if ( ! file_exists( $dir . $slug . '.html' ) ) {
				file_put_contents( $dir . $slug . '.html', $page['html'] );
				$fc++;
			}
			$existing = get_page_by_path( $slug );
			if ( ! $existing ) {
				$id = wp_insert_post( [
					'post_title'   => $page['title'],
					'post_name'    => $slug,
					'post_status'  => 'publish',
					'post_type'    => 'page',
					'post_content' => '',
					'post_excerpt' => $page['excerpt'] ?? '',
				] );
				if ( $id && ! is_wp_error( $id ) ) {
					update_post_meta( $id, '_ah_static_page', $slug );
					update_post_meta( $id, '_wp_page_template', 'template-static-page.php' );
					$pc++;
				}
			} else {
				update_post_meta( $existing->ID, '_ah_static_page', $slug );
				update_post_meta( $existing->ID, '_wp_page_template', 'template-static-page.php' );
			}
		}
		return [ 'inserted' => $fc + $pc, 'updated' => 0 ];
	}

	private static function sp_css(): string {
		return '<style>*{box-sizing:border-box}body{font-family:system-ui,-apple-system,sans-serif;max-width:760px;margin:40px auto;padding:0 24px;color:#1e293b;line-height:1.7}h1{font-size:1.75rem;font-weight:800;margin-bottom:6px}h2{font-size:1.1rem;font-weight:700;margin:26px 0 8px;color:#0f172a}p.sub{color:#64748b;margin:0 0 24px}.card{background:#fff;border:1.5px solid #e2e8f0;border-radius:12px;padding:24px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,.05)}label{display:block;font-weight:600;font-size:.875rem;margin-bottom:6px;color:#374151}input[type=number],select{width:100%;padding:10px 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:1rem;margin-bottom:18px;font-family:inherit}input:focus,select:focus{border-color:#b7791f;outline:none;box-shadow:0 0 0 3px rgba(183,121,31,.12)}.btn{width:100%;background:#b7791f;color:#fff;border:none;padding:13px;border-radius:8px;font-size:1rem;font-weight:700;cursor:pointer;font-family:inherit}.btn:hover{background:#7c4a08}.res{background:linear-gradient(135deg,#b7791f,#7c4a08);color:#fff;border-radius:12px;padding:22px;margin-top:16px;display:none}.amt{font-size:2rem;font-weight:800;margin:4px 0}.brk{font-size:.875rem;opacity:.9;margin-top:10px;line-height:1.9}.badge{display:inline-block;background:#fef3c7;color:#92400e;border:1px solid #fde68a;border-radius:20px;padding:3px 12px;font-size:.75rem;font-weight:700;margin-bottom:20px}table{width:100%;border-collapse:collapse;font-size:.875rem}th,td{padding:9px 14px;border-bottom:1px solid #f1f5f9;text-align:left}th{font-size:.75rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8}tr:last-child td{border:none}.ci{display:flex;align-items:baseline;gap:10px;padding:9px 0;border-bottom:1px solid #f1f5f9}.ci input[type=checkbox]{width:16px;height:16px;flex-shrink:0;accent-color:#b7791f;cursor:pointer;margin-top:2px}.ci label{font-size:.9rem;cursor:pointer;color:#374151}dt{font-weight:700;color:#0f172a;margin-top:18px}dd{color:#64748b;margin:4px 0 0 12px;padding-left:12px;border-left:3px solid #fde68a;font-size:.875rem}.sec{background:#f8fafc;border-radius:8px;padding:16px 20px;margin:12px 0}.sec p{color:#374151;margin:4px 0 0}@media(max-width:600px){h1{font-size:1.35rem}.amt{font-size:1.6rem}}</style>';
	}

	private static function sp_page( string $title, string $body, string $badge = '' ): string {
		$b = $badge ? '<span class="badge">' . htmlspecialchars( $badge, ENT_QUOTES, 'UTF-8' ) . '</span>' : '';
		return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'
			. htmlspecialchars( $title, ENT_QUOTES, 'UTF-8' ) . ' | Advaith Homes</title>'
			. self::sp_css() . '</head><body>' . $b . $body . '</body></html>';
	}

	private static function static_page_defs(): array {
		return [

			'stamp-duty-calculator' => [
				'title'   => 'Stamp Duty Calculator',
				'excerpt' => 'Calculate your stamp duty land tax for any UK property purchase, updated for 2025.',
				'html'    => self::sp_page(
					'Stamp Duty Calculator',
					'<h1>Stamp Duty Calculator 2025</h1><p class="sub">Calculate your Stamp Duty Land Tax (SDLT) instantly. Updated for 2025 thresholds - covers standard purchases, first-time buyers, and additional properties.</p><div class="card"><label for="sp">Property price (&pound;)</label><input type="number" id="sp" placeholder="e.g. 450000" min="0" step="1000"><label for="st">Buyer type</label><select id="st"><option value="std">Standard buyer (main home)</option><option value="ftb">First-time buyer</option><option value="btl">Additional property / Buy-to-let</option></select><button class="btn" onclick="sdlt()">Calculate Stamp Duty</button><div class="res" id="sr"><p style="opacity:.8;font-size:.875rem;margin:0">Your SDLT liability</p><div class="amt" id="sa"></div><div class="brk" id="sb"></div></div></div><script>function sf(n){return"£"+Math.round(n).toLocaleString("en-GB")}function sdlt(){var p=parseFloat(document.getElementById("sp").value)||0,t=document.getElementById("st").value,x=0,ls=[];if(t==="ftb"&&p<=425e3){ls=["No SDLT - first-time buyer relief applies (up to £425,000)"]}else if(t==="ftb"&&p<=625e3){x=(p-425e3)*.05;ls=["5% on "+sf(p-425e3)+" above £425k limit = "+sf(x)]}else{var bs=t==="btl"?[[25e4,.05],[925e3,.1],[15e5,.15],[1e18,.17]]:[[25e4,0],[925e3,.05],[15e5,.1],[1e18,.12]],pr=0;bs.forEach(function(b){if(p>pr){var c=Math.min(p,b[0])-pr,v=c*b[1];x+=v;if(v>0)ls.push(b[1]*100+"% on "+sf(c)+" = "+sf(v));pr=b[0]}})}if(t==="btl")ls.unshift("3% surcharge applies (additional / BTL property)");document.getElementById("sa").textContent=sf(x);document.getElementById("sb").innerHTML=ls.join("<br>")||"No stamp duty payable";document.getElementById("sr").style.display="block"}</script>',
					'Free Calculator'
				),
			],

			'mortgage-calculator' => [
				'title'   => 'Mortgage Calculator',
				'excerpt' => 'Estimate your monthly mortgage repayments and total interest over the term.',
				'html'    => self::sp_page(
					'Mortgage Calculator',
					'<h1>Mortgage Repayment Calculator</h1><p class="sub">Estimate monthly payments and total interest cost. For personalised advice, speak with a qualified mortgage broker.</p><div class="card"><label for="ml">Loan amount (&pound;)</label><input type="number" id="ml" placeholder="e.g. 300000" min="0" step="5000"><label for="mr">Annual interest rate (%)</label><input type="number" id="mr" placeholder="e.g. 4.5" min="0" max="30" step="0.1"><label for="mt">Mortgage term (years)</label><input type="number" id="mt" placeholder="e.g. 25" min="1" max="40" step="1"><button class="btn" onclick="mcalc()">Calculate</button><div class="res" id="mr2"><p style="opacity:.8;font-size:.875rem;margin:0">Monthly repayment</p><div class="amt" id="ma"></div><div class="brk" id="mb"></div></div></div><script>function mf(n){return"£"+Math.round(n).toLocaleString("en-GB")}function mcalc(){var L=parseFloat(document.getElementById("ml").value)||0,R=parseFloat(document.getElementById("mr").value)||0,Y=parseFloat(document.getElementById("mt").value)||0;if(!L||!R||!Y){alert("Please fill in all three fields");return}var r=R/100/12,n=Y*12,pmt=r?L*(r*Math.pow(1+r,n))/(Math.pow(1+r,n)-1):L/n,tot=pmt*n,ti=tot-L;document.getElementById("ma").textContent=mf(pmt)+" / month";document.getElementById("mb").innerHTML="Total repaid: "+mf(tot)+"<br>Total interest: "+mf(ti)+"<br>Loan (capital): "+mf(L);document.getElementById("mr2").style.display="block"}</script>',
					'Free Calculator'
				),
			],

			'first-time-buyer-checklist' => [
				'title'   => 'First-Time Buyer Checklist',
				'excerpt' => 'A step-by-step checklist covering everything first-time buyers need before, during and after purchase.',
				'html'    => self::sp_page(
					'First-Time Buyer Checklist',
					'<h1>First-Time Buyer Checklist</h1><p class="sub">Work through each step in order. Tick items off as you complete them.</p><div class="card"><h2>1. Finances</h2><div class="ci"><input type="checkbox" id="c1"><label for="c1">Check your credit report free via Experian, Equifax, or Credit Karma</label></div><div class="ci"><input type="checkbox" id="c2"><label for="c2">Calculate your total budget: deposit + stamp duty + legal fees + survey costs</label></div><div class="ci"><input type="checkbox" id="c3"><label for="c3">Get an Agreement in Principle (AIP) from a lender or mortgage broker</label></div><div class="ci"><input type="checkbox" id="c4"><label for="c4">Check eligibility for a Lifetime ISA (25% government bonus on savings up to &pound;4,000/year)</label></div><div class="ci"><input type="checkbox" id="c5"><label for="c5">Confirm stamp duty position - no SDLT on first &pound;425k for properties up to &pound;625k</label></div></div><div class="card"><h2>2. Search and View</h2><div class="ci"><input type="checkbox" id="c6"><label for="c6">Register with local estate agents and set up Rightmove / Zoopla alerts</label></div><div class="ci"><input type="checkbox" id="c7"><label for="c7">View at least 5 to 8 properties before making an offer</label></div><div class="ci"><input type="checkbox" id="c8"><label for="c8">Research recent sold prices in target area via Land Registry data</label></div><div class="ci"><input type="checkbox" id="c9"><label for="c9">Ask the vendor why they are selling and how long the property has been listed</label></div></div><div class="card"><h2>3. Offer and Legal</h2><div class="ci"><input type="checkbox" id="c10"><label for="c10">Submit your offer in writing via the estate agent</label></div><div class="ci"><input type="checkbox" id="c11"><label for="c11">Instruct a solicitor or licensed conveyancer immediately after offer acceptance</label></div><div class="ci"><input type="checkbox" id="c12"><label for="c12">Submit full mortgage application within 2 to 3 weeks of offer acceptance</label></div><div class="ci"><input type="checkbox" id="c13"><label for="c13">Book a RICS HomeBuyer Report or full Building Survey</label></div><div class="ci"><input type="checkbox" id="c14"><label for="c14">Review results of local authority, water, and environmental searches</label></div></div><div class="card"><h2>4. Exchange and Completion</h2><div class="ci"><input type="checkbox" id="c15"><label for="c15">Pay exchange deposit (typically 10% of purchase price)</label></div><div class="ci"><input type="checkbox" id="c16"><label for="c16">Agree a completion date with the vendor</label></div><div class="ci"><input type="checkbox" id="c17"><label for="c17">Arrange buildings insurance to begin from date of exchange</label></div><div class="ci"><input type="checkbox" id="c18"><label for="c18">Transfer remaining balance to solicitor and collect your keys!</label></div></div>',
					'Free Guide'
				),
			],

			'property-glossary' => [
				'title'   => 'Property Glossary',
				'excerpt' => 'Plain-English definitions of UK property buying terms from AIP to title deeds.',
				'html'    => self::sp_page(
					'Property Glossary',
					'<h1>UK Property Glossary</h1><p class="sub">Plain-English definitions of terms you will encounter when buying a property in the UK.</p><dl><dt>Agreement in Principle (AIP)</dt><dd>A conditional indication from a lender of how much they will lend. Not a binding offer, but signals to sellers that you are a credible buyer. Also known as a Mortgage in Principle or Decision in Principle.</dd><dt>Chain</dt><dd>A sequence of linked transactions where each purchase depends on another completing simultaneously. Chains collapse if any participant withdraws. Chain-free purchases complete faster and with less risk.</dd><dt>Completion</dt><dd>The final stage of purchase. Ownership transfers, the balance is paid, and keys are handed over. Usually 1 to 4 weeks after exchange of contracts.</dd><dt>Conveyancing</dt><dd>The legal transfer of property ownership from seller to buyer, handled by a solicitor or licensed conveyancer. Typically costs &pound;1,000 to &pound;2,500.</dd><dt>Exchange of Contracts</dt><dd>The stage where both parties sign identical contracts and a deposit (usually 10%) is transferred. The sale becomes legally binding. Neither party can withdraw without significant financial penalty.</dd><dt>Freehold</dt><dd>Outright ownership of the property and the land it stands on, indefinitely. The most straightforward ownership structure for houses.</dd><dt>Ground Rent</dt><dd>An annual charge paid by a leaseholder to the freeholder. Under the Leasehold Reform Act 2022, new residential leases must have zero ground rent.</dd><dt>Land Registry</dt><dd>The government body that records all land and property ownership in England and Wales. Your ownership is registered here after completion.</dd><dt>Leasehold</dt><dd>Ownership of a property for a fixed term (e.g. 125 years) under the terms of a lease. Common for flats. Leases below 80 years can be costly to extend.</dd><dt>SDLT (Stamp Duty Land Tax)</dt><dd>A tax on residential purchases over &pound;250,000 in England. Rates: 0% to 12% standard (17% for additional properties). First-time buyers pay no SDLT on the first &pound;425,000 of purchases up to &pound;625,000.</dd><dt>Service Charge</dt><dd>A fee paid by leaseholders for maintenance of shared areas and building structure. Common in flats. Can vary significantly year to year.</dd><dt>Survey</dt><dd>A professional inspection of a property. Types: basic Mortgage Valuation (for lender only), RICS HomeBuyer Report, and the comprehensive Building Survey.</dd><dt>Title Deeds</dt><dd>Documents evidencing ownership and the history of a property. Now held electronically by HM Land Registry for most UK properties.</dd></dl>',
					'Reference'
				),
			],

			'conveyancing-explained' => [
				'title'   => 'Conveyancing Explained',
				'excerpt' => 'What conveyancing is, how long it takes, and what to expect at each stage of the legal process.',
				'html'    => self::sp_page(
					'Conveyancing Explained',
					'<h1>Conveyancing Explained</h1><p class="sub">A plain-English guide to the legal process of buying a property in England.</p><div class="sec"><h2>What is conveyancing?</h2><p>Conveyancing is the legal transfer of property ownership from seller to buyer. A solicitor or licensed conveyancer handles it, covering the draft contract, mortgage deed, searches, and Land Registry registration.</p></div><div class="sec"><h2>How long does it take?</h2><p>Typically 8 to 16 weeks from offer acceptance to completion. Chain-free purchases are faster. Common delays include slow mortgage offers, missing paperwork, and chain complications.</p></div><h2>Stages at a glance</h2><table><thead><tr><th>Stage</th><th>Who</th><th>Timing</th></tr></thead><tbody><tr><td>Instruct solicitor</td><td>Buyer</td><td>Day 1 to 3</td></tr><tr><td>Draft contract issued</td><td>Vendor solicitor</td><td>Week 1 to 2</td></tr><tr><td>Local searches ordered</td><td>Buyer solicitor</td><td>Week 1 to 3</td></tr><tr><td>Mortgage offer received</td><td>Lender</td><td>Week 2 to 6</td></tr><tr><td>Searches returned</td><td>Local authority</td><td>Week 3 to 8</td></tr><tr><td>Enquiries resolved</td><td>Both solicitors</td><td>Week 4 to 10</td></tr><tr><td>Exchange of contracts</td><td>Both solicitors</td><td>Week 8 to 14</td></tr><tr><td>Completion</td><td>Solicitors + lender</td><td>1 to 4 weeks after exchange</td></tr></tbody></table><h2>Typical costs</h2><table><thead><tr><th>Item</th><th>Typical cost</th></tr></thead><tbody><tr><td>Solicitor / conveyancer fees</td><td>&pound;900 to &pound;2,000</td></tr><tr><td>Local authority searches</td><td>&pound;250 to &pound;450</td></tr><tr><td>Land Registry fee</td><td>&pound;30 to &pound;910 (by price)</td></tr><tr><td>CHAPS bank transfer fee</td><td>&pound;25 to &pound;50</td></tr><tr><td>ID verification check</td><td>&pound;10 to &pound;20</td></tr></tbody></table>',
					'Guide'
				),
			],

			'privacy-policy' => [
				'title'   => 'Privacy Policy',
				'excerpt' => 'How Advaith Homes collects, uses and protects your personal data in line with UK GDPR.',
				'html'    => self::sp_page(
					'Privacy Policy',
					'<h1>Privacy Policy</h1><p class="sub">Last updated: May 2025. Advaith Homes is committed to protecting your personal data in line with UK GDPR and the Data Protection Act 2018.</p><div class="sec"><h2>Who we are</h2><p>Advaith Homes is a buyer\'s agent operating across the UK. Contact: <strong>contact@advaithhomes.co.uk</strong></p></div><h2>Data we collect</h2><table><thead><tr><th>Category</th><th>Examples</th><th>Purpose</th></tr></thead><tbody><tr><td>Contact data</td><td>Name, email, phone</td><td>Responding to enquiries</td></tr><tr><td>Property preferences</td><td>Budget, area, property type</td><td>Tailoring our service</td></tr><tr><td>Usage data</td><td>Pages visited, session time</td><td>Improving our website</td></tr><tr><td>Cookie data</td><td>Session ID, analytics IDs</td><td>Site functionality and analytics</td></tr></tbody></table><h2>Legal basis for processing</h2><table><thead><tr><th>Activity</th><th>Legal basis</th></tr></thead><tbody><tr><td>Responding to enquiries</td><td>Legitimate interests</td></tr><tr><td>Providing buyer\'s agent service</td><td>Contract performance</td></tr><tr><td>Sending market updates</td><td>Consent (opt-in)</td></tr></tbody></table><h2>Your rights under UK GDPR</h2><p class="sub">You have the right to: access your data, correct inaccuracies, request deletion, restrict processing, and withdraw consent at any time. Contact <strong>contact@advaithhomes.co.uk</strong> to exercise any right.</p><h2>Data retention</h2><p class="sub">Contact data retained for 2 years from last interaction. Analytics data retained for 13 months.</p>'
				),
			],

			'cookie-policy' => [
				'title'   => 'Cookie Policy',
				'excerpt' => 'Information about the cookies used on the Advaith Homes website and how to manage them.',
				'html'    => self::sp_page(
					'Cookie Policy',
					'<h1>Cookie Policy</h1><p class="sub">Last updated: May 2025. This page explains how Advaith Homes uses cookies on our website.</p><div class="sec"><h2>What are cookies?</h2><p>Cookies are small text files placed on your device when you visit a website. They help us recognise returning visitors and understand site usage.</p></div><h2>Cookies we use</h2><table><thead><tr><th>Cookie</th><th>Type</th><th>Purpose</th><th>Duration</th></tr></thead><tbody><tr><td>wordpress_*</td><td>Essential</td><td>WordPress session management</td><td>Session</td></tr><tr><td>wordpress_logged_in_*</td><td>Essential</td><td>Keeps admin users logged in</td><td>Session</td></tr><tr><td>_ga, _gid</td><td>Analytics</td><td>Google Analytics - anonymous visit tracking</td><td>2 years / 24h</td></tr><tr><td>ah_consent</td><td>Functional</td><td>Remembers your cookie consent choice</td><td>1 year</td></tr></tbody></table><h2>Managing cookies</h2><p class="sub">Control cookies through your browser settings. Blocking essential cookies may affect site functionality. To opt out of Google Analytics, use the official opt-out add-on at tools.google.com/dlpage/gaoptout</p>'
				),
			],

		];
	}

	// ── Plugin tables seeder ─────────────────────────────────────────────────

	/** Populate all CMS plugin DB tables with realistic demo data.
	 *  Silently skips when the plugin schema is not installed.
	 *  @return array{inserted:int,updated:int} */
	public static function seed_plugin_tables(): array {
		global $wpdb;
		$pfx = $wpdb->prefix . 'ah_';

		if ( ! self::table_exists( "{$pfx}pages" ) ) {
			return self::skip( 'CMS plugin tables not found - activate the plugin first' );
		}

		$count = 0;

		// ── Site settings ─────────────────────────────────────────────────────
		$st = "{$pfx}site_settings";
		if ( self::table_exists( $st ) ) {
			$sm = [
				'site_name'        => 'Advaith Homes',
				'contact_email'    => 'contact@advaithhomes.co.uk',
				'contact_phone'    => '+44 7747 223762',
				'whatsapp_number'  => '+44 7747 223762',
				'whatsapp'         => '+44 7747 223762',
				'email'            => 'contact@advaithhomes.co.uk',
				'phone'            => '+44 7747 223762',
				'address'          => 'London & Nationwide, UK',
				'facebook_url'     => '',
				'twitter_url'      => '',
				'linkedin_url'     => '',
				'instagram_url'    => '',
				'youtube_url'      => '',
				'consultation_url' => '/contact/',
				'footer_tagline'   => "The UK's buyer's agent - working exclusively for you.",
				'primary_color'    => '#b7791f',
			];
			foreach ( $sm as $key => $val ) {
				if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$st}` WHERE setting_key = %s", $key ) ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$wpdb->update( $st, [ 'setting_val' => $val ], [ 'setting_key' => $key ] );
				}
			}
		}

		// ── Resolve page IDs ──────────────────────────────────────────────────
		$pt         = "{$pfx}pages";
		$home_id    = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$pt}` WHERE slug = %s", 'home' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$about_id   = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$pt}` WHERE slug = %s", 'about' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$svc_id     = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$pt}` WHERE slug = %s", 'services' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$contact_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$pt}` WHERE slug = %s", 'contact' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$cs_id      = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$pt}` WHERE slug = %s", 'client-stories' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// ── Home: Hero ────────────────────────────────────────────────────────
		$ht = "{$pfx}section_hero";
		if ( $home_id && self::table_exists( $ht ) && ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$ht}` WHERE page_id = %d", $home_id ) ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->insert( $ht, [
				'page_id'            => $home_id,
				'badge_text'         => "#1 Trusted Buyer's Agent",
				'heading'            => 'Make Smarter Property Decisions',
				'subheading'         => "Navigating the UK housing market can be complex, but having access to the right information makes all the difference. With unbiased market data, expert guidance, and practical tools, you can make confident property decisions based on facts rather than speculation. Whether you're buying your first home, investing, or simply exploring the market, our insights help you better understand trends, pricing, and opportunities across the UK.",
				'cta_primary_text'   => 'Book a Free Consultation',
				'cta_primary_url'    => '/contact/',
				'cta_secondary_text' => 'See Our Services',
				'cta_secondary_url'  => '/services/',
				'is_visible'         => 1,
			] );
			$count += (int) (bool) $wpdb->rows_affected;
		}

		// ── Home: Highlights ──────────────────────────────────────────────────
		$hlt = "{$pfx}section_highlights";
		if ( $home_id && self::table_exists( $hlt ) && ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$hlt}` WHERE page_id = %d", $home_id ) ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			foreach ( [
				[ 1, '£28M+ saved for clients' ],
				[ 2, '94% off-market success rate' ],
				[ 3, '500+ homes secured' ],
				[ 4, '4.9★ average client rating' ],
			] as [ $i, $text ] ) {
				$wpdb->insert( $hlt, [ 'page_id' => $home_id, 'text' => $text, 'sort_order' => $i, 'status' => 'active' ] );
				$count += (int) (bool) $wpdb->rows_affected;
			}
		}

		// ── Home: Why Us + Cards ──────────────────────────────────────────────
		$wut  = "{$pfx}section_why_us";
		$wuct = "{$pfx}section_why_us_cards";
		if ( $home_id && self::table_exists( $wut ) && ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$wut}` WHERE page_id = %d", $home_id ) ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->insert( $wut, [
				'page_id'        => $home_id,
				'heading'        => "Why You Need a Buyer's Agent",
				'description'    => "Estate agents work for the seller. We work exclusively for you - from search to negotiation to completion.",
				'more_link_text' => 'Learn More →',
				'more_link_url'  => '/services/',
				'is_visible'     => 1,
			] );
			$wu_id = (int) $wpdb->insert_id;
			if ( $wu_id && self::table_exists( $wuct ) ) {
				foreach ( [
					[ 1, 'Exclusive Market Access',   'We source up to 30% of properties before they reach Rightmove - giving you first pick before the competition.' ],
					[ 2, 'Expert Negotiation',         'Our agents save clients an average of £42,000 below asking price through skilled, data-backed negotiation.' ],
					[ 3, 'End-to-End Coordination',    'We manage solicitors, surveyors, and mortgage brokers so you focus on decisions - not admin.' ],
					[ 4, 'Works Only For You',         'Unlike estate agents, we have zero conflict of interest. Our only job is to get you the best outcome.' ],
				] as [ $i, $title, $desc ] ) {
					$wpdb->insert( $wuct, [ 'why_us_id' => $wu_id, 'title' => $title, 'description' => $desc, 'sort_order' => $i, 'status' => 'active' ] );
					$count += (int) (bool) $wpdb->rows_affected;
				}
			}
			$count++;
		}

		// ── Home: Guide Through + Points ──────────────────────────────────────
		$gtt  = "{$pfx}section_guide_through";
		$gtpt = "{$pfx}section_guide_through_points";
		if ( $home_id && self::table_exists( $gtt ) && ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$gtt}` WHERE page_id = %d", $home_id ) ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->insert( $gtt, [
				'page_id'        => $home_id,
				'heading'        => 'We Guide You Through Every Step',
				'description'    => "Buying a home is one of the biggest decisions you'll make. We make it straightforward, safe, and stress-free - from your first brief to getting the keys.",
				'more_link_text' => 'Our Process →',
				'more_link_url'  => '/services/',
				'is_visible'     => 1,
			] );
			$gt_id = (int) $wpdb->insert_id;
			if ( $gt_id && self::table_exists( $gtpt ) ) {
				foreach ( [
					[ 1, 'Define your brief - budget, location, property type, must-haves and deal-breakers' ],
					[ 2, 'Access off-market and on-market properties before competing buyers see them' ],
					[ 3, 'View, shortlist, and run full independent due-diligence checks' ],
					[ 4, 'Negotiate the best price and contract terms entirely on your behalf' ],
					[ 5, 'Coordinate conveyancing, surveys, and mortgage through to completion' ],
				] as [ $i, $pt ] ) {
					$wpdb->insert( $gtpt, [ 'guide_id' => $gt_id, 'point_text' => $pt, 'sort_order' => $i, 'status' => 'active' ] );
					$count += (int) (bool) $wpdb->rows_affected;
				}
			}
			$count++;
		}

		// ── Home: Difference + Comparison Table ───────────────────────────────
		$dift  = "{$pfx}section_difference";
		$diftt = "{$pfx}section_difference_table";
		if ( $home_id && self::table_exists( $dift ) && ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$dift}` WHERE page_id = %d", $home_id ) ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->insert( $dift, [
				'page_id'        => $home_id,
				'heading'        => "How We're Different From Estate Agents",
				'information'    => "Estate agents are legally obligated to act in the seller's best interest. We're legally obligated to act in yours - the difference is everything.",
				'more_link_text' => 'About Us →',
				'more_link_url'  => '/about/',
				'is_visible'     => 1,
			] );
			$dif_id = (int) $wpdb->insert_id;
			if ( $dif_id && self::table_exists( $diftt ) ) {
				foreach ( [
					[ 1, 'Works for',             'The buyer - exclusively',         'The seller' ],
					[ 2, 'Off-market access',      'Yes - up to 30% of stock',       'Listed properties only' ],
					[ 3, 'Negotiation goal',       'Lowest price for you',            'Highest price for seller' ],
					[ 4, 'Conflict of interest',   'None',                            'Always (paid by seller)' ],
					[ 5, 'Searches & due-dil.',    'Full independent check',          'Not provided' ],
					[ 6, 'Ongoing coordination',   'Solicitors, surveys, mortgage',   'Not included' ],
				] as [ $i, $feature, $us, $others ] ) {
					$wpdb->insert( $diftt, [ 'difference_id' => $dif_id, 'feature_label' => $feature, 'us_value' => $us, 'others_value' => $others, 'sort_order' => $i, 'status' => 'active' ] );
					$count += (int) (bool) $wpdb->rows_affected;
				}
			}
			$count++;
		}

		// ── Home: Experience + Cards ──────────────────────────────────────────
		$expt  = "{$pfx}section_experience";
		$expct = "{$pfx}section_experience_cards";
		if ( $home_id && self::table_exists( $expt ) && ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$expt}` WHERE page_id = %d", $home_id ) ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->insert( $expt, [
				'page_id'        => $home_id,
				'heading'        => 'Our Experience Across Every Market',
				'description'    => "From first-time buyers to seasoned investors, we've navigated every scenario the UK property market throws at buyers.",
				'more_link_text' => 'Client Stories →',
				'more_link_url'  => '/client-stories/',
				'is_visible'     => 1,
			] );
			$exp_id = (int) $wpdb->insert_id;
			if ( $exp_id && self::table_exists( $expct ) ) {
				foreach ( [
					[ 1, 'First-Time Buyers',    'From AIP to completion - we guide first-timers through every step and avoid the costly traps.' ],
					[ 2, 'Upsizers & Families',  'Timing your sale and purchase simultaneously is complex. We coordinate both sides seamlessly.' ],
					[ 3, 'Buy-to-Let Investors', 'Yield analysis, tenant demand data, and off-market deal sourcing for investors who want returns.' ],
					[ 4, 'Relocation Buyers',    'Moving city or country? We act as your local expert on the ground - no need for multiple trips.' ],
					[ 5, 'Probate & Inheritance','Sensitive, time-pressured purchases handled with care, discretion, and full legal awareness.' ],
				] as [ $i, $title, $desc ] ) {
					$wpdb->insert( $expct, [ 'section_id' => $exp_id, 'title' => $title, 'description' => $desc, 'sort_order' => $i, 'status' => 'active' ] );
					$count += (int) (bool) $wpdb->rows_affected;
				}
			}
			$count++;
		}

		// ── Home: Why Required + Cards ────────────────────────────────────────
		$wrt  = "{$pfx}section_why_required";
		$wrct = "{$pfx}section_why_required_cards";
		if ( $home_id && self::table_exists( $wrt ) && ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$wrt}` WHERE page_id = %d", $home_id ) ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->insert( $wrt, [
				'page_id'        => $home_id,
				'heading'        => "Why a Buyer's Agent Is Worth Every Penny",
				'information'    => "The average UK buyer leaves significant money on the table. Our fee pays for itself - typically many times over.",
				'more_link_text' => 'Our Fees →',
				'more_link_url'  => '/services/',
				'is_visible'     => 1,
			] );
			$wr_id = (int) $wpdb->insert_id;
			if ( $wr_id && self::table_exists( $wrct ) ) {
				foreach ( [
					[ 1, 'The Average Buyer Pays 3% Too Much',       "Without independent data, most buyers accept early offers or fail to challenge inflated asking prices. We don't." ],
					[ 2, 'Off-Market Eliminates Bidding Wars',        'Bidding wars cost buyers an average of 8% above guide price. Off-market deals remove the competition entirely.' ],
					[ 3, 'Survey Issues Cost £12k on Average',        'Missing structural or legal problems before exchange can be catastrophic. We identify them early - and use them to renegotiate.' ],
				] as [ $i, $title, $desc ] ) {
					$wpdb->insert( $wrct, [ 'section_id' => $wr_id, 'title' => $title, 'description' => $desc, 'sort_order' => $i, 'status' => 'active' ] );
					$count += (int) (bool) $wpdb->rows_affected;
				}
			}
			$count++;
		}

		// ── About: Header ─────────────────────────────────────────────────────
		$apht = "{$pfx}about_page_header";
		if ( $about_id && self::table_exists( $apht ) && ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$apht}` WHERE page_id = %d", $about_id ) ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->insert( $apht, [
				'page_id'     => $about_id,
				'heading'     => 'About Advaith Homes',
				'information' => "We're the UK's dedicated buyer's agent - a team of property experts who work exclusively for buyers, never sellers. Our mission is to level the playing field so that every buyer has expert representation on their side.",
				'is_visible'  => 1,
			] );
			$count += (int) (bool) $wpdb->rows_affected;
		}

		// ── About: Story + Points ─────────────────────────────────────────────
		$astt  = "{$pfx}about_story";
		$astpt = "{$pfx}about_story_points";
		if ( $about_id && self::table_exists( $astt ) && ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$astt}` WHERE page_id = %d", $about_id ) ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->insert( $astt, [
				'page_id'    => $about_id,
				'heading'    => 'Our Story',
				'subheading' => 'Founded on the belief that buyers deserve better',
				'is_visible' => 1,
			] );
			$ast_id = (int) $wpdb->insert_id;
			if ( $ast_id && self::table_exists( $astpt ) ) {
				foreach ( [
					[ 1, 'Founded in 2019 after witnessing buyers consistently lose out through poor representation' ],
					[ 2, 'Over £28 million saved for clients across 500+ completed transactions' ],
					[ 3, 'Access to off-market properties through an exclusive network of agent relationships' ],
					[ 4, 'Regulated, transparent, and 100% conflict-free advice - always on your side' ],
					[ 5, 'Average client saves £42,000 against the initial asking price' ],
				] as [ $i, $pt ] ) {
					$wpdb->insert( $astpt, [ 'story_id' => $ast_id, 'point_text' => $pt, 'sort_order' => $i ] );
					$count += (int) (bool) $wpdb->rows_affected;
				}
			}
			$count++;
		}

		// ── About: Values ─────────────────────────────────────────────────────
		$avt = "{$pfx}about_values";
		if ( $about_id && self::table_exists( $avt ) && ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$avt}` WHERE page_id = %d", $about_id ) ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			foreach ( [
				[ 1, 'Buyer-First Always',        "We work exclusively for buyers - no vendor relationships, no conflicts, ever." ],
				[ 2, 'Radical Transparency',       "Fixed fees, clear scope, no hidden charges. You always know exactly what you're getting." ],
				[ 3, 'Data-Driven Decisions',      'Every recommendation is backed by real market data, comparable sales, and independent analysis.' ],
				[ 4, 'Long-Term Relationships',    "Most of our clients return for their next purchase. That's how we measure success." ],
			] as [ $i, $heading, $info ] ) {
				$wpdb->insert( $avt, [ 'page_id' => $about_id, 'heading' => $heading, 'information' => $info, 'sort_order' => $i, 'status' => 'active' ] );
				$count += (int) (bool) $wpdb->rows_affected;
			}
		}

		// ── Services: Page Header ─────────────────────────────────────────────
		$spht = "{$pfx}services_page_header";
		if ( $svc_id && self::table_exists( $spht ) && ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$spht}` WHERE page_id = %d", $svc_id ) ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->insert( $spht, [
				'page_id'     => $svc_id,
				'heading'     => 'Our Services',
				'information' => 'From your first search to handing over the keys - complete buyer-side representation at every stage of the property purchase.',
				'is_visible'  => 1,
			] );
			$count += (int) (bool) $wpdb->rows_affected;
		}

		// ── Services + Bullet Points ──────────────────────────────────────────
		$svct  = "{$pfx}services";
		$svbpt = "{$pfx}service_bullet_points";
		if ( self::table_exists( $svct ) && ! $wpdb->get_var( "SELECT id FROM `{$svct}` LIMIT 1" ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$services = [
				[
					'slug'  => 'property-search', 'sort_order' => 1,
					'title' => 'Property Search & Shortlisting',
					'short' => 'Comprehensive on and off-market property search tailored to your exact brief.',
					'full'  => '<p>We begin with a detailed briefing to understand exactly what you need - location, size, budget, lifestyle, must-haves, and deal-breakers. Then we search the entire market on your behalf: Rightmove, Zoopla, our exclusive off-market network, and direct agent relationships.</p><p>Every property we recommend has been pre-assessed against your brief. You only see the homes worth your time.</p>',
					'pts'   => [
						'Detailed needs analysis and buyer brief',
						'Full market search - on and off-market',
						'Pre-assessment and shortlisting against brief',
						'Accompanied viewings with expert commentary',
						'Area research and comparable sales analysis',
					],
				],
				[
					'slug'  => 'off-market-access', 'sort_order' => 2,
					'title' => 'Off-Market Property Access',
					'short' => 'Up to 30% of our deals are off-market - no competition, no bidding wars.',
					'full'  => '<p>Our relationships with estate agents, developers, and property solicitors across the UK give us access to properties before they are listed publicly. This off-market network means you can secure the best homes without competing against a field of buyers.</p><p>Off-market purchases typically complete 3 weeks faster and at a lower price than openly marketed equivalents.</p>',
					'pts'   => [
						'Exclusive agent network across the UK',
						'Developer and new-build off-plan relationships',
						'Probate and private sale introductions',
						'Pre-market viewings before public launch',
						'No bidding wars - fewer competing buyers',
					],
				],
				[
					'slug'  => 'negotiation-strategy', 'sort_order' => 3,
					'title' => 'Negotiation & Offer Strategy',
					'short' => 'Data-backed negotiation that saves our clients an average of £42,000.',
					'full'  => '<p>Negotiating a property price without independent data is like negotiating a salary without knowing the market rate. We arm you with comparable sold prices, time-on-market data, chain status, and vendor motivation - then negotiate on your behalf.</p><p>Our average client saves 5–8% against the initial asking price. On a £500,000 property, that is £25,000–£40,000.</p>',
					'pts'   => [
						'Comparable sold price analysis',
						'Vendor motivation and chain research',
						'Strategic offer timing and structuring',
						'Condition and contract term negotiation',
						'Gazumping and re-negotiation management',
					],
				],
				[
					'slug'  => 'due-diligence', 'sort_order' => 4,
					'title' => 'Due Diligence & Survey Coordination',
					'short' => 'Independent checks to ensure you know everything before you commit.',
					'full'  => '<p>Missing a structural defect, planning issue, or legal problem before exchange can cost tens of thousands of pounds - or make a property unmortgageable. We coordinate all pre-purchase checks independently from the estate agent.</p><p>We interpret survey findings, identify material risks, and use issues we find to re-negotiate the price where appropriate.</p>',
					'pts'   => [
						'Independent RICS survey commissioning',
						'Structural and planning history checks',
						'Environmental and flood risk research',
						'Leasehold and title deed review',
						'Survey result interpretation and renegotiation',
					],
				],
				[
					'slug'  => 'conveyancing-management', 'sort_order' => 5,
					'title' => 'Conveyancing & Legal Coordination',
					'short' => 'We manage your solicitor, chase the chain, and keep the deal moving.',
					'full'  => '<p>Most purchase delays and fall-throughs are caused by poor communication between solicitors, agents, and lenders. As your representative we bridge that gap - chasing all parties, flagging issues early, and keeping the transaction on track.</p><p>Our clients complete 3–4 weeks faster on average than buyers without representation.</p>',
					'pts'   => [
						'Solicitor introduction and coordination',
						'Chain status monitoring and chasing',
						'Lender and mortgage progress tracking',
						'Search result and enquiry management',
						'Exchange and completion day coordination',
					],
				],
				[
					'slug'  => 'relocation-investment', 'sort_order' => 6,
					'title' => 'Relocation & Investment Services',
					'short' => 'Specialist support for those relocating or building a property portfolio.',
					'full'  => '<p>Relocating from another city or country means you cannot easily visit properties at short notice. We act as your eyes and ears on the ground - attending viewings, meeting agents, and giving you honest, expert feedback on every property we see.</p><p>For investors, we add rental yield analysis, tenant demand data, and long-term capital growth projections to every recommendation.</p>',
					'pts'   => [
						'Remote search and virtual viewing support',
						'Area research and school catchment analysis',
						'Buy-to-let yield and return analysis',
						'Portfolio strategy and growth planning',
						'Renovation and uplift potential assessment',
					],
				],
			];
			foreach ( $services as $svc ) {
				$wpdb->insert( $svct, [
					'title'      => $svc['title'],
					'slug'       => $svc['slug'],
					'short_desc' => $svc['short'],
					'full_desc'  => $svc['full'],
					'sort_order' => $svc['sort_order'],
					'status'     => 'active',
				] );
				$svc_row_id = (int) $wpdb->insert_id;
				if ( $svc_row_id && self::table_exists( $svbpt ) ) {
					foreach ( $svc['pts'] as $bi => $bullet ) {
						$wpdb->insert( $svbpt, [ 'service_id' => $svc_row_id, 'point_text' => $bullet, 'sort_order' => $bi + 1 ] );
					}
				}
				$count++;
			}
		}

		// ── Team Members ──────────────────────────────────────────────────────
		$tmt = "{$pfx}team_members";
		if ( self::table_exists( $tmt ) && ! $wpdb->get_var( "SELECT id FROM `{$tmt}` LIMIT 1" ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			foreach ( [
				[ 1, 'Advaith Rajkumar', 'Founder & Lead Buyer\'s Agent',              1, 'contact@advaithhomes.co.uk', 'https://linkedin.com/in/advaithhomes', "Advaith founded Advaith Homes in 2019 after eight years as a senior estate agent, watching buyers consistently lose out through poor representation. He personally oversees every acquisition above £750,000 and has negotiated over £28 million in savings for clients." ],
				[ 2, 'Sarah Mitchell',   'Senior Buyer\'s Agent - London & South East', 1, 'sarah@advaithhomes.co.uk',   '',                                     "Sarah brings 12 years of property experience across prime London and the commuter belt. She specialises in family relocations and upsizers, with an unrivalled off-market network across Richmond, Wimbledon, and Surrey." ],
				[ 3, 'Priya Sharma',     'Finance & Mortgage Specialist',               0, 'priya@advaithhomes.co.uk',   '',                                     "Priya is a qualified mortgage adviser who joined Advaith Homes to provide clients with independent financing guidance integrated into the buying process. She has access to over 90 lenders and specialises in complex income structures." ],
				[ 4, 'James Cooper',     'Legal Liaison & Due Diligence Lead',          0, 'james@advaithhomes.co.uk',   '',                                     "-James spent 10 years as a property solicitor before moving into buyer representation. He leads all due diligence, survey interpretation, and conveyancing coordination - catching issues early and using them to renegotiate." ],
			] as [ $i, $name, $designation, $is_featured, $email, $linkedin, $bio ] ) {
				$wpdb->insert( $tmt, [
					'name'         => $name,
					'designation'  => $designation,
					'bio'          => $bio,
					'email'        => $email,
					'linkedin_url' => $linkedin,
					'sort_order'   => $i,
					'is_featured'  => $is_featured,
					'status'       => 'active',
				] );
				$count += (int) (bool) $wpdb->rows_affected;
			}
		}

		// ── Reviews ───────────────────────────────────────────────────────────
		$rvt = "{$pfx}reviews";
		if ( self::table_exists( $rvt ) && ! $wpdb->get_var( "SELECT id FROM `{$rvt}` LIMIT 1" ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$has_short_desc = ! empty( $wpdb->get_results( $wpdb->prepare(
				'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s',
				DB_NAME, $rvt, 'short_desc'
			) ) );
			foreach ( [
				[ 'James & Rachel T.',  'First-Time Buyers, Battersea',       5, 1, 'Off-market flat secured 8% below comparable properties',   "We were first-time buyers in London - overwhelmed, outbid, and frankly terrified. Advaith Homes found us a flat off-market, below our budget, in six weeks. They held our hands the entire way. Genuinely life-changing." ],
				[ 'Michael R.',         'Investor, Manchester & Leeds',        5, 1, '6 investment properties acquired across two cities',        "I've bought six properties through Advaith Homes over three years. Their yield analysis is better than any agent I've used, and the off-market access means I'm not competing against 40 other bidders." ],
				[ 'Priya & Vikram S.',  'Relocating from Singapore',          5, 1, 'Completed purchase without the buyer visiting until moving day', "Moving from Singapore, we needed someone to be our eyes on the ground. Sarah viewed 14 properties over two weeks and gave us honest, detailed feedback on each. We bought a brilliant family home in Richmond without a single wasted trip." ],
				[ 'Catherine B.',       'Downsizer, Kensington to Devon',     5, 0, 'Negotiated £65,000 below asking on a probate sale',         "The chain management alone was worth the fee. James kept everything moving when the estate agent went quiet for weeks. We completed on time, below asking, and without the stress I feared." ],
				[ 'Tom & Alicia H.',    'Upsizers, South West London',        5, 0, 'Won competitive offer on third attempt after strategy change', "We'd been outbid three times before we found Advaith Homes. They changed our offer strategy completely - we won the next property we went for, at a price we were comfortable with." ],
				[ 'David & Emma L.',    'Second Home, Cotswolds',             5, 0, 'Off-market farmhouse secured in 7 weeks',                   "Finding a second home in a competitive market without living nearby is a nightmare. Advaith Homes found us exactly what we were looking for within two months, including a property we'd never have found on Rightmove." ],
			] as [ $reviewer_name, $reviewer_title, $rating, $is_featured, $short_desc, $review_text ] ) {
				$data = [
					'reviewer_name'  => $reviewer_name,
					'reviewer_title' => $reviewer_title,
					'review_text'    => $review_text,
					'rating'         => $rating,
					'is_featured'    => $is_featured,
					'status'         => 'active',
					'source'         => 'manual',
				];
				if ( $has_short_desc ) {
					$data['short_desc'] = $short_desc;
				}
				$wpdb->insert( $rvt, $data );
				$count += (int) (bool) $wpdb->rows_affected;
			}
		}

		// ── FAQs ──────────────────────────────────────────────────────────────
		$faqt = "{$pfx}faqs";
		if ( self::table_exists( $faqt ) && ! $wpdb->get_var( "SELECT id FROM `{$faqt}` LIMIT 1" ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			foreach ( [
				[ 1, "What is a buyer's agent?",                            "A buyer's agent (also called a buying agent) is a property professional who acts exclusively for the buyer. Unlike estate agents - legally obligated to act for the seller - a buyer's agent's only duty is to you. We search for properties, negotiate prices, coordinate due diligence, and manage the transaction on your behalf." ],
				[ 2, 'How much does Advaith Homes charge?',                  "We offer fixed, transparent fees agreed in advance. Fees depend on the service level required and the price range of the property. Contact us for a no-obligation quote. In most cases, the savings we achieve far exceed our fee." ],
				[ 3, 'Do I have to use your recommended solicitor or broker?', "Not at all. We have relationships with excellent independent solicitors and mortgage advisers, and we're happy to recommend them - but you are entirely free to use anyone you choose." ],
				[ 4, 'Can you help with off-market properties?',             "Yes - this is one of our core strengths. We maintain relationships with estate agents, developers, and solicitors across the UK, giving us access to properties before they're publicly listed. Typically 25–30% of our clients' purchases are off-market." ],
				[ 5, 'How long does the buying process take?',               "From initial briefing to completion, most clients buy within 8–16 weeks. Off-market purchases can be faster. We'll give you a realistic timeline based on your specific brief and target market." ],
				[ 6, 'Do you operate across the whole UK?',                  "We operate primarily in London, the South East, and major UK cities. For other areas, we work with a trusted network of buying agents and will always be honest if another agent is better placed to help you." ],
				[ 7, "Is a buyer's agent worth it for a property under £500,000?", "Absolutely. Our negotiation alone typically saves 5–8% of the purchase price. On a £400,000 property that's £20,000–£32,000. We also offer a negotiation-only service for buyers who have already found their property." ],
				[ 8, 'What happens after offer acceptance?',                 "The hard work begins. We coordinate the survey, manage any renegotiation, liaise with solicitors on both sides, track mortgage progress, monitor the chain, and keep everything moving through to exchange and completion." ],
			] as [ $i, $q, $a ] ) {
				$wpdb->insert( $faqt, [ 'question' => $q, 'answer' => $a, 'sort_order' => $i, 'status' => 'active' ] );
				$count += (int) (bool) $wpdb->rows_affected;
			}
		}

		// ── News Bar Items ────────────────────────────────────────────────────
		$nbit = "{$pfx}news_bar_items";
		if ( self::table_exists( $nbit ) && ! $wpdb->get_var( "SELECT id FROM `{$nbit}` LIMIT 1" ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			foreach ( [
				[ 1, 'New: Off-market properties now available in Richmond, Wimbledon & Surrey - register today',       '/contact/' ],
				[ 2, 'UK property market update: Average asking prices up 2.3% - expert analysis available',            '/blog/' ],
				[ 3, 'Advaith Homes rated 4.9/5 by 200+ verified clients - read the stories',                          '/client-stories/' ],
				[ 4, 'Free 30-minute consultation with a buyer\'s agent - no obligation, book now',                     '/contact/' ],
				[ 5, 'New guide: How to Buy a Home in the UK - 25-page step-by-step walkthrough, free download',        '/guides/' ],
			] as [ $i, $text, $url ] ) {
				$wpdb->insert( $nbit, [ 'text' => $text, 'link_url' => $url, 'sort_order' => $i, 'status' => 'active' ] );
				$count += (int) (bool) $wpdb->rows_affected;
			}
		}

		// ── Contact Page Config ───────────────────────────────────────────────
		$cpct = "{$pfx}contact_page_config";
		if ( $contact_id && self::table_exists( $cpct ) && ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$cpct}` WHERE page_id = %d", $contact_id ) ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->insert( $cpct, [
				'page_id'         => $contact_id,
				'heading'         => 'Get in Touch',
				'basic_info'      => "Ready to buy smarter? Whether you have a specific property in mind or are just starting your search, we'd love to hear from you. A 30-minute call is free, confidential, and no-obligation.",
				'email'           => 'contact@advaithhomes.co.uk',
				'whatsapp_number' => '+44 7747 223762',
				'phone_number'    => '+44 7747 223762',
				'is_visible'      => 1,
			] );
			$count += (int) (bool) $wpdb->rows_affected;
		}

		// ── Client Stories Header ─────────────────────────────────────────────
		$csht = "{$pfx}client_stories_header";
		if ( $cs_id && self::table_exists( $csht ) && ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$csht}` WHERE page_id = %d", $cs_id ) ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->insert( $csht, [
				'page_id'     => $cs_id,
				'heading'     => 'Client Stories',
				'information' => "Real results from real buyers. Every story here is from a client who trusted us to guide one of the biggest purchases of their life - and came out ahead.",
				'is_visible'  => 1,
			] );
			$count += (int) (bool) $wpdb->rows_affected;
		}

		// ── Footer Config + Contact Links + Social Links ──────────────────────
		$fct = "{$pfx}footer_config";
		if ( self::table_exists( $fct ) && ! $wpdb->get_var( "SELECT id FROM `{$fct}` LIMIT 1" ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->insert( $fct, [
				'site_name'            => 'Advaith Homes',
				'tagline'              => "The UK's buyer's agent - working exclusively for you.",
				'copyright_text'       => '© ' . gmdate( 'Y' ) . ' Advaith Homes. All rights reserved.',
				'get_in_touch_heading' => 'Get in Touch',
				'is_visible'           => 1,
			] );
			$count += (int) (bool) $wpdb->rows_affected;

			$fclt = "{$pfx}footer_contact_links";
			if ( self::table_exists( $fclt ) ) {
				foreach ( [
					[ 1, 'Phone',   '+44 7747 223762',               'tel:+447747223762',                    'phone'    ],
					[ 2, 'Email',   'contact@advaithhomes.co.uk',     'mailto:contact@advaithhomes.co.uk',    'email'    ],
					[ 3, 'Address', 'London & Nationwide, UK',        '',                                     'location' ],
				] as [ $i, $label, $value, $url, $icon ] ) {
					$wpdb->insert( $fclt, [ 'label' => $label, 'value' => $value, 'link_url' => $url, 'icon_class' => $icon, 'sort_order' => $i, 'status' => 'active' ] );
					$count += (int) (bool) $wpdb->rows_affected;
				}
			}

			$fslt = "{$pfx}footer_social_links";
			if ( self::table_exists( $fslt ) ) {
				foreach ( [
					[ 1, 'Instagram',   'https://instagram.com/advaithhomes',               'instagram' ],
					[ 2, 'Facebook',    'https://facebook.com/advaithhomes',                'facebook'  ],
					[ 3, 'LinkedIn',    'https://linkedin.com/company/advaithhomes',        'linkedin'  ],
					[ 4, 'Twitter / X', 'https://twitter.com/advaithhomes',                'twitter'   ],
				] as [ $i, $platform, $url, $icon ] ) {
					$wpdb->insert( $fslt, [ 'platform' => $platform, 'url' => $url, 'icon_class' => $icon, 'sort_order' => $i, 'status' => 'active' ] );
					$count += (int) (bool) $wpdb->rows_affected;
				}
			}
		}

		// ── Nav Menu Items ────────────────────────────────────────────────────
		$nmt  = "{$pfx}nav_menus";
		$nmit = "{$pfx}nav_menu_items";
		if ( self::table_exists( $nmt ) && self::table_exists( $nmit ) ) {
			$primary_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$nmt}` WHERE slug = %s", 'primary' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$footer_id  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$nmt}` WHERE slug = %s", 'footer' ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			if ( $primary_id && ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$nmit}` WHERE menu_id = %d", $primary_id ) ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				foreach ( [
					[ 1, 'Home',           '/'               ],
					[ 2, 'About',          '/about/'         ],
					[ 3, 'Services',       '/services/'      ],
					[ 4, 'Guides',         '/guides/'        ],
					[ 5, 'Client Stories', '/client-stories/'],
					[ 6, 'Blog',           '/blog/'          ],
					[ 7, 'Contact',        '/contact/'       ],
				] as [ $i, $label, $url ] ) {
					$wpdb->insert( $nmit, [ 'menu_id' => $primary_id, 'label' => $label, 'url' => $url, 'sort_order' => $i, 'status' => 'active' ] );
					$count += (int) (bool) $wpdb->rows_affected;
				}
			}
			if ( $footer_id && ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM `{$nmit}` WHERE menu_id = %d", $footer_id ) ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				foreach ( [
					[ 1, 'Privacy Policy',        '/privacy-policy/'              ],
					[ 2, 'Cookie Policy',         '/cookie-policy/'               ],
					[ 3, 'Stamp Duty Calculator', '/guides/stamp-duty-calculator/' ],
					[ 4, 'Mortgage Calculator',   '/guides/mortgage-calculator/'  ],
					[ 5, 'Contact',               '/contact/'                     ],
				] as [ $i, $label, $url ] ) {
					$wpdb->insert( $nmit, [ 'menu_id' => $footer_id, 'label' => $label, 'url' => $url, 'sort_order' => $i, 'status' => 'active' ] );
					$count += (int) (bool) $wpdb->rows_affected;
				}
			}
		}

		// ── Floating Widget (WhatsApp) ────────────────────────────────────────
		$fwt = "{$pfx}floating_widgets";
		if ( self::table_exists( $fwt ) && ! $wpdb->get_var( "SELECT id FROM `{$fwt}` LIMIT 1" ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$wpdb->insert( $fwt, [
				'widget_type' => 'whatsapp',
				'label'       => 'Chat on WhatsApp',
				'link_url'    => 'https://wa.me/447747223762',
				'position'    => 'bottom_right',
				'is_visible'  => 1,
			] );
			$count += (int) (bool) $wpdb->rows_affected;
		}

		// ── Plugin Posts (blog + news) ────────────────────────────────────────
		$ppt = "{$pfx}posts";
		if ( self::table_exists( $ppt ) && ! $wpdb->get_var( "SELECT id FROM `{$ppt}` LIMIT 1" ) ) { // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			foreach ( [
				[
					'blog', 1, 'how-long-does-buying-a-home-take',
					'How Long Does Buying a Home in the UK Really Take?',
					'The complete week-by-week guide to UK property buying timelines - what happens, who does it, and how to avoid delays.',
					'<p>If you\'ve been told buying a home in the UK takes "about three months", that\'s broadly right - but it tells you almost nothing useful. This guide breaks every stage down into what\'s happening, who\'s doing it, and what you can do to keep things moving.</p><h2>Before you start: the work that pays itself back</h2><p>The fastest completions share one thing in common - the buyer had their finances ready before they made an offer. That means an Agreement in Principle, a deposit sitting ready, and a solicitor on standby.</p>',
				],
				[
					'blog', 1, 'off-market-property-guide',
					'Off-Market Property: What It Is and How to Find It',
					"How to access properties that never appear on Rightmove - and why buyers who win off-market deals use a buyer's agent.",
					"<p>Around 25–30% of UK property transactions happen before the home ever reaches Rightmove. These off-market deals go to buyers with the right connections - or the right agent working on their behalf.</p><h2>Why sellers go off-market</h2><p>Privacy, avoiding the disruption of viewings, or simply trusting an agent to bring a qualified buyer directly. Probate sales, corporate relocations, and downsizing retirees are the most common sources.</p>",
				],
				[
					'blog', 0, 'stamp-duty-guide-2025',
					'Stamp Duty 2025: The Complete Guide for Buyers',
					'Everything buyers need to know about stamp duty in 2025 - rates, thresholds, first-time buyer relief, and the additional property surcharge.',
					'<p>Stamp Duty Land Tax (SDLT) is one of the largest costs of buying a property in England. The rules changed again in 2024 and the thresholds are different depending on whether you\'re a first-time buyer, moving home, or purchasing an additional property.</p><h2>Current stamp duty rates (2025)</h2><p>For properties purchased as your main home: 0% on the first £250,000; 5% on £250,001–£925,000; 10% on £925,001–£1.5M; 12% above £1.5M.</p>',
				],
				[
					'news', 0, 'uk-property-market-may-2025',
					'UK Property Market: May 2025 Update',
					"Average asking prices rose 2.3% in Q1 2025. Here's what it means for buyers and how to navigate a competitive market.",
					'<p>The latest data from HM Land Registry shows average UK house prices increased by 2.3% in the first quarter of 2025, driven by continued demand pressure in London, Manchester, and Bristol.</p><h2>What this means for buyers</h2><p>Competition for well-priced stock remains intense, particularly for family homes under £600,000. Off-market sourcing is increasingly the strategy that makes the difference.</p>',
				],
			] as [ $type, $is_featured, $slug, $title, $excerpt, $content ] ) {
				$wpdb->insert( $ppt, [
					'post_type'    => $type,
					'title'        => $title,
					'slug'         => $slug,
					'excerpt'      => $excerpt,
					'content'      => $content,
					'is_featured'  => $is_featured,
					'status'       => 'active',
					'published_at' => current_time( 'mysql', true ),
				] );
				$count += (int) (bool) $wpdb->rows_affected;
			}
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
		// ah_taxonomy_types and ah_taxonomies belong to the CMS plugin — not truncated here.

		$options = [
			'ah_site_settings', 'ah_home_settings', 'ah_guide_nav',
			'ah_guide_categories', 'ah_nav_buying_topics', 'ah_nav_finance_topics',
			'ah_nav_legal_topics', 'ah_process_steps', 'ah_site_stats',
			'ah_trust_signals', 'ah_news_bar_items', 'ah_featured_properties',
			'ah_contact_settings', 'ah_html_blocks',
			'ah_static_quick_links', 'ah_nav_static_page_links', 'ah_theme_navigation', 'ah_theme_footer',
			'ah_cms_navigation', 'ah_cms_footer', 'ah_cms_nav_cta',
		];
		foreach ( $options as $opt ) {
			if ( get_option( $opt ) !== false ) {
				delete_option( $opt );
				$deleted++;
			}
		}

		// Remove seeded WP pages (mandatory + extra)
		$seeded_pages = array_merge(
			[ 'home', 'about', 'services', 'client-stories', 'reviews', 'contact', 'contact-us', 'guides', 'blog', 'news', 'faq' ],
			array_keys( AH_Data::extra_pages() )
		);
		foreach ( $seeded_pages as $slug ) {
			$page = get_page_by_path( $slug );
			if ( $page ) {
				wp_delete_post( $page->ID, true );
				$deleted++;
			}
		}
		// Reset front-page options
		update_option( 'show_on_front', 'posts' );
		delete_option( 'page_on_front' );
		delete_option( 'page_for_posts' );

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
		$options = [ 'ah_site_settings', 'ah_home_settings', 'ah_guide_nav', 'ah_guide_categories', 'ah_nav_buying_topics', 'ah_nav_finance_topics', 'ah_nav_legal_topics', 'ah_process_steps', 'ah_site_stats', 'ah_trust_signals', 'ah_theme_navigation', 'ah_theme_footer', 'ah_cms_navigation', 'ah_cms_footer', 'ah_cms_nav_cta' ];
		foreach ( $options as $opt ) {
			$counts[ $opt ] = get_option( $opt ) !== false ? '✓' : '-';
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
