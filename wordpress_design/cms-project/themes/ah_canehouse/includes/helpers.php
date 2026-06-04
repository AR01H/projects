<?php
defined( 'ABSPATH' ) || exit;

// ── Table helper - reads same wp_ah_cms_plug_* tables as the CMS plugin ──────
function ch_theme_table( string $name ): string {
	global $wpdb;
	$mid = defined( 'TABLE_MID_FIX' ) ? TABLE_MID_FIX : '_cms_plug_';
	return $wpdb->prefix . 'ah' . $mid . $name;
}

// ── Section Visibility ────────────────────────────────────────────────────────
function ch_section_visible( string $key ): bool {
	static $map = null;
	if ( $map === null ) {
		$raw = get_option( 'ch_section_visibility', [] );
		if ( is_string( $raw ) ) $raw = json_decode( $raw, true ) ?: [];
		$map = (array) $raw;
	}
	return isset( $map[ $key ] ) ? (bool) $map[ $key ] : true;
}

// ── Site Settings ─────────────────────────────────────────────────────────────
function ch_get_settings(): array {
	$saved = get_option( 'ch_site_settings', [] );
	if ( is_string( $saved ) ) $saved = json_decode( $saved, true ) ?: [];
	$defaults = ch_mock_default_settings();
	return array_merge( $defaults, (array) $saved );
}

// ── Homepage display limits ───────────────────────────────────────────────────
/**
 * How many items a homepage section should show.
 * Returns 0 to mean "no limit - show everything on the homepage".
 * Controlled from Content & Menu → Homepage Display Limits.
 *
 * @param string $key            e.g. 'story_cards', 'faqs'
 * @param int    $default_count  fallback when no admin value is set
 */
function ch_home_limit( string $key, int $default_count ): int {
	$s  = ch_get_settings();
	$hl = $s['home_limits'] ?? [];
	if ( is_string( $hl ) ) $hl = json_decode( $hl, true ) ?: [];

	// Limit is ON by default. Unchecked in admin → show all (return 0).
	$enabled = isset( $hl[ $key . '_limit' ] ) ? (bool) $hl[ $key . '_limit' ] : true;
	if ( ! $enabled ) {
		return 0;
	}
	$count = isset( $hl[ $key . '_count' ] ) ? (int) $hl[ $key . '_count' ] : $default_count;
	return max( 1, $count );
}

/**
 * Reusable "View all / Read more" button shown under a limited homepage section.
 *
 * @param string $url   destination page
 * @param string $label button text
 * @param string $style 'outline' (default) or 'lime'
 */
function ch_more_button( string $url, string $label, string $style = 'outline' ): void {
	$cls = $style === 'lime' ? 'btn-lime' : 'btn-outline';
	printf(
		'<div class="ch-section-more fade-up"><a href="%s" class="%s">%s</a></div>',
		esc_url( $url ),
		esc_attr( $cls ),
		esc_html( $label )
	);
}

// ── Price visibility ──────────────────────────────────────────────────────────
function ch_show_prices(): bool {
	$s = ch_get_settings();
	// Default OFF - only show if explicitly enabled in admin
	return ! empty( $s['show_prices'] ) && $s['show_prices'] === '1';
}

// ── Certifications ────────────────────────────────────────────────────────────
function ch_get_certifications(): array {
	$s     = ch_get_settings();
	$saved = isset( $s['certifications'] ) ? $s['certifications'] : [];
	if ( is_string( $saved ) ) $saved = json_decode( $saved, true ) ?: [];
	if ( ! empty( $saved ) ) return (array) $saved;
	// Real data fallback – edit real_data/csv/certifications.csv to customise per client.
	$rows = CH_Real_Loader::csv( 'certifications' );
	return ! empty( $rows ) ? $rows : [];
}

// ── Schema / SEO Settings ──────────────────────────────────────────────────────
function ch_get_schema_settings(): array {
	$s      = ch_get_settings();
	$schema = isset( $s['schema'] ) ? $s['schema'] : [];
	if ( is_string( $schema ) ) $schema = json_decode( $schema, true ) ?: [];
	$settings = ch_get_settings();
	$defaults = [
		'enabled'          => '1',
		'type'             => 'FoodEstablishment',
		'name'             => get_bloginfo( 'name' ) ?: 'The Cane House',
		'description'      => $settings['tagline'] ?? 'Fresh sugarcane juice pressed live, served cool.',
		'phone'            => $settings['phone'] ?? '',
		'email'            => $settings['email'] ?? '',
		'area_served'      => 'United Kingdom',
		'price_range'      => '',   // intentionally blank by default
		'include_price'    => '0',  // do not include pricing in schema by default
		'include_reviews'  => '1',
		'logo_url'         => get_template_directory_uri() . '/assets/images/logo.png',
		'social_instagram' => $settings['instagram_url'] ?? '',
		'social_facebook'  => $settings['facebook_url']  ?? '',
	];
	return array_merge( $defaults, (array) $schema );
}

// ── Home / Hero settings ──────────────────────────────────────────────────────
function ch_get_home_settings(): array {
	if ( class_exists( 'AH_Model_Home' ) ) {
		$rows = AH_Model_Home::all();
		if ( ! empty( $rows ) ) {
			$out = [];
			foreach ( $rows as $r ) {
				$key = $r->meta_key ?? $r->field_key ?? $r->key ?? null;
				$val = $r->meta_value ?? $r->field_value ?? $r->value ?? null;
				if ( $key !== null ) $out[ $key ] = $val;
			}
			if ( $out ) return $out;
		}
	}
	$opt = get_option( 'ch_home_settings', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) ) return $opt;
	return ch_mock_home_settings_array();
}

