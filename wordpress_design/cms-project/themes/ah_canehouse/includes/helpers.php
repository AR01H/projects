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
 * Returns 0 to mean "no limit — show everything on the homepage".
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
	// Default OFF — only show if explicitly enabled in admin
	return ! empty( $s['show_prices'] ) && $s['show_prices'] === '1';
}

// ── Certifications ────────────────────────────────────────────────────────────
function ch_get_certifications(): array {
	$s    = ch_get_settings();
	$saved = isset( $s['certifications'] ) ? $s['certifications'] : [];
	if ( is_string( $saved ) ) $saved = json_decode( $saved, true ) ?: [];
	if ( ! empty( $saved ) ) return (array) $saved;
	return [
		[
			'icon'  => '🍽️',
			'title' => 'Food Hygiene Rating 5',
			'desc'  => 'Awarded the highest food hygiene score by local authority. Inspected and verified.',
			'badge' => 'Grade 5 ★★★★★',
		],
		[
			'icon'  => '🛡️',
			'title' => 'Public Liability Insured',
			'desc'  => 'Fully covered with comprehensive public liability insurance for all UK events.',
			'badge' => 'Fully Insured',
		],
		[
			'icon'  => '🌿',
			'title' => 'Allergen Information',
			'desc'  => 'No added allergens. Pure fresh cane juice. Full allergen information available on request.',
			'badge' => 'Allergen Safe',
		],
		[
			'icon'  => '📋',
			'title' => 'HACCP Compliant',
			'desc'  => 'Full Hazard Analysis and Critical Control Point procedures in place for all operations.',
			'badge' => 'HACCP Cert.',
		],
		[
			'icon'  => '🤝',
			'title' => 'Multi-Event Handler',
			'desc'  => 'Experienced in serving 50 to 1,000+ guests. Reliable, punctual, professional every time.',
			'badge' => '200+ Events',
		],
		[
			'icon'  => '✅',
			'title' => 'Risk Assessed',
			'desc'  => 'Full risk assessment documentation provided for venues and event organisers on request.',
			'badge' => 'RA Documented',
		],
	];
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
		[ 'id' => 'how-to-order', 'label' => 'How to Order',  'type' => 'link', 'url' => home_url( '/#how-to-order' ), 'visible' => true, 'submenu' => [] ],
		[ 'id' => 'build',        'label' => 'Our Juices',    'type' => 'link', 'url' => home_url( '/#build' ),        'visible' => true, 'submenu' => [] ],
		[ 'id' => 'hire',         'label' => 'Events & Hire', 'type' => 'link', 'url' => home_url( '/#hire' ),         'visible' => true, 'submenu' => [] ],
		[ 'id' => 'franchise',    'label' => 'Franchise',     'type' => 'link', 'url' => home_url( '/#franchise' ),    'visible' => true, 'submenu' => [] ],
		[ 'id' => 'faq',          'label' => 'FAQ',           'type' => 'link', 'url' => home_url( '/#faq' ),          'visible' => true, 'submenu' => [] ],
	];
}

function ch_get_nav_cta(): array {
	$label    = defined( 'CH_NAV_CONTACT' ) ? CH_NAV_CONTACT : 'Contact Us';
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
function ch_get_reviews( int $limit = 6 ): array {
	if ( class_exists( 'AH_Model_Reviews' ) ) {
		$rows = AH_Model_Reviews::all( [ 'status' => 'active', 'limit' => $limit ] );
		if ( ! empty( $rows ) ) return $rows;
	}
	global $wpdb;
	$table = ch_theme_table( 'reviews' );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
		$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE status='active' ORDER BY id DESC LIMIT %d", $limit ) );
		if ( ! empty( $rows ) ) return $rows;
	}
	return array_slice( ch_mock_reviews(), 0, $limit );
}

