<?php
defined( 'ABSPATH' ) || exit;

// ── Table helper (mirrors CMS plugin logic) ───────────────────────────────────
function ah_theme_table( string $name ): string {
	global $wpdb;
	$mid = defined( 'TABLE_MID_FIX' ) ? TABLE_MID_FIX : '_';
	return $wpdb->prefix . 'ah' . $mid . $name;
}

// ── Section Visibility ────────────────────────────────────────────────────────
/**
 * Returns true if a homepage/page section should be displayed.
 * Reads ah_section_visibility option; defaults all sections to visible.
 */
function ah_section_visible( string $key ): bool {
	static $map = null;
	if ( $map === null ) {
		$raw = get_option( 'ah_section_visibility', [] );
		if ( is_string( $raw ) ) $raw = json_decode( $raw, true ) ?: [];
		$map = (array) $raw;
	}
	return isset( $map[ $key ] ) ? (bool) $map[ $key ] : true;
}

// ── Site Settings ─────────────────────────────────────────────────────────────
function ah_get_settings(): array {
	global $wpdb;
	$table = $wpdb->prefix . 'ah_site_settings';
	$rows  = $wpdb->get_results( "SELECT setting_key, setting_val FROM `{$table}`" ) ?: [];
	$out   = [];
	foreach ( $rows as $r ) {
		$out[ $r->setting_key ] = $r->setting_val;
	}
	return $out;
}

// ── Home / Hero settings ──────────────────────────────────────────────────────
function ah_get_home_settings(): array {
	$opt = get_option( 'ah_home_settings', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) ) return (array) $opt;
	global $wpdb;
	$table = $wpdb->prefix . 'ah_site_settings';
	$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT setting_key, setting_val FROM `{$table}` WHERE group_name = %s", 'home' ) ) ?: [];
	$out   = [];
	foreach ( $rows as $r ) {
		$out[ $r->setting_key ] = $r->setting_val;
	}
	return $out;
}

// ── Buying Guides Navigation ──────────────────────────────────────────────────
function ah_buying_guides_nav(): array {
	$opt = get_option( 'ah_guide_nav', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return (array) $opt;
}

// ── Guide Categories ──────────────────────────────────────────────────────────
function ah_get_guide_categories(): array {
	global $wpdb;
	$tt  = "{$wpdb->prefix}ah_taxonomy_types";
	$t   = "{$wpdb->prefix}ah_taxonomies";
	$ct  = "{$wpdb->prefix}ah_content_taxonomies";
	$pt  = "{$wpdb->prefix}ah_taxonomy_parent_terms";
	$wt  = $wpdb->terms;           // wp_terms
	$wtt = $wpdb->term_taxonomy;   // wp_term_taxonomy

	// Terms inside the plugin's "category" type.
	// count: prefer WP native category post count (guides page uses WP_Query),
	//        fall back to plugin content_taxonomies count.
	// HAVING count > 0 hides categories that have no posts yet.
	// parent_color: background color from the parent term for card styling.
	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT tax.id, tax.name AS title, tax.slug, tax.description AS `desc`,
			        tax.image_id, tax.icon_emoji,
			        GREATEST( COALESCE(wtt.count, 0), COUNT(DISTINCT pct.object_id) ) AS `count`,
			        ptrow.color AS parent_color
			 FROM `{$t}` tax
			 INNER JOIN `{$tt}` type_row ON type_row.id = tax.type_id AND type_row.slug = %s
			 LEFT  JOIN `{$ct}` pct ON pct.taxonomy_id = tax.id AND pct.object_type = 'ah_post'
			 LEFT  JOIN `{$wt}`  wterm ON wterm.slug = tax.slug
			 LEFT  JOIN `{$wtt}` wtt   ON wtt.term_id = wterm.term_id AND wtt.taxonomy = 'category'
			 LEFT  JOIN `{$pt}`  ptrow ON ptrow.id = tax.parent_term_id
			 WHERE tax.status = 'active'
			 GROUP BY tax.id
			 HAVING `count` > 0
			 ORDER BY tax.sort_order ASC, tax.name ASC",
			'category'
		)
	) ?: [];
}