// ── Navigation ────────────────────────────────────────────────────────────────
function ch_normalize_theme_url( string $url, string $fallback = '' ): string {
	$url = trim( wp_unslash( $url ) );
	if ( $url === '' ) return $fallback;
	if ( preg_match( '#^(https?:)?//#i', $url ) || strpos( $url, '#' ) === 0 || strpos( $url, 'mailto:' ) === 0 || strpos( $url, 'tel:' ) === 0 ) {
		return $url;
	}
	return '/' . trim( $url, '/' ) . '/';
}

function ch_get_theme_navigation(): array {
	// Primary: CMS plugin shared navigation (single source of truth).
	$opt = get_option( 'ah_cms_navigation', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) && is_array( $opt ) ) {
		return ch_normalize_theme_navigation( $opt );
	}
	// Fallback: legacy theme-specific option (if it was ever saved).
	$opt = get_option( 'ch_theme_navigation', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) && is_array( $opt ) ) {
		return ch_normalize_theme_navigation( $opt );
	}
	return ch_build_default_navigation();
}

function ch_normalize_theme_navigation( array $items ): array {
	$normalized = [];
	foreach ( $items as $index => $item ) {
		$item  = (array) $item;
		$label = sanitize_text_field( $item['label'] ?? '' );
		if ( $label === '' ) continue;
		$type    = ( $item['type'] ?? 'link' ) === 'dropdown' ? 'dropdown' : 'link';
		$submenu = [];
		foreach ( (array) ( $item['submenu'] ?? [] ) as $sub ) {
			$sub       = (array) $sub;
			$sub_label = sanitize_text_field( $sub['label'] ?? '' );
			$sub_url   = ch_normalize_theme_url( (string) ( $sub['url'] ?? '' ) );
			if ( $sub_label === '' || $sub_url === '' ) continue;
			$submenu[] = [
				'label'       => $sub_label,
				'url'         => $sub_url,
				'description' => sanitize_text_field( $sub['description'] ?? '' ),
				'icon'        => sanitize_text_field( $sub['icon'] ?? '' ),
				'highlight'   => ! empty( $sub['highlight'] ),
			];
		}
		$normalized[] = [
			'id'      => sanitize_title( $item['id'] ?? $label ?: 'nav-' . $index ),
			'label'   => $label,
			'type'    => $type,
			'url'     => $type === 'link' ? ch_normalize_theme_url( (string) ( $item['url'] ?? '' ), home_url( '/' ) ) : '',
			'visible' => isset( $item['visible'] ) ? (bool) $item['visible'] : true,
			'submenu' => $submenu,
		];
	}
	return $normalized;
}

function ch_build_default_navigation(): array {
	// Use common_terms.php constants if available, otherwise raw strings
	return function_exists( 'ch_default_nav' ) ? ch_default_nav() : [
	];
}

function ch_get_nav_cta(): array {
	$label    = defined( 'CH_NAV_CONTACT' ) ? CH_NAV_CONTACT : 'Hire Us';
	$defaults = [ 'label' => $label, 'url' => home_url( '/#contact' ) ];
	// Primary: CMS plugin option
	$opt = get_option( 'ah_cms_nav_cta', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) ) return array_merge( $defaults, (array) $opt );
	// Fallback: legacy theme option
	$opt = get_option( 'ch_nav_cta', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return ! empty( $opt ) ? array_merge( $defaults, (array) $opt ) : $defaults;
}

// ── Footer ────────────────────────────────────────────────────────────────────
function ch_get_theme_footer(): array {
	// Primary: CMS plugin option
	$opt = get_option( 'ah_cms_footer', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) && is_array( $opt ) ) return (array) $opt;
	// Fallback: legacy theme option
	$opt = get_option( 'ch_theme_footer', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) && is_array( $opt ) ) return (array) $opt;
	return ch_build_default_footer();
}

function ch_build_default_footer(): array {
	$s = ch_get_settings();
	return function_exists( 'ch_default_footer_data' )
		? ch_default_footer_data( $s )
		: [
			'brand_description' => defined( 'CH_BRAND_DESC' ) ? CH_BRAND_DESC : 'Fresh sugarcane juice pressed live, served cool.',
			'columns'           => [],
			'legal_links'       => [ [ 'label' => 'Privacy Policy', 'url' => home_url( '/privacy-policy/' ) ] ],
			'social'            => [ 'instagram' => '', 'facebook' => '', 'tiktok' => '', 'youtube' => '' ],
			'copyright'         => '© ' . date( 'Y' ) . ' The Cane House. Pressed Fresh. Served Cool.',
		];
}