// ── FAQs ──────────────────────────────────────────────────────────────────────
function ch_get_faqs( string $topic = '', int $limit = 20 ): array {
	if ( class_exists( 'AH_Model_FAQs' ) ) {
		$args = [ 'status' => 'active', 'limit' => $limit ];
		if ( $topic ) $args['topic'] = $topic;
		$rows = AH_Model_FAQs::all( $args );
		if ( ! empty( $rows ) ) return $rows;
	}
	global $wpdb;
	$table = ch_theme_table( 'faqs' );
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
		if ( $topic ) {
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE status='active' AND topic=%s ORDER BY sort_order ASC LIMIT %d", $topic, $limit ) );
		} else {
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$table}` WHERE status='active' ORDER BY sort_order ASC LIMIT %d", $limit ) );
		}
		if ( ! empty( $rows ) ) return $rows;
	}
	return ch_mock_faqs( $topic );
}

// ── Benefits ──────────────────────────────────────────────────────────────────
function ch_get_benefits(): array {
	$opt = get_option( 'ch_benefits', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return ! empty( $opt ) ? $opt : ch_mock_benefits();
}

// ── Events / Hire Packages ────────────────────────────────────────────────────
function ch_get_hire_packages(): array {
	$opt = get_option( 'ch_hire_packages', [] );
	if ( is_string( $opt ) ) $opt = json_decode( $opt, true ) ?: [];
	return ! empty( $opt ) ? $opt : ch_mock_hire_packages();
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
	return [
		[
			'id'       => 'origins',
			'icon'     => '🌍',
			'label'    => 'Ancient Origins',
			'heading'  => 'Sugarcane: 2,000 Years of History',
			'body'     => 'Sugarcane has been cultivated since 8000 BC in New Guinea and spread across South Asia, where ancient texts celebrated it as a healing drink. For centuries it was traded more than gold.',
			'facts'    => [ '8000 BC first cultivated', 'Travelled from Asia to Arabia', 'Documented in Ayurvedic medicine' ],
			'images'   => [
				'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?auto=format&fit=crop&w=700&q=80',
				'https://images.unsplash.com/photo-1591287083773-9a52bc35cb5e?auto=format&fit=crop&w=700&q=80',
			],
		],
		[
			'id'       => 'sourcing',
			'icon'     => '🚜',
			'label'    => 'Farm Sourced',
			'heading'  => 'Sourced From Trusted Farms',
			'body'     => 'We source our sugarcane directly from trusted suppliers who grow varieties specifically suited to juicing. Yellow cane, Barbados cane — each with a distinct natural sweetness and character.',
			'facts'    => [ 'Yellow & Red cane varieties', 'Grown without added chemicals', 'Freshness guaranteed' ],
			'images'   => [
				'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?auto=format&fit=crop&w=700&q=80',
				'https://images.unsplash.com/photo-1530267981375-f0de937f5f13?auto=format&fit=crop&w=700&q=80',
			],
		],
		[
			'id'       => 'pressing',
			'icon'     => '⚙️',
			'label'    => 'Live Pressed',
			'heading'  => 'Pressed Live in Front of You',
			'body'     => 'Every cup is pressed at the moment you order — not pre-made, not bottled. You watch it happen. Raw cane goes in, pure juice comes out. No heat, no chemicals, no shortcuts.',
			'facts'    => [ 'Pressed to order every time', 'Cold extraction preserves nutrients', 'Ready in under 60 seconds' ],
			'images'   => [
				'https://images.unsplash.com/photo-1437418747212-8d9709afab22?auto=format&fit=crop&w=700&q=80',
				'https://images.unsplash.com/photo-1610970881699-44a5587cabec?auto=format&fit=crop&w=700&q=80',
			],
		],
		[
			'id'       => 'blending',
			'icon'     => '🍋',
			'label'    => 'Custom Blends',
			'heading'  => 'Blended With Natural Botanicals',
			'body'     => 'From zingy lemon to spicy ginger, cooling mint to tropical pineapple — our blend range uses only real, fresh botanical ingredients. Nothing artificial, nothing pre-mixed.',
			'facts'    => [ '8+ natural blend options', 'Real lemon, ginger, mint', 'No syrups or concentrates' ],
			'images'   => [
				'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=700&q=80',
				'https://images.unsplash.com/photo-1622597467836-f3285f2131b8?auto=format&fit=crop&w=700&q=80',
				'https://images.unsplash.com/photo-1610970881699-44a5587cabec?auto=format&fit=crop&w=700&q=80',
			],
		],
		[
			'id'       => 'serving',
			'icon'     => '🥤',
			'label'    => 'Served Cool',
			'heading'  => 'Chilled and Served Immediately',
			'body'     => 'Every juice is served immediately after pressing — cool, fresh, and full of life. No ice that dilutes, no sitting in a fridge losing nutrients. Just pure cane, pressed and served.',
			'facts'    => [ 'Served within 60 seconds', 'Optimal temperature maintained', 'Maximum nutrients preserved' ],
			'images'   => [
				'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=700&q=80',
				'https://images.unsplash.com/photo-1600271886742-f049cd451bba?auto=format&fit=crop&w=700&q=80',
			],
		],
		[
			'id'       => 'pure',
			'icon'     => '💚',
			'label'    => 'Zero Additives',
			'heading'  => '100% Pure — Nothing Added',
			'body'     => 'No preservatives, no artificial sweeteners, no colourings, no emulsifiers. What goes in is cane. What comes out is juice. The way it should be. The way it has always been.',
			'facts'    => [ 'Zero preservatives', 'Zero artificial anything', 'Naturally allergen-free' ],
			'images'   => [
				'https://images.unsplash.com/photo-1610970881699-44a5587cabec?auto=format&fit=crop&w=700&q=80',
				'https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&w=700&q=80',
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