// ── Nav Topic Groups (for header dropdowns) ───────────────────────────────────
function ah_get_nav_buying_topics(): array {
	$opt = get_option( 'ah_nav_buying_topics', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return (array) $opt;
}

function ah_get_nav_finance_topics(): array {
	$opt = get_option( 'ah_nav_finance_topics', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return (array) $opt;
}

function ah_get_nav_legal_topics(): array {
	$opt = get_option( 'ah_nav_legal_topics', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return (array) $opt;
}

// ── Nav Visibility (which top-level items show) ───────────────────────────────
function ah_get_nav_visibility(): array {
	$opt = get_option( 'ah_nav_visibility', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	$defaults = [ 'buying' => true, 'finance' => true, 'legal' => true, 'news' => true, 'services' => true ];
	return array_merge( $defaults, (array) $opt );
}

// ── Nav Static Links (News, Services) ────────────────────────────────────────
function ah_get_nav_static_links(): array {
	$opt = get_option( 'ah_nav_static_links', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	$defaults = [
		
	];
	return ! empty( $opt ) ? array_merge( $defaults, $opt ) : $defaults;
}

// ── Nav CTA Button ────────────────────────────────────────────────────────────
function ah_get_nav_cta(): array {
	$opt = get_option( 'ah_cms_nav_cta', [] );
	if ( empty( $opt ) ) {
		$opt = get_option( 'ah_nav_cta', [] );
	}
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	$defaults = [ 'label' => AH_LABEL_GET_HELP, 'url' => AH_LINK_CONTACT ];
	return ! empty( $opt ) ? array_merge( $defaults, $opt ) : $defaults;
}

function ah_normalize_theme_url( string $url, string $fallback = '' ): string {
	$url = trim( wp_unslash( $url ) );
	if ( $url === '' ) {
		return $fallback;
	}
	if ( preg_match( '#^(https?:)?//#i', $url ) || strpos( $url, '#' ) === 0 || strpos( $url, 'mailto:' ) === 0 || strpos( $url, 'tel:' ) === 0 ) {
		return $url;
	}
	return '/' . trim( $url, '/' ) . '/';
}

function ah_get_theme_navigation(): array {
	$opt = get_option( 'ah_cms_navigation', [] );
	if ( empty( $opt ) ) {
		$opt = get_option( 'ah_theme_navigation', [] );
	}
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) && is_array( $opt ) ) {
		return ah_normalize_theme_navigation( $opt );
	}

	return [];
}

function ah_normalize_theme_navigation( array $items ): array {
	$normalized = [];
	foreach ( $items as $index => $item ) {
		$item  = is_object( $item ) ? (array) $item : (array) $item;
		$label = sanitize_text_field( $item['label'] ?? '' );
		if ( $label === '' ) {
			continue;
		}

		$type    = ( $item['type'] ?? 'link' ) === 'dropdown' ? 'dropdown' : 'link';
		$submenu = [];
		foreach ( (array) ( $item['submenu'] ?? [] ) as $sub_item ) {
			$sub_item = is_object( $sub_item ) ? (array) $sub_item : (array) $sub_item;
			$sub_label = sanitize_text_field( $sub_item['label'] ?? '' );
			$sub_url   = ah_normalize_theme_url( (string) ( $sub_item['url'] ?? '' ) );
			if ( $sub_label === '' || $sub_url === '' ) {
				continue;
			}
			$submenu[] = [
				'label'       => $sub_label,
				'url'         => $sub_url,
				'description' => sanitize_text_field( $sub_item['description'] ?? '' ),
				'icon'        => sanitize_text_field( $sub_item['icon'] ?? '' ),
				'highlight'   => ! empty( $sub_item['highlight'] ),
			];
		}

		$normalized[] = [
			'id'          => sanitize_title( $item['id'] ?? $label ?: 'nav-' . $index ),
			'label'       => $label,
			'type'        => $type,
			'url'         => $type === 'link' ? ah_normalize_theme_url( (string) ( $item['url'] ?? '' ), home_url( '/' ) ) : '',
			'visible'     => isset( $item['visible'] ) ? (bool) $item['visible'] : true,
			'icon'        => sanitize_text_field( $item['icon'] ?? '' ),
			'description' => sanitize_text_field( $item['description'] ?? '' ),
			'submenu'     => $submenu,
		];
	}

	return $normalized;
}



function ah_get_nav_link_suggestions(): array {
	$suggestions = [];

	$push = static function ( string $label, string $url, string $type ) use ( &$suggestions ): void {
		$key = strtolower( $label . '|' . $url );
		if ( isset( $suggestions[ $key ] ) ) {
			return;
		}
		$suggestions[ $key ] = [
			'label' => $label,
			'url'   => $url,
			'type'  => $type,
		];
	};

	$push( 'Home', home_url( '/' ), 'page' );
	$push( 'Blog', home_url( '/blog/' ), 'page' );
	$push( 'Services', home_url( '/services/' ), 'page' );
	$push( 'Contact', home_url( AH_LINK_CONTACT ), 'page' );

	foreach ( get_pages( [ 'post_status' => [ 'publish', 'draft', 'private' ], 'sort_column' => 'post_title' ] ) as $page ) {
		$push(
			$page->post_title ?: ucwords( str_replace( '-', ' ', $page->post_name ) ),
			get_permalink( $page->ID ) ?: home_url( '/' . $page->post_name . '/' ),
			'wp-page'
		);
	}

	$posts = get_posts( [
		'post_type'      => 'post',
		'post_status'    => [ 'publish', 'draft' ],
		'posts_per_page' => 50,
		'orderby'        => 'date',
		'order'          => 'DESC',
	] );
	foreach ( $posts as $post ) {
		$push( get_the_title( $post ) ?: 'Post #' . $post->ID, get_permalink( $post ) ?: home_url( '/?p=' . $post->ID ), 'post' );
	}

	foreach ( ah_get_static_pages() as $page ) {
		$push( $page['label'], $page['url'], 'static-page' );
	}

	return array_values( $suggestions );
}

function ah_get_theme_footer(): array {
	$opt = get_option( 'ah_cms_footer', [] );
	if ( empty( $opt ) ) {
		$opt = get_option( 'ah_theme_footer', [] );
	}
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	if ( ! empty( $opt ) && is_array( $opt ) ) {
		return ah_normalize_theme_footer( $opt );
	}

	return ah_build_legacy_theme_footer();
}

function ah_normalize_theme_footer( array $footer ): array {
	$columns = [];
	foreach ( (array) ( $footer['columns'] ?? [] ) as $column ) {
		$column = is_object( $column ) ? (array) $column : (array) $column;
		$title = sanitize_text_field( $column['title'] ?? '' );
		$items = [];
		foreach ( (array) ( $column['items'] ?? [] ) as $item ) {
			$item = is_object( $item ) ? (array) $item : (array) $item;
			$label = sanitize_text_field( $item['label'] ?? '' );
			$url   = ah_normalize_theme_url( (string) ( $item['url'] ?? '' ) );
			if ( $label === '' || $url === '' ) {
				continue;
			}
			$items[] = [
				'label'     => $label,
				'url'       => $url,
				'highlight' => ! empty( $item['highlight'] ),
			];
		}
		if ( $title !== '' || ! empty( $items ) ) {
			$columns[] = [
				'title' => $title ?: 'Links',
				'items' => $items,
			];
		}
	}

	$legal_links = [];
	foreach ( (array) ( $footer['legal_links'] ?? [] ) as $item ) {
		$item = is_object( $item ) ? (array) $item : (array) $item;
		$label = sanitize_text_field( $item['label'] ?? '' );
		$url   = ah_normalize_theme_url( (string) ( $item['url'] ?? '' ) );
		if ( $label === '' || $url === '' ) {
			continue;
		}
		$legal_links[] = [ 'label' => $label, 'url' => $url ];
	}

	return [
		'brand_description' => wp_kses_post( $footer['brand_description'] ?? '' ),
		'badge_text'        => sanitize_text_field( $footer['badge_text'] ?? '' ),
		'columns'           => $columns,
		'contact'           => [
			'phone_note'   => sanitize_text_field( $footer['contact']['phone_note'] ?? '' ),
			'email_note'   => sanitize_text_field( $footer['contact']['email_note'] ?? '' ),
			'address_note' => sanitize_text_field( $footer['contact']['address_note'] ?? '' ),
		],
		'cta'               => [
			'label' => sanitize_text_field( $footer['cta']['label'] ?? '' ),
			'url'   => ah_normalize_theme_url( (string) ( $footer['cta']['url'] ?? '' ), AH_LINK_CONTACT ),
		],
		'legal_links'       => $legal_links,
	];
}

function ah_build_legacy_theme_footer(): array {
	$settings    = ah_get_settings();
	$guides      = ah_buying_guides_nav();
	$consult     = $settings['consultation_url'] ?? home_url( '/free-consultation/' );
	$static_nav  = ah_get_nav_static_page_links();
	$footer_page_links = [];

	foreach ( $static_nav as $item ) {
		$item = is_object( $item ) ? (array) $item : (array) $item;
		if ( ( $item['section'] ?? '' ) !== 'footer' ) {
			continue;
		}
		$slug = sanitize_title( $item['slug'] ?? '' );
		if ( $slug === '' ) {
			continue;
		}
		$footer_page_links[] = [
			'label' => $item['label'] ?: ucwords( str_replace( '-', ' ', $slug ) ),
			'url'   => home_url( '/' . $slug . '/' ),
		];
	}

	$guide_links = [];
	foreach ( $guides as $guide ) {
		$guide = is_object( $guide ) ? (array) $guide : (array) $guide;
		$guide_links[] = [
			'label'     => $guide['title'] ?? '',
			'url'       => home_url( '/guides/' . sanitize_title( $guide['slug'] ?? '' ) . '/' ),
			'highlight' => ! empty( $guide['highlight'] ),
		];
	}
	$guide_links[] = [
		'label' => 'Free Consultation Guide',
		'url'   => $consult,
	];

	$columns = [
		[
			'title' => 'Buying Guides',
			'items' => $guide_links,
		],
		[
			'title' => 'Company',
			'items' => [
				[ 'label' => 'Home', 'url' => home_url( '/' ) ],
				[ 'label' => 'Services', 'url' => home_url( '/services/' ) ],
				[ 'label' => 'About Us', 'url' => home_url( '/about/' ) ],
				[ 'label' => 'Client Stories', 'url' => home_url( '/client-stories/' ) ],
				[ 'label' => 'Blog', 'url' => home_url( '/blog/' ) ],
				[ 'label' => 'Contact', 'url' => home_url( AH_LINK_CONTACT ) ],
			],
		],
	];

	if ( ! empty( $footer_page_links ) ) {
		$columns[] = [
			'title' => 'Resources',
			'items' => $footer_page_links,
		];
	}

	return [
		'brand_description' => "The UK's dedicated buyer's agent - we work exclusively for you, not the seller. Saving you time, stress, and thousands of pounds on your most important purchase.",
		'badge_text'        => 'Proudly serving UK home buyers',
		'columns'           => $columns,
		'contact'           => [
			'phone_note'   => 'Mon-Sat, 9am-6pm',
			'email_note'   => 'We reply within 2 hours',
			'address_note' => 'Covering all of England & Wales',
		],
		'cta'               => [
			'label' => 'Contact Us->',
			'url'   => $consult,
		],
		'legal_links'       => [
			[ 'label' => 'Privacy Policy', 'url' => home_url( '/privacy-policy/' ) ],
			[ 'label' => 'Terms', 'url' => home_url( '/terms/' ) ],
			[ 'label' => 'Refund Policy', 'url' => home_url( '/refund-policy/' ) ],
		],
	];
}

// ── Featured Properties ───────────────────────────────────────────────────────
function ah_get_properties( int $limit = 6 ): array {
	global $wpdb;
	$table = ah_theme_table( 'properties' );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE status='active' ORDER BY sort_order ASC LIMIT %d", $limit ) );
		if ( ! empty( $rows ) ) return $rows;
	}
	$opt = get_option( 'ah_featured_properties', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return ! empty( $opt ) ? array_slice( $opt, 0, $limit ) : [];
}

// ── Contact Form Settings ─────────────────────────────────────────────────────
function ah_get_contact_settings(): array {
	$opt = get_option( 'ah_contact_settings', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	$defaults = [
		'recipient_email' => get_option( 'admin_email' ),
		'subject_prefix'  => CLIENT_ENQUIRY_SUBJECT_PREFIX,
		'thank_you_msg'   => "Thanks for getting in touch! We'll respond within one working day.",
	];
	return ! empty( $opt ) ? array_merge( $defaults, $opt ) : $defaults;
}

// ── Custom HTML Blocks ────────────────────────────────────────────────────────
function ah_get_html_block( string $key ): string {
	$blocks = get_option( 'ah_html_blocks', [] );
	if ( is_string( $blocks ) ) $blocks = json_decode( $blocks, true ) ?: [];
	return wp_kses_post( $blocks[ $key ] ?? '' );
}

// ── Process Steps ─────────────────────────────────────────────────────────────
function ah_get_process_steps(): array {
	global $wpdb;
	$table = $wpdb->prefix . 'ah_site_settings';
	$val   = $wpdb->get_var( $wpdb->prepare( "SELECT setting_val FROM `{$table}` WHERE setting_key = %s", 'process_steps' ) );
	if ( $val ) {
		$decoded = json_decode( $val, true );
		if ( is_array( $decoded ) && ! empty( $decoded ) ) return $decoded;
	}
	return [];
}

// ── Site Stats ────────────────────────────────────────────────────────────────
function ah_get_site_stats(): array {
	global $wpdb;
	$table = $wpdb->prefix . 'ah_site_settings';
	$val   = $wpdb->get_var( $wpdb->prepare( "SELECT setting_val FROM `{$table}` WHERE setting_key = %s", 'site_stats' ) );
	if ( $val ) {
		$decoded = json_decode( $val, true );
		if ( is_array( $decoded ) && ! empty( $decoded ) ) return $decoded;
	}
	return [];
}

// ── Trust Signals ─────────────────────────────────────────────────────────────
function ah_get_trust_signals(): array {
	global $wpdb;
	$table = $wpdb->prefix . 'ah_site_settings';
	$val   = $wpdb->get_var( $wpdb->prepare( "SELECT setting_val FROM `{$table}` WHERE setting_key = %s", 'trust_signals' ) );
	if ( $val ) {
		$decoded = json_decode( $val, true );
		if ( is_array( $decoded ) && ! empty( $decoded ) ) return $decoded;
	}
	return [];
}

// ── News Bar Items ────────────────────────────────────────────────────────────
function ah_get_news_bar_items(): array {
	if ( ! class_exists( 'AH_Newsbar_Model' ) ) {
		return [];
	}
	return ( new AH_Newsbar_Model() )->get_active();
}

// ── Services ──────────────────────────────────────────────────────────────────
function ah_get_services_bullet_points(array $ids = []): array {
	if (!$ids) return [];
	global $wpdb;
	$table = $wpdb->prefix . 'ah_service_bullet_points';
	$placeholders = implode(',', array_fill(0, count($ids), '%d'));
	$sql = "
		SELECT *
		FROM {$table}
		WHERE service_id IN ($placeholders)
		ORDER BY sort_order ASC
	";
	$results = $wpdb->get_results(
		$wpdb->prepare($sql, ...$ids),
		ARRAY_A
	) ?: [];
	$data = [];
	foreach ($results as $row) {
		$data[$row['service_id']][] = $row;
	}
	return $data;
}

function ah_get_services( int $limit = 12 ): array {
	global $wpdb;
	$table = $wpdb->prefix . 'ah_services';
	return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE status='active' ORDER BY sort_order ASC LIMIT %d", $limit ) ) ?: [];
}

// ── Team ──────────────────────────────────────────────────────────────────────
function ah_get_team( int $limit = 12 ): array {
	global $wpdb;
	$table = $wpdb->prefix . 'ah_team_members';
	return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE status='active' ORDER BY sort_order ASC LIMIT %d", $limit ) ) ?: [];
}

// ── Reviews ───────────────────────────────────────────────────────────────────
function ah_get_reviews( int $limit = 6 ): array {
	global $wpdb;
	$table = $wpdb->prefix . 'ah_reviews';
	return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE status='active' ORDER BY sort_order ASC, id DESC LIMIT %d", $limit ) ) ?: [];
}

// ── FAQs ──────────────────────────────────────────────────────────────────────
function ah_get_faqs( int $limit = 20 ): array {
	global $wpdb;
	$table = $wpdb->prefix . 'ah_faqs';
	return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE status='active' ORDER BY sort_order ASC LIMIT %d", $limit ) ) ?: [];
}

// ── Star rating renderer ──────────────────────────────────────────────────────
function ah_stars( float $rating = 5.0, bool $echo = true ): string {
	$full  = (int) $rating;
	$half  = ( $rating - $full ) >= 0.5;
	$empty = 5 - $full - (int) $half;
	$html  = '<span class="stars" aria-label="'. esc_attr( $rating . ' out of 5 stars' ) .'">';
	$html .= str_repeat( '<span class="star star--full">★</span>', $full );
	if ( $half ) $html .= '<span class="star star--half">★</span>';
	$html .= str_repeat( '<span class="star star--empty">☆</span>', max( 0, $empty ) );
	$html .= '</span>';
	if ( $echo ) echo $html;
	return $html;
}

// ── SVG icons ─────────────────────────────────────────────────────────────────
function ah_icon( string $key, int $size = 24, string $class = '' ): string {
	static $icons = null;
	if ( $icons === null ) {
		$icons = [
			'check'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
			'arrow'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>',
			'phone'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>',
			'mail'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
			'star'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>',
			'home'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
			'shield' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
			'key'    => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>',
			'chart'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
			'clock'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
			'users'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
			'search' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>',
			'plus'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>',
			'minus'  => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>',
		];
	}
	$path = $icons[ $key ] ?? $icons['check'];
	$cls  = $class ? ' class="' . esc_attr( $class ) . '"' : '';
	return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor"' . $cls . ' aria-hidden="true">' . $path . '</svg>';
}

// ── Page helpers ──────────────────────────────────────────────────────────────
function ah_breadcrumb(): void {
	if ( is_front_page() ) return;
	$crumbs = [ [ 'label' => 'Home', 'url' => home_url( '/' ) ] ];
	if ( is_singular( 'post' ) ) {
		$crumbs[] = [ 'label' => 'Blog', 'url' => home_url( '/blog/' ) ];
		$crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
	} elseif ( is_page() ) {
		$crumbs[] = [ 'label' => get_the_title(), 'url' => '' ];
	} elseif ( is_category() || is_tag() || is_archive() ) {
		$crumbs[] = [ 'label' => get_the_archive_title(), 'url' => '' ];
	} elseif ( is_search() ) {
		$crumbs[] = [ 'label' => 'Search: ' . get_search_query(), 'url' => '' ];
	} elseif ( is_404() ) {
		$crumbs[] = [ 'label' => 'Page Not Found', 'url' => '' ];
	}
	echo '<nav class="breadcrumb" aria-label="<?php echo esc_attr( TXT_BREADCRUMB ); ?>"><ol class="breadcrumb__list">';
	foreach ( $crumbs as $i => $c ) {
		$last = $i === count( $crumbs ) - 1;
		echo  '<li class="breadcrumb__item">';
		if ( ! $last && $c['url'] ) {
			echo "<a href=" . esc_url( $c['url'] ) . " class='breadcrumb__link'> ". esc_html( $c["label"] ) . "</a>";
			echo '<span class="breadcrumb__sep" aria-hidden="true">></span>';
		} else {
			echo '<span class="breadcrumb__current" aria-current="page">' . esc_html( $c["label"] ) . '</span>';
		}
		echo '</li>';
	}
	echo '</ol></nav>';
}

function ah_pagination(): void {
	$links = paginate_links( [ 'type' => 'array', 'prev_text' => '← Prev', 'next_text' => 'Next →' ] );
	if ( ! $links ) return;
	echo '<nav class="pagination" aria-label="<?php echo esc_attr( TXT_POSTS_NAVIGATION ); ?>"><ul class="pagination__list">';
	foreach ( $links as $link ) echo ( '<li class="pagination__item">'. $link . '</li>' );
	echo '</ul></nav>';
}

function ah_excerpt( int $length = 30 ): string {
	$text = wp_strip_all_tags( get_the_excerpt() ?: get_the_content() );
	return wp_trim_words( $text, $length, '…' );
}

function ah_reading_time( int $post_id = 0 ): string {
	$content = get_post_field( 'post_content', $post_id ?: get_the_ID() );
	$count   = str_word_count( wp_strip_all_tags( $content ) );
	return max( 1, (int) ceil( $count / 200 ) ) . ' min read';
}

// ── Static Pages ──────────────────────────────────────────────────────────────
function ah_get_static_pages(): array {
	$dir   = trailingslashit( get_template_directory() ) . 'static/';
	$files = glob( $dir . '*.html' ) ?: [];
	$pages = [];
	foreach ( $files as $file ) {
		$slug    = basename( $file, '.html' );
		$label   = ucwords( str_replace( '-', ' ', $slug ) );
		$wp_page = get_page_by_path( $slug );
		$pages[] = [
			'slug'        => $slug,
			'label'       => $label,
			'url'         => $wp_page ? get_permalink( $wp_page->ID ) : home_url( '/' . $slug . '/' ),
			'has_wp_page' => (bool) $wp_page,
		];
	}
	return $pages;
}

function ah_get_static_quick_links(): array {
	$opt = get_option( 'ah_static_quick_links', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return (array) $opt;
}

function ah_get_nav_static_page_links(): array {
	$opt = get_option( 'ah_nav_static_page_links', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return (array) $opt;
}

function ah_get_builder_pages( int $limit = 20 ): array {
	global $wpdb;
	$table = $wpdb->prefix . 'ah_builder_pages';
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	if ( $exists !== $table ) {
		return array();
	}
	return $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM `{$table}` ORDER BY updated_at DESC LIMIT %d", $limit )
	) ?: array();
}

function ah_get_file_links( int $limit = 20 ): array {
	global $wpdb;
	$table = $wpdb->prefix . 'ah_file_links';
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	if ( $exists !== $table ) {
		return array();
	}

	$rows = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM `{$table}` ORDER BY created_at DESC LIMIT %d", $limit )
	) ?: array();

	$upload = wp_upload_dir();
	foreach ( $rows as $row ) {
		$row->file_url = trailingslashit( $upload['baseurl'] ) . 'ah-files/' . ltrim( (string) $row->file_path, '/' );
	}
	return $rows;
}

function ah_get_forms_summary(): array {
	if ( class_exists( 'AH_Form_Builder' ) && method_exists( 'AH_Form_Builder', 'get_all' ) ) {
		return AH_Form_Builder::get_all();
	}

	global $wpdb;
	$table = $wpdb->prefix . 'ah_forms';
	$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
	if ( $exists !== $table ) {
		return array();
	}

	return $wpdb->get_results( "SELECT * FROM `{$table}` ORDER BY id ASC" ) ?: array();
}

/**
 * Resolve a meaningful icon for a topic / parent term.
 *
 * Honours an explicitly-set emoji only when it is a real emoji/symbol - a plain
 * letter or word (some terms store just an initial, e.g. "B") is ignored and the
 * name/slug is matched against a keyword map instead.
 *
 * @param string $name     Term display name.
 * @param string $slug     Term slug.
 * @param string $explicit The stored icon_emoji value, if any.
 * @return string A single emoji.
 */
if ( ! function_exists( 'ah_topic_icon' ) ) {
	function ah_topic_icon( string $name = '', string $slug = '', string $explicit = '' ): string {
		$explicit = trim( $explicit );
		// Real emoji/symbol = contains a non-ASCII char. Plain "B"/"News" -> ignore.
		if ( $explicit !== '' && preg_match( '/[^\x00-\x7F]/u', $explicit ) ) {
			return $explicit;
		}
		$h   = strtolower( $name . ' ' . $slug );
		$map = [
			'first-time' => '🔑', 'first time' => '🔑',
			'mortgage'   => '🏦', 'finance' => '💷', 'remortgage' => '🏦',
			'calculat'   => '🧮', 'stamp duty' => '🧾',
			'legal'      => '⚖️', 'conveyanc' => '⚖️',
			'invest'     => '📈', 'btl' => '📈', 'buy-to-let' => '📈',
			'market'     => '📊', 'news' => '📰',
			'reloc'      => '✈️', 'international' => '🌍',
			'luxury'     => '💎',
			'tip'        => '💡', 'advice' => '💡',
			'sell'       => '🏷️',
			'buy'        => '🏡', 'home' => '🏡', 'purchase' => '🏡',
			'rent'       => '🔑', 'landlord' => '🏘️',
		];
		foreach ( $map as $needle => $icon ) {
			if ( strpos( $h, $needle ) !== false ) return $icon;
		}
		return '📂';
	}
}