// ── Menu / Products ───────────────────────────────────────────────────────────
function ch_get_menu_sizes(): array {
	$opt = get_option( 'ch_menu_sizes', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return ! empty( $opt ) ? $opt : ch_mock_menu_sizes();
}

function ch_get_cane_types(): array {
	$opt = get_option( 'ch_cane_types', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return ! empty( $opt ) ? $opt : ch_mock_cane_types();
}

function ch_get_textures(): array {
	$opt = get_option( 'ch_textures', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return ! empty( $opt ) ? $opt : ch_mock_textures();
}

function ch_get_flavours(): array {
	$opt = get_option( 'ch_flavours', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return ! empty( $opt ) ? $opt : ch_mock_flavours();
}

// ── Order Steps ───────────────────────────────────────────────────────────────
function ch_get_order_steps(): array {
	$opt = get_option( 'ch_order_steps', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return ! empty( $opt ) ? $opt : ch_mock_order_steps();
}

// ── Marquee Items ─────────────────────────────────────────────────────────────
function ch_get_marquee_items(): array {
	// Try news_bar table (shared with CMS plugin)
	if ( class_exists( 'AH_Model_News_Bar' ) ) {
		$rows = method_exists( 'AH_Model_News_Bar', 'get_active' )
			? AH_Model_News_Bar::get_active()
			: AH_Model_News_Bar::all( [ 'status' => 'active' ] );
		if ( ! empty( $rows ) ) {
			return array_map( fn( $r ) => $r->message ?? $r->text ?? '', $rows );
		}
	}
	global $wpdb;
	$table = ch_theme_table( 'news_bar' );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
		$rows = $wpdb->get_results( "SELECT message FROM `{$table}` WHERE status='active' ORDER BY sort_order ASC" );
		if ( ! empty( $rows ) ) return array_map( fn( $r ) => $r->message, $rows );
	}
	$opt = get_option( 'ch_marquee_items', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return ! empty( $opt ) ? $opt : ch_mock_marquee_items();
}

// ── Reviews ───────────────────────────────────────────────────────────────────
/**
 * @param int    $limit         Max reviews to return (0 = no limit for taxonomy queries).
 * @param string $taxonomy_slug Optional: filter by Review Type term slug ('customer','partner','event').
 *                              When empty, returns all active reviews.
 */
function ch_get_reviews( int $limit = 6, string $taxonomy_slug = '' ): array {
	if ( class_exists( 'AH_Reviews_Model' ) ) {
		$model = new AH_Reviews_Model();

		if ( $taxonomy_slug !== '' ) {
			// Taxonomy-filtered: never fall back to untagged reviews - that causes
			// the same pool to bleed into every section on the site.
			$rows = $model->get_by_taxonomy_slug( $taxonomy_slug, $limit );
			return empty( $rows ) ? [] : array_map( 'ch_normalize_review', $rows );
		}

		// No taxonomy filter → return all active reviews.
		$rows = $model->get_paginated( 1, '', 'active' );
		if ( ! empty( $rows['items'] ) ) {
			$reviews = $limit > 0 ? array_slice( $rows['items'], 0, $limit ) : $rows['items'];
			return array_map( 'ch_normalize_review', $reviews );
		}
	}

	// Direct DB query fallback (plugin autoloader not yet loaded).
	// Use {prefix}ah_reviews - the table the plugin installer creates.
	global $wpdb;
	$table = $wpdb->prefix . 'ah_reviews';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
		$limit_sql = $limit > 0 ? $wpdb->prepare( ' LIMIT %d', $limit ) : '';
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results(
			"SELECT * FROM `{$table}` WHERE status='active' ORDER BY id DESC" . $limit_sql
		);
		if ( ! empty( $rows ) ) {
			return array_map( 'ch_normalize_review', $rows );
		}
	}

	// Last resort: mock data (only when DB has zero reviews at all).
	return array_slice( ch_mock_reviews(), 0, $limit );
}

// ── Normalize review data from plugin to theme format ───────────────────────────
function ch_normalize_review( $r ): object {
	$r = (array) $r;
	return (object) [
		'id'          => (int) ( $r['id'] ?? 0 ),
		'author_name' => $r['reviewer_name'] ?? $r['author_name'] ?? 'Happy Customer',
		'location'    => $r['reviewer_title'] ?? $r['location'] ?? 'Verified Customer',
		'review_text' => $r['review_text'] ?? '',
		'rating'      => (float) ( $r['rating'] ?? 5.0 ),
		'image_id'    => (int) ( $r['reviewer_image_id'] ?? 0 ),
	];
}

// ── Highlight Names in review text ────────────────────────────────────────────
/**
 * Fetch taxonomy terms of type 'highlight-names' attached to this review.
 * Results are cached per request so the DB is only hit once per review.
 */
function ch_get_review_highlight_names( int $review_id ): array {
	if ( ! $review_id || ! class_exists( 'AH_Reviews_Model' ) ) return [];
	static $cache = [];
	if ( ! isset( $cache[ $review_id ] ) ) {
		$cache[ $review_id ] = ( new AH_Reviews_Model() )->get_highlight_names( $review_id );
	}
	return $cache[ $review_id ];
}

/**
 * Escape plain review text and wrap occurrences of highlight names in <mark> tags.
 * Always call wp_strip_all_tags() on the input before passing here.
 */
function ch_highlight_text( string $plain_text, array $names ): string {
	if ( $plain_text === '' ) return '';
	if ( empty( $names ) ) return esc_html( $plain_text );
	// Already sorted longest-first by the model; re-sort here in case called directly.
	usort( $names, fn( $a, $b ) => strlen( $b ) - strlen( $a ) );
	$out = esc_html( $plain_text );
	foreach ( $names as $name ) {
		if ( $name === '' ) continue;
		$pat = preg_quote( esc_html( $name ), '#' );
		$out = preg_replace( '#' . $pat . '#iu', '<mark class="ch-highlight">$0</mark>', $out );
	}
	return $out;
}

// ── Get reviewer image URL - supports custom images or generates avatar ─────────
function ch_get_review_image( array|object $review, int $index = 0, string $size = 'thumbnail' ): string {
	$review = (array) $review;
	$image_id = (int) ( $review['image_id'] ?? $review['reviewer_image_id'] ?? 0 );
	if ( $image_id ) {
		$url = wp_get_attachment_image_url( $image_id, $size );
		if ( $url ) return $url;
	}
	return 'https://i.pravatar.cc/120?u=' . ( $index + 10 );
}

// ── FAQs ──────────────────────────────────────────────────────────────────────
function ch_get_faqs( string $topic = '', int $limit = 20 ): array {
	// FAQs are owned by the CMS plugin (ah_faqs table) - the theme has no FAQ
	// data of its own. When $topic is given it filters by a "FAQ Tags" taxonomy
	// term slug through the content_taxonomies pivot (object_type = 'faq').
	if ( ! class_exists( 'AH_Faqs_Model' ) ) {
		return [];
	}
	global $wpdb;
	$faqs = $wpdb->prefix . 'ah_faqs';

	if ( $topic !== '' ) {
		$ct  = $wpdb->prefix . 'ah_content_taxonomies';
		$tax = $wpdb->prefix . 'ah_taxonomies';
		return $wpdb->get_results( $wpdb->prepare(
			"SELECT f.* FROM `{$faqs}` f
			 INNER JOIN `{$ct}`  ct ON ct.object_type = 'faq' AND ct.object_id = f.id
			 INNER JOIN `{$tax}` t  ON t.id = ct.taxonomy_id AND t.slug = %s AND t.status = 'active'
			 WHERE f.status = 'active'
			 ORDER BY f.sort_order ASC, f.id ASC
			 LIMIT %d",
			$topic, $limit
		) ) ?: [];
	}

	return $wpdb->get_results( $wpdb->prepare(
		"SELECT * FROM `{$faqs}` WHERE status = 'active' ORDER BY sort_order ASC, id ASC LIMIT %d",
		$limit
	) ) ?: [];
}

// ── Posts (from CMS plugin) ────────────────────────────────────────────────────
/**
 * Get posts from the CMS plugin (AH_Posts_Model). Plugin-first.
 *
 * @param int   $limit  max items to return
 * @param array $filters ['search' => string, 'status' => 'active'|'draft', 'post_type' => string]
 */
function ch_get_posts( int $limit = 12, array $filters = [] ): array {
	if ( class_exists( 'AH_Posts_Model' ) ) {
		$model = new AH_Posts_Model();
		$result = $model->get_paginated( 1, $filters );
		if ( ! empty( $result['data'] ) ) {
			return array_slice( $result['data'], 0, $limit );
		}
	}
	return [];
}

/**
 * Get a single post from the CMS plugin by slug.
 */
function ch_get_post_by_slug( string $slug ): ?object {
	if ( class_exists( 'AH_Posts_Model' ) ) {
		return ( new AH_Posts_Model() )->find_by( 'slug', $slug );
	}
	return null;
}

// ── Pages (from CMS plugin) ────────────────────────────────────────────────────
/**
 * Get pages from the CMS plugin (AH_Pages_Model). Plugin-first.
 *
 * @param int   $limit  max items to return
 * @param array $filters ['search' => string]
 */
function ch_get_pages_list( int $limit = 20, array $filters = [] ): array {
	if ( class_exists( 'AH_Pages_Model' ) ) {
		$model  = new AH_Pages_Model();
		$result = $model->get_paginated( 1, $filters['search'] ?? '' );
		if ( ! empty( $result['data'] ) ) {
			return array_slice( $result['data'], 0, $limit );
		}
	}
	return [];
}

/**
 * Get a single page from the CMS plugin by slug.
 */
function ch_get_page_by_slug( string $slug ): ?object {
	if ( class_exists( 'AH_Pages_Model' ) ) {
		return ( new AH_Pages_Model() )->get_by_slug( $slug );
	}
	return null;
}

// ── Benefits ──────────────────────────────────────────────────────────────────
function ch_get_benefits(): array {
	$opt = get_option( 'ch_benefits', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return ! empty( $opt ) ? $opt : ch_mock_benefits();
}

// ── Events / Hire Packages ────────────────────────────────────────────────────
function ch_get_hire_packages( int $limit = 0 ): array {
	// Pull from plugin DB table if available
	if ( class_exists( 'AH_Events_Model' ) ) {
		$model = new AH_Events_Model();
		$rows  = $model->get_active( $limit );
		if ( ! empty( $rows ) ) {
			return array_map( function ( $r ) {
				return [
					'id'    => (int) $r->id,
					'icon'  => $r->icon  ?? '🎉',
					'title' => $r->title ?? '',
					'desc'  => $r->description ?? '',
					'items' => (array) ( $r->items ?? [] ),
					'color' => $r->color ?? 'green',
				];
			}, $rows );
		}
	}
	// Fallback: legacy WP option
	$opt = get_option( 'ch_hire_packages', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	$packages = ! empty( $opt ) ? $opt : ch_mock_hire_packages();
	if ( $limit > 0 ) $packages = array_slice( $packages, 0, $limit );
	return $packages;
}

function ch_get_hire_features(): array {
	$opt = get_option( 'ch_hire_features', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return ! empty( $opt ) ? $opt : ch_mock_hire_features();
}

// ── Franchise Locations ───────────────────────────────────────────────────────
function ch_get_franchise_locations(): array {
	$opt = get_option( 'ch_franchise_locations', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return ! empty( $opt ) ? $opt : ch_mock_franchise_locations();
}

// ── Showcase / Juice Gallery ──────────────────────────────────────────────────
function ch_get_juice_showcase(): array {
	$opt = get_option( 'ch_juice_showcase', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return ! empty( $opt ) ? $opt : ch_mock_juice_showcase();
}

// ── Gallery Images ────────────────────────────────────────────────────────────
function ch_get_gallery( string $key, array $defaults ): array {
	$opt = get_option( $key, [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return ! empty( $opt ) ? $opt : $defaults;
}

/**
 * Home hero banners - managed in the plugin (CMS ADMIN → Home Banners).
 * Falls back to the plugin's example defaults if the table is empty,
 * and to a hardcoded set if the plugin is not active.
 */
function ch_get_home_banners(): array {
	if ( class_exists( 'AH_Banners_Helper' ) ) {
		$rows = AH_Banners_Helper::get_all( true ); // active only
		if ( ! empty( $rows ) ) {
			return $rows;
		}
		return AH_Banners_Helper::defaults();
	}
	// Plugin off - minimal safe fallback.
	return [];
}

function ch_get_banner_autoplay(): int {
	return class_exists( 'AH_Banners_Helper' ) ? AH_Banners_Helper::get_autoplay() : 5000;
}

function ch_get_events_gallery(): array {
	// DB/admin option first; then real_data/csv/events-gallery.csv.
	$opt = get_option( 'ch_events_gallery', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) ) return (array) $opt;
	$rows = CH_Real_Loader::csv( 'events-gallery' );
	return ! empty( $rows ) ? $rows : [];
}

function ch_get_franchise_gallery(): array {
	// DB/admin option first; then real_data/csv/franchise-gallery.csv.
	$opt = get_option( 'ch_franchise_gallery', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) ) return (array) $opt;
	$rows = CH_Real_Loader::csv( 'franchise-gallery' );
	return ! empty( $rows ) ? $rows : [];
}

function ch_get_about_gallery(): array {
	// DB/admin option first; then real_data/csv/about-gallery.csv.
	$opt = get_option( 'ch_about_gallery', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) ) return (array) $opt;
	$rows = CH_Real_Loader::csv( 'about-gallery' );
	return ! empty( $rows ) ? $rows : [];
}

/**
 * Home showcase carousel - machines, bottles, products (after hero).
 * Each item supports type: 'image' | 'gif' | 'video' (mp4/webm, autoplays muted+loop).
 */
function ch_get_showcase(): array {
	// DB/admin option first; then real_data/csv/showcase-items.csv.
	$opt = get_option( 'ch_showcase_items', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) ) return (array) $opt;
	$rows = CH_Real_Loader::csv( 'showcase-items' );
	return ! empty( $rows ) ? $rows : [];
}

function ch_get_events_media_gallery(): array {
	// DB/admin option first; then real_data/csv/events-media-gallery.csv.
	$opt = get_option( 'ch_events_media_gallery', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) ) return (array) $opt;
	$rows = CH_Real_Loader::csv( 'events-media-gallery' );
	return ! empty( $rows ) ? $rows : [];
}

function ch_get_franchise_media_gallery(): array {
	// DB/admin option first; then real_data/csv/franchise-media-gallery.csv.
	$opt = get_option( 'ch_franchise_media_gallery', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) ) return (array) $opt;
	$rows = CH_Real_Loader::csv( 'franchise-media-gallery' );
	return ! empty( $rows ) ? $rows : [];
}

function ch_get_sugarcane_gallery(): array {
	// DB/admin option first; then real_data/csv/sugarcane-gallery.csv.
	$opt = get_option( 'ch_sugarcane_gallery', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) ) return (array) $opt;
	$rows = CH_Real_Loader::csv( 'sugarcane-gallery' );
	return ! empty( $rows ) ? $rows : [];
}

// ── Why Sugarcane Stats bar ───────────────────────────────────────────────────
function ch_get_sugarcane_stats(): array {
	$opt = get_option( 'ch_sugarcane_stats', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) ) return (array) $opt;
	// Real data fallback – edit real_data/csv/sugarcane-stats.csv to customise per client.
	$rows = CH_Real_Loader::csv( 'sugarcane-stats' );
	return ! empty( $rows ) ? $rows : [];
}

// ── Nutrition Facts (Why Sugarcane page) ──────────────────────────────────────
function ch_get_nutrition_facts(): array {
	$opt = get_option( 'ch_nutrition_facts', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) ) return (array) $opt;
	// Real data fallback – edit real_data/csv/nutrition-facts.csv to customise per client.
	$rows = CH_Real_Loader::csv( 'nutrition-facts' );
	return ! empty( $rows ) ? $rows : [];
}

// ── Events "Why Choose Us" items ──────────────────────────────────────────────
function ch_get_events_why(): array {
	$opt = get_option( 'ch_events_why', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) ) return $opt;
	// Real data fallback – edit real_data/json/events-why.json to customise per client.
	$data = CH_Real_Loader::kv_json( 'events-why' );
	if ( empty( $data ) ) {
		// kv_json returns [] for arrays; load raw JSON object manually.
		$path = get_template_directory() . '/real_data/json/events-why.json';
		if ( file_exists( $path ) ) {
			$decoded = json_decode( file_get_contents( $path ), true );
			if ( is_array( $decoded ) ) $data = $decoded;
		}
	}
	return $data ?: [];
}

// ── About Page: Mission / Vision / Values ─────────────────────────────────────
function ch_get_about_mvv(): array {
	$opt = get_option( 'ch_about_mvv', [] );
	if ( is_string( $opt ) ){
		$opt = json_decode( $opt, true ) ?: [];
	} 
	return $opt;
}

// ── About Page: Quality Commitment items ──────────────────────────────────────
function ch_get_about_quality(): array {
	$opt = get_option( 'ch_about_quality', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) ) return (array) $opt;
	// Real data fallback – edit real_data/csv/about-quality.csv to customise per client.
	$rows = CH_Real_Loader::csv( 'about-quality' );
	return ! empty( $rows ) ? array_column( $rows, 'item' ) : [];
}

// ── About Page: Equipment Gallery (gallery-strip) ────────────────────────────
function ch_get_about_equipment(): array {
	return ch_get_gallery( 'ch_about_equipment', CH_Data::about_equipment() );
}

// ── About Page: Promise Card ──────────────────────────────────────────────────
function ch_get_about_promise(): array {
	$opt = get_option( 'ch_about_promise', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) ) return $opt;
	$kv = CH_Data::about_settings();
	return [
		'icon'  => $kv['promise_icon']  ?? '🌱',
		'title' => $kv['promise_title'] ?? 'Our Promise',
		'sub'   => $kv['promise_sub']   ?? 'Pressed Fresh. Served Cool.',
		'tags'  => $kv['promise_tags']  ?? [],
	];
}

// ── Enquiry Types (Contact Form dropdown) ─────────────────────────────────────
function ch_get_enquiry_types(): array {
	$opt = get_option( 'ch_enquiry_types', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) ) return (array) $opt;
	// Real data fallback – edit real_data/csv/enquiry-types.csv to customise per client.
	$rows = CH_Real_Loader::csv( 'enquiry-types' );
	return ! empty( $rows ) ? $rows : [];
}

// ── Booking Occasions (Booking Wizard dropdown) ───────────────────────────────
function ch_get_occasions(): array {
	$opt = get_option( 'ch_occasions', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) ) return (array) $opt;
	// Real data fallback – edit real_data/csv/occasions.csv to customise per client.
	$rows = CH_Real_Loader::csv( 'occasions' );
	return ! empty( $rows ) ? array_column( $rows, 'value' ) : [];
}

// ── Hero Badges (dynamic list) ────────────────────────────────────────────────
function ch_get_hero_badges(): array {
	$opt = get_option( 'ch_hero_badges', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) ) return $opt;
	// Fallback: old badge_1..4 keys from ch_home_settings
	$h = ch_get_home_settings();
	return array_values( array_filter( [
		$h['hero_badge_1'] ?? 'No Added Sugar',
		$h['hero_badge_2'] ?? 'No Preservatives',
		$h['hero_badge_3'] ?? 'Pressed Live',
		$h['hero_badge_4'] ?? 'Served Chilled',
	] ) );
}

// ── Contact Settings ─────────────────────────────────────────────────────────
function ch_get_contact_settings(): array {
	$opt = get_option( 'ch_contact_settings', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	$defaults = [
		'recipient_email' => get_option( 'admin_email' ),
		'subject_prefix'  => '[The Cane House Enquiry]',
		'thank_you_msg'   => "Thanks for getting in touch! We'll be in touch shortly. 🌿",
	];
	return ! empty( $opt ) ? array_merge( $defaults, $opt ) : $defaults;
}

// ── Custom HTML Blocks ────────────────────────────────────────────────────────
function ch_get_html_block( string $key ): string {
	$blocks = get_option( 'ch_html_blocks', [] );
	if ( is_string( $blocks ) ) $blocks = json_decode( $blocks, true ) ?: [];
	return wp_kses_post( $blocks[ $key ] ?? '' );
}

// ── Interactive Story Cards ───────────────────────────────────────────────────
/**
 * Resolve any image reference to a usable URL.
 * Accepts: full URLs, protocol-relative (//), data URIs,
 * site-root paths (/wp-content/…), or theme-relative paths
 * (e.g. "assets/images/story/cane.jpg" or "story/cane.jpg").
 */
function ch_resolve_image_url( string $path ): string {
	$path = trim( $path );
	if ( $path === '' ) return '';

	// Absolute URL, protocol-relative, or data URI
	if ( preg_match( '#^(https?:)?//#i', $path ) || strpos( $path, 'data:' ) === 0 ) {
		return $path;
	}
	// Absolute path from site root
	if ( $path[0] === '/' ) {
		return home_url( $path );
	}
	// Bare filename → assume in /assets/images/
	if ( strpos( $path, '/' ) === false ) {
		$path = 'assets/images/' . $path;
	}
	// Theme-relative path
	return trailingslashit( get_template_directory_uri() ) . ltrim( $path, '/' );
}

/**
 * Get a normalised list of image URLs for a story card.
 * Supports: 'images' (array OR comma/newline string) and legacy single 'image'.
 */
function ch_card_images( $card ): array {
	$card = (array) $card;
	$out  = [];

	$raw = $card['images'] ?? '';
	if ( is_string( $raw ) && $raw !== '' ) {
		$raw = preg_split( '/[\r\n,]+/', $raw );
	}
	foreach ( (array) $raw as $p ) {
		$u = ch_resolve_image_url( (string) $p );
		if ( $u ) $out[] = $u;
	}
	// Legacy single image fallback
	if ( empty( $out ) && ! empty( $card['image'] ) ) {
		$u = ch_resolve_image_url( (string) $card['image'] );
		if ( $u ) $out[] = $u;
	}
	return array_values( array_unique( $out ) );
}

function ch_get_story_cards(): array {
	$opt = get_option( 'ch_story_cards', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) && is_array( $opt ) ) return (array) $opt;
	// Real data fallback – edit real_data/json/story-cards.json to customise per client.
	$rows = CH_Real_Loader::json( 'story-cards' );
	if ( ! empty( $rows ) ) return $rows;
	return [
	[
		'id'       => 'cultivation',
		'icon'     => '🌱',
		'label'    => 'Cultivation',
		'heading'  => 'Sugarcane Begins on the Farm',
		'body'     => 'Sugarcane is planted in fertile soil and carefully grown for 10–18 months. Farmers monitor irrigation, sunlight, and soil health to produce healthy stalks packed with natural sweetness.',
		'facts'    => [
			'10–18 month growing cycle',
			'Rich fertile farmland',
			'Naturally sun-ripened'
		],
		'images'   => [
			'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=700&q=80',
			'https://images.unsplash.com/photo-1464226184884-fa280b87c399?auto=format&fit=crop&w=700&q=80',
		],
	],

	[
		'id'       => 'harvesting',
		'icon'     => '🚜',
		'label'    => 'Harvesting',
		'heading'  => 'Harvested at Peak Sweetness',
		'body'     => 'Once mature, the sugarcane is carefully harvested and prepared for transport. Timing is critical to preserve maximum juice content and natural flavour.',
		'facts'    => [
			'Peak maturity harvesting',
			'Freshly cut stalks',
			'Quality inspected'
		],
		'images'   => [
			'https://images.unsplash.com/photo-1500382017468-9049fed747ef?auto=format&fit=crop&w=700&q=80',
			'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=700&q=80',
		],
	],

	[
		'id'       => 'transport',
		'icon'     => '🚚',
		'label'    => 'Farm to Store',
		'heading'  => 'Delivered Fresh From Trusted Farms',
		'body'     => 'Freshly harvested cane is transported directly from partner farms to our stores, helping preserve freshness and ensuring the highest quality juice.',
		'facts'    => [
			'Direct farm sourcing',
			'Fresh delivery process',
			'Minimal storage time'
		],
		'images'   => [
			'https://images.unsplash.com/photo-1502877338535-766e1452684a?auto=format&fit=crop&w=700&q=80',
			'https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?auto=format&fit=crop&w=700&q=80',
		],
	],

	[
		'id'       => 'juicing',
		'icon'     => '🥤',
		'label'    => 'Fresh Pressing',
		'heading'  => 'Pressed Fresh for Every Cup',
		'body'     => 'Every sugarcane stalk is washed and pressed live when ordered. No preservatives, concentrates, or artificial ingredients are added.',
		'facts'    => [
			'Pressed to order',
			'No preservatives',
			'100% natural juice'
		],
		'images'   => [
			'https://images.unsplash.com/photo-1610970881699-44a5587cabec?auto=format&fit=crop&w=700&q=80',
			'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=700&q=80',
		],
	],

	[
		'id'       => 'enjoy',
		'icon'     => '🍹',
		'label'    => 'Enjoyment',
		'heading'  => 'Farm Fresh Juice Served Instantly',
		'body'     => 'Customers enjoy pure sugarcane juice moments after pressing, experiencing the natural sweetness exactly as nature intended.',
		'facts'    => [
			'Served immediately',
			'Maximum freshness',
			'Naturally refreshing'
		],
		'images'   => [
			'https://images.unsplash.com/photo-1600271886742-f049cd451bba?auto=format&fit=crop&w=700&q=80',
			'https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&w=700&q=80',
		],
	],

	[
		'id'       => 'bagasse',
		'icon'     => '♻️',
		'label'    => 'Bagasse Recovery',
		'heading'  => 'Nothing Goes to Waste',
		'body'     => 'After juice extraction, the remaining sugarcane fibre known as bagasse is collected and repurposed instead of being discarded.',
		'facts'    => [
			'Natural sugarcane fibre',
			'Waste reduction',
			'Sustainable reuse'
		],
		'images'   => [
			'https://images.unsplash.com/photo-1520607162513-77705c0f0d4a?auto=format&fit=crop&w=700&q=80',
			'https://images.unsplash.com/photo-1497436072909-f5e4be0d1e91?auto=format&fit=crop&w=700&q=80',
		],
	],

	[
		'id'       => 'plates',
		'icon'     => '🍽️',
		'label'    => 'Eco Products',
		'heading'  => 'Turned Into Sustainable Tableware',
		'body'     => 'The recovered bagasse is transformed into biodegradable plates, bowls, and food containers that help replace plastic waste.',
		'facts'    => [
			'Biodegradable plates',
			'Plastic-free solution',
			'Food-safe products'
		],
		'images'   => [
			'https://images.unsplash.com/photo-1515003197210-e0cd71810b5f?auto=format&fit=crop&w=700&q=80',
			'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=700&q=80',
		],
	],

	[
		'id'       => 'compost',
		'icon'     => '🌍',
		'label'    => 'Return to Nature',
		'heading'  => 'Back to the Earth',
		'body'     => 'After use, the sugarcane-fibre products naturally break down and return nutrients to the soil, completing the circular lifecycle.',
		'facts'    => [
			'100% compostable',
			'Returns to soil',
			'Supports circular sustainability'
		],
		'images'   => [
			'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=700&q=80',
			'https://images.unsplash.com/photo-1465146344425-f00d5f5c8f07?auto=format&fit=crop&w=700&q=80',
		],
	],
];
}

// ── Story Section ─────────────────────────────────────────────────────────────
function ch_get_story_settings(): array {
	$opt = get_option( 'ch_story_settings', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return ! empty( $opt ) ? $opt : ch_mock_story_settings();
}

// ── Page helpers ──────────────────────────────────────────────────────────────
function ch_pagination(): void {
	$links = paginate_links( [ 'type' => 'array', 'prev_text' => '← Prev', 'next_text' => 'Next →' ] );
	if ( ! $links ) return;
	echo '<nav class="ch-pagination" aria-label="Posts navigation"><ul class="ch-pagination__list">';
	foreach ( $links as $link ) echo '<li class="ch-pagination__item">' . $link . '</li>';
	echo '</ul></nav>';
}

function ch_excerpt( int $length = 160 ): string {
	$text = wp_strip_all_tags( get_the_excerpt() ?: get_the_content() );
	return wp_trim_words( $text, 30, '…' );
}

// ── Stars renderer ────────────────────────────────────────────────────────────
function ch_stars( float $rating = 5.0, bool $echo = true ): string {
	$full  = (int) $rating;
	$half  = ( $rating - $full ) >= 0.5;
	$empty = 5 - $full - (int) $half;
	$html  = '<span class="ch-stars" aria-label="' . esc_attr( $rating . ' out of 5 stars' ) . '">';
	$html .= str_repeat( '<span class="ch-star ch-star--full">★</span>', $full );
	if ( $half ) $html .= '<span class="ch-star ch-star--half">★</span>';
	$html .= str_repeat( '<span class="ch-star ch-star--empty">☆</span>', max( 0, $empty ) );
	$html .= '</span>';
	if ( $echo ) echo $html;
	return $html;
}

// ── Services ──────────────────────────────────────────────────────────────────
function ch_get_services( string $status = 'active' ): array {
	global $wpdb;
	$table = ch_theme_table( 'services' );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE status = %s ORDER BY sort_order ASC", $status ) );
		if ( ! empty( $rows ) ) return $rows;
	}
	if ( class_exists( 'CH_Data' ) ) {
		return CH_Data::services();
	}
	return [];
}

// ── Team Members ──────────────────────────────────────────────────────────────
function ch_get_team_members( string $status = 'active' ): array {
	global $wpdb;
	$table = ch_theme_table( 'about_team' );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE status = %s ORDER BY sort_order ASC", $status ) );
		if ( ! empty( $rows ) ) return $rows;
	}
	if ( class_exists( 'CH_Data' ) ) {
		return CH_Data::about_team();
	}
	return [];
}

// ── Blog Posts ─────────────────────────────────────────────────────────────────
function ch_get_blog_posts( int $limit = 12, string $status = 'published' ): array {
	global $wpdb;
	$table = ch_theme_table( 'blog_posts' );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE status = %s AND published_at IS NOT NULL ORDER BY published_at DESC LIMIT %d", $status, $limit ) );
		if ( ! empty( $rows ) ) return $rows;
	}
	return [];
}

// ── Nav link suggestions (for admin nav builder) ─────────────────────────────
function ch_get_nav_link_suggestions(): array {
	$suggestions = [];
	$push = static function ( string $label, string $url, string $type ) use ( &$suggestions ): void {
		$key = strtolower( $label . '|' . $url );
		if ( isset( $suggestions[ $key ] ) ) return;
		$suggestions[ $key ] = [ 'label' => $label, 'url' => $url, 'type' => $type ];
	};
	$push( 'Home',      home_url( '/' ),          'page' );
	$push( 'How to Order', home_url( '/#how-to-order' ), 'anchor' );
	$push( 'Our Juices',   home_url( '/#build' ),        'anchor' );
	$push( 'Events',       home_url( '/#hire' ),         'anchor' );
	$push( 'Franchise',    home_url( '/#franchise' ),    'anchor' );
	$push( 'FAQ',          home_url( '/#faq' ),          'anchor' );
	$push( 'Contact',      home_url( '/#contact' ),      'anchor' );
	foreach ( get_pages( [ 'post_status' => [ 'publish', 'draft' ], 'sort_column' => 'post_title' ] ) as $p ) {
		$push( $p->post_title, get_permalink( $p->ID ) ?: home_url( '/' ), 'wp-page' );
	}
	return array_values( $suggestions );
}

// ── Important Notice ──────────────────────────────────────────────────────────
function ch_get_important_notice(): array {
	// Retrieve from plugin level (plugin manages data, theme displays it)
	if ( class_exists( 'AH_Notice_Helper' ) ) {
		return AH_Notice_Helper::get_notice();
	}
	// Fallback if plugin helper not available
	return [
		'enabled'        => false,
		'id'             => 'default',
		'title'          => 'Important Update',
		'message'        => '',
		'image'          => '',
		'button_label'   => '',
		'button_url'     => '',
	];
}



