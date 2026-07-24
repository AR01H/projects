<?php
/**
 * apis/services.php - Data services (API abstraction layer).
 *
 * RULE: Templates and intermediate logic NEVER read data sources directly -
 *       they call these service functions. Today the data comes from
 *       data/json/*.json via ADN_Real_Loader; when a real backend/API/DB
 *       arrives, swap the internals here and nothing else changes.
 *
 * Pattern: adn_service_<thing>(): array
 */

defined( 'ABSPATH' ) || exit;

/**
 * Full home page content - one call returns every section's data
 * (mirrors what GET /advaithhomes/v1/home serves).
 */
function adn_service_home_data() {
	return ADN_Real_Loader::json( 'home_page' );
}

/**
 * Site chrome: logo, navigation, header CTA and footer content.
 * Shared by every page that renders the main header/footer.
 *
 * Data sources, in order of precedence (highest wins):
 *   1. Plugin nav/footer editors  - ah_cms_navigation, ah_cms_nav_cta, ah_cms_footer
 *   2. Plugin site settings DB    - chrome_* rows in ah_site_settings (group: chrome)
 *      Managed at WP Admin → CMS Admin → Settings → chrome tab.
 *   3. data/json/site_chrome.json - hard-coded fallback; only used when the DB
 *      rows are missing (e.g. first install before migration runs).
 *
 * Statically cached per request so the many callers (one per page) cost
 * exactly one DB query total.
 */
function adn_service_site_chrome() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	// Layer 1 (lowest priority): JSON fallback.
	$chrome = ADN_Real_Loader::json( 'site_chrome' );
	$chrome = is_array( $chrome ) ? $chrome : array();

	// Layer 2: DB settings (chrome group in ah_site_settings).
	$chrome = adn_chrome_overlay_db_settings( $chrome );

	// Layer 3: nav items from the Navigation Editor.
	$plugin_nav = adn_chrome_plugin_nav();
	if ( ! empty( $plugin_nav ) ) {
		$chrome['nav'] = $plugin_nav;
	}

	// Layer 3: header CTA from the Navigation Editor.
	$plugin_cta = adn_chrome_plugin_cta();
	if ( ! empty( $plugin_cta ) ) {
		$chrome['header_cta'] = $plugin_cta;
	}

	// Layer 3: footer columns + legal links from the Footer Editor.
	$json_footer   = isset( $chrome['footer'] ) && is_array( $chrome['footer'] ) ? $chrome['footer'] : array();
	$plugin_footer = adn_chrome_plugin_footer( $json_footer );
	if ( ! empty( $plugin_footer ) ) {
		$chrome['footer'] = $plugin_footer;
	}

	// Layer 4: social links from DB social group - only show platforms with a URL set.
	$_social_map = array(
		'facebook_url'  => array( 'label' => 'Facebook',  'icon' => 'fab fa-facebook-f' ),
		'instagram_url' => array( 'label' => 'Instagram', 'icon' => 'fab fa-instagram' ),
		'twitter_url'   => array( 'label' => 'X / Twitter', 'icon' => 'fab fa-x-twitter' ),
		'linkedin_url'  => array( 'label' => 'LinkedIn',  'icon' => 'fab fa-linkedin-in' ),
		'youtube_url'   => array( 'label' => 'YouTube',   'icon' => 'fab fa-youtube' ),
		'tiktok_url'    => array( 'label' => 'TikTok',    'icon' => 'fab fa-tiktok' ),
	);
	$_db_socials = array();
	foreach ( $_social_map as $_sk => $_sm ) {
		$_sv = adn_get_social_setting( $_sk );
		if ( '' !== $_sv ) {
			$_db_socials[] = array( 'url' => $_sv, 'label' => $_sm['label'], 'icon' => $_sm['icon'] );
		}
	}
	// Always replace footer social with DB data.
	// Empty array = no social links shown (prevents JSON '#' placeholders from appearing).
	if ( ! isset( $chrome['footer'] ) || ! is_array( $chrome['footer'] ) ) {
		$chrome['footer'] = array();
	}
	$chrome['footer']['social'] = $_db_socials;

	$cache = $chrome;
	return $cache;
}

/**
 * Load all chrome_* settings from the DB in a single query, return as key → value map.
 * Queries by setting_key prefix so it works regardless of which admin tab each row lives in
 * (general, social, etc.) - no dependency on group_name.
 * Statically cached per request.
 *
 * @return array<string,string>
 */
function adn_chrome_db_settings(): array {
	static $settings_cache = null;
	if ( null !== $settings_cache ) {
		return $settings_cache;
	}
	if ( ! function_exists( 'get_option' ) ) {
		$settings_cache = array();
		return $settings_cache;
	}
	global $wpdb;
	$table = $wpdb->prefix . 'ah_site_settings';
	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$rows  = $wpdb->get_results( "SELECT setting_key, setting_val FROM `{$table}` WHERE setting_key LIKE 'chrome_%'", ARRAY_A );
	$settings_cache = array();
	if ( is_array( $rows ) ) {
		foreach ( $rows as $row ) {
			$settings_cache[ (string) $row['setting_key'] ] = (string) $row['setting_val'];
		}
	}
	return $settings_cache;
}

/**
 * Apply chrome_* DB settings on top of the JSON base array.
 * Only overwrites a key when the DB value is non-empty.
 * Copyright supports %YEAR% which is replaced with the current 4-digit year.
 *
 * @param array $chrome  The JSON-loaded base chrome array.
 * @return array         Chrome array with DB values overlaid.
 */
function adn_chrome_overlay_db_settings( array $chrome ): array {
	$s = adn_chrome_db_settings();
	if ( empty( $s ) ) {
		return $chrome;
	}

	// ── Logo ──────────────────────────────────────────────────────────────────
	$logo = isset( $chrome['logo'] ) && is_array( $chrome['logo'] ) ? $chrome['logo'] : array();
	foreach ( array( 'icon' => 'chrome_logo_icon', 'name' => 'chrome_logo_name', 'sub' => 'chrome_logo_sub', 'url' => 'chrome_logo_url' ) as $field => $key ) {
		if ( isset( $s[ $key ] ) && '' !== $s[ $key ] ) {
			$logo[ $field ] = $s[ $key ];
		}
	}
	$chrome['logo'] = $logo;

	// ── Search ────────────────────────────────────────────────────────────────
	if ( isset( $s['chrome_search_ph'] ) && '' !== $s['chrome_search_ph'] ) {
		$chrome['search'] = isset( $chrome['search'] ) && is_array( $chrome['search'] ) ? $chrome['search'] : array();
		$chrome['search']['placeholder'] = $s['chrome_search_ph'];
	}

	// ── Footer brand ──────────────────────────────────────────────────────────
	$footer = isset( $chrome['footer'] ) && is_array( $chrome['footer'] ) ? $chrome['footer'] : array();
	$brand  = isset( $footer['brand'] ) && is_array( $footer['brand'] ) ? $footer['brand'] : array();
	foreach ( array( 'icon' => 'chrome_footer_icon', 'name' => 'chrome_footer_name', 'sub' => 'chrome_footer_sub' ) as $field => $key ) {
		if ( isset( $s[ $key ] ) && '' !== $s[ $key ] ) {
			$brand[ $field ] = $s[ $key ];
		}
	}
	$footer['brand'] = $brand;

	// ── Social links - built from individual URL fields in the Social settings tab ──
	// Each platform is only included when its URL is set to something other than '#'.
	$_social_map = array(
		'chrome_social_facebook'  => array( 'label' => 'Facebook',  'icon' => 'fa-brands fa-facebook'  ),
		'chrome_social_instagram' => array( 'label' => 'Instagram', 'icon' => 'fa-brands fa-instagram' ),
		'chrome_social_youtube'   => array( 'label' => 'YouTube',   'icon' => 'fa-brands fa-youtube'   ),
	);
	$_built_social = array();
	foreach ( $_social_map as $_sk => $_meta ) {
		if ( ! empty( $s[ $_sk ] ) && '#' !== trim( $s[ $_sk ] ) ) {
			$_built_social[] = array(
				'label' => $_meta['label'],
				'icon'  => $_meta['icon'],
				'url'   => $s[ $_sk ],
			);
		}
	}
	if ( ! empty( $_built_social ) ) {
		$footer['social'] = $_built_social;
	}

	// ── Copyright - %YEAR% replaced with current year ─────────────────────────
	if ( isset( $s['chrome_copyright'] ) && '' !== $s['chrome_copyright'] ) {
		$footer['copyright'] = str_replace( '%YEAR%', (string) gmdate( 'Y' ), $s['chrome_copyright'] );
	}

	// ── Made-with line ────────────────────────────────────────────────────────
	if ( isset( $s['chrome_made_with'] ) && '' !== $s['chrome_made_with'] ) {
		$footer['made_with'] = $s['chrome_made_with'];
	}

	// ── Disclaimer ────────────────────────────────────────────────────────────
	if ( isset( $s['chrome_disclaimer'] ) && '' !== $s['chrome_disclaimer'] ) {
		$footer['disclaimer'] = $s['chrome_disclaimer'];
	}

	$chrome['footer'] = $footer;
	return $chrome;
}

/**
 * Read + JSON-decode one of the plugin's chrome options.
 * The plugin stores these as JSON strings; tolerate arrays too.
 *
 * @return array Decoded option value, or empty array when unavailable.
 */
function adn_chrome_option( $key ) {
	if ( ! function_exists( 'get_option' ) ) {
		return array();
	}
	$value = get_option( $key, '' );
	if ( is_string( $value ) ) {
		if ( '' === $value ) {
			return array();
		}
		$decoded = json_decode( $value, true );
		return is_array( $decoded ) ? $decoded : array();
	}
	return is_array( $value ) ? $value : array();
}

/**
 * Map the plugin's navigation option (ah_cms_navigation) into the shape the
 * main_header component expects: array of { label, url, children[ {label,url} ] }.
 * A plugin item of type 'dropdown' with submenu links becomes a `children` array.
 */
function adn_chrome_plugin_nav() {
	$items = adn_chrome_option( 'ah_cms_navigation' );
	if ( empty( $items ) ) {
		return array();
	}

	$nav = array();
	foreach ( $items as $item ) {
		$item = (array) $item;

		// Respect the "Show this menu item" toggle (absent = visible).
		if ( isset( $item['visible'] ) && ! $item['visible'] ) {
			continue;
		}
		$label = isset( $item['label'] ) ? (string) $item['label'] : '';
		if ( '' === $label ) {
			continue;
		}

		$url  = isset( $item['url'] ) ? (string) $item['url'] : '';
		$type = isset( $item['type'] ) ? (string) $item['type'] : 'link';

		$children = array();
		foreach ( (array) ( isset( $item['submenu'] ) ? $item['submenu'] : array() ) as $sub ) {
			$sub        = (array) $sub;
			$sub_label  = isset( $sub['label'] ) ? (string) $sub['label'] : '';
			$sub_url    = isset( $sub['url'] ) ? (string) $sub['url'] : '';
			if ( '' === $sub_label || '' === $sub_url ) {
				continue;
			}
			$children[] = array(
					'label'      => $sub_label,
					'url'        => $sub_url,
					'highlight'  => ! empty( $sub['highlight'] ),
					'css_class'  => isset( $sub['css_class'] ) ? (string) $sub['css_class'] : '',
				);
		}

		$node = array(
			'label'       => $label,
			'url'         => '' !== $url ? $url : '#',
			'description' => isset( $item['description'] )  ? (string) $item['description']  : '',
			'icon'        => isset( $item['icon'] )         ? (string) $item['icon']         : '',
			'panel_image' => isset( $item['panel_image'] )  ? (string) $item['panel_image']  : '',
			'css_class'   => isset( $item['css_class'] )    ? (string) $item['css_class']    : '',
		);
		if ( 'dropdown' === $type && ! empty( $children ) ) {
			$node['children'] = $children;
		}
		$nav[] = $node;
	}

	return $nav;
}

/**
 * Map the plugin's header CTA option (ah_cms_nav_cta) into { label, url }.
 */
function adn_chrome_plugin_cta() {
	$cta = adn_chrome_option( 'ah_cms_nav_cta' );
	if ( empty( $cta ) || empty( $cta['label'] ) ) {
		return array();
	}
	return array(
		'label' => (string) $cta['label'],
		'url'   => isset( $cta['url'] ) ? (string) $cta['url'] : '#',
	);
}

/**
 * Overlay the plugin's footer option (ah_cms_footer) onto the JSON footer.
 * Plugin-managed: brand description, columns, legal links (→ bottom_links).
 * JSON-managed (kept): brand name/icon/sub, social icons, copyright,
 * made_with line and disclaimer.
 *
 * @param array $json_footer The footer block from site_chrome.json.
 * @return array Merged footer, or empty array to keep the JSON footer as-is.
 */
function adn_chrome_plugin_footer( $json_footer ) {
	$raw = adn_chrome_option( 'ah_cms_footer' );
	if ( empty( $raw ) ) {
		return array();
	}

	$footer = is_array( $json_footer ) ? $json_footer : array();

	if ( ! empty( $raw['brand_description'] ) ) {
		$footer['brand'] = isset( $footer['brand'] ) ? (array) $footer['brand'] : array();
		$footer['brand']['description'] = (string) $raw['brand_description'];
	}

	if ( ! empty( $raw['badge_text'] ) ) {
		$footer['badge_text'] = (string) $raw['badge_text'];
	}

	if ( ! empty( $raw['cta'] ) && ! empty( $raw['cta']['label'] ) ) {
		$footer['cta'] = array(
			'label' => (string) $raw['cta']['label'],
			'url'   => isset( $raw['cta']['url'] ) ? (string) $raw['cta']['url'] : '#',
		);
	}

	$columns = array();
	foreach ( (array) ( isset( $raw['columns'] ) ? $raw['columns'] : array() ) as $column ) {
		$column = (array) $column;
		$links  = array();
		foreach ( (array) ( isset( $column['items'] ) ? $column['items'] : array() ) as $link ) {
			$link  = (array) $link;
			$label = isset( $link['label'] ) ? (string) $link['label'] : '';
			if ( '' === $label ) {
				continue;
			}
			$links[] = array(
				'label'     => $label,
				'url'       => isset( $link['url'] ) ? (string) $link['url'] : '#',
				'highlight' => ! empty( $link['highlight'] ),
			);
		}
		if ( ! empty( $links ) ) {
			$columns[] = array(
				'title' => isset( $column['title'] ) ? (string) $column['title'] : '',
				'links' => $links,
			);
		}
	}
	if ( ! empty( $columns ) ) {
		$footer['columns'] = $columns;
	}

	$bottom_links = array();
	foreach ( (array) ( isset( $raw['legal_links'] ) ? $raw['legal_links'] : array() ) as $link ) {
		$link  = (array) $link;
		$label = isset( $link['label'] ) ? (string) $link['label'] : '';
		if ( '' === $label ) {
			continue;
		}
		$bottom_links[] = array(
			'label' => $label,
			'url'   => isset( $link['url'] ) ? (string) $link['url'] : '#',
		);
	}
	if ( ! empty( $bottom_links ) ) {
		$footer['bottom_links'] = $bottom_links;
	}

	return $footer;
}

/**
 * Category page content - one call returns all sections for a given slug.
 * The slug maps to data/json/{slug}.json (e.g. 'buying' → data/json/buying.json).
 * When the plugin is ready, swap the internals here; callers are unchanged.
 *
 * @param string $slug  Sanitized page slug (buying, selling, house-movers…).
 */
function adn_service_category_data( $slug ) {
	$slug = sanitize_key( (string) $slug );
	return class_exists( 'ADN_Real_Loader' ) ? ADN_Real_Loader::json( $slug ) : array();
}

/**
 * Individual guide article content - loads data/json/guide-{slug}.json.
 * The "guide-" prefix avoids collisions with category slugs of the same name.
 * When the plugin is ready, swap internals here; callers are unchanged.
 *
 * @param string $slug  Sanitized page slug (e.g. 'buying-step-by-step').
 */
function adn_service_guide_data( $slug ) {
	$slug = sanitize_key( (string) $slug );
	return class_exists( 'ADN_Real_Loader' ) ? ADN_Real_Loader::json( 'guide-' . $slug ) : array();
}

/**
 * Calculators page content - loads data/json/calculators.json.
 */
function adn_service_calculators_data() {
	return class_exists( 'ADN_Real_Loader' ) ? ADN_Real_Loader::json( 'calculators' ) : array();
}

/**
 * Guides listing page content - loads data/json/{slug}.json.
 * The slug maps to the WP page slug (e.g. 'buying-guides').
 * Reusable for any category's guide listing when a plugin replaces this.
 *
 * @param string $slug  Sanitized page slug (e.g. 'buying-guides').
 */
function adn_service_guides_listing_data( $slug ) {
	$slug = sanitize_key( (string) $slug );
	return class_exists( 'ADN_Real_Loader' ) ? ADN_Real_Loader::json( $slug ) : array();
}

/**
 * News & Insights page content - loads data/json/news.json.
 * When the plugin is ready, swap internals here; callers are unchanged.
 */
function adn_service_news_data() {
	return class_exists( 'ADN_Real_Loader' ) ? ADN_Real_Loader::json( 'news' ) : array();
}

/**
 * Single post sidebar data.
 * Calculators come from the live registry (adn_calculators() merged with AH_Calculator_DB
 * via the adn_calculators filter) + adn_calculators_meta option for per-calc URLs.
 * Newsletter copy falls back to post_sidebar.json so it can still be edited there.
 */
function adn_service_post_sidebar_data() {
	/* ── Calculators from DB/registry ── */
	$tools_raw = function_exists( 'adn_calculators' ) ? adn_calculators() : array();
	$meta_all  = get_option( 'adn_calculators_meta', array() );
	if ( ! is_array( $meta_all ) ) {
		$meta_all = array();
	}

	$view_all_url = home_url( SITE_CALCULATORS_URL );

	$items = array();
	foreach ( array_slice( $tools_raw, 0, 5, true ) as $key => $reg ) {
		$cmeta   = isset( $meta_all[ $key ] ) && is_array( $meta_all[ $key ] ) ? $meta_all[ $key ] : array();
		$items[] = array(
			'icon'  => isset( $reg['icon'] )  && '' !== $reg['icon']  ? (string) $reg['icon']  : '🧮',
			'label' => isset( $reg['title'] ) && '' !== $reg['title'] ? (string) $reg['title'] : (string) $key,
			'url'   => ! empty( $cmeta['card_url'] )
				? (string) $cmeta['card_url']
				: adn_calc_page_url( $key ),
		);
	}

	/* ── Newsletter copy from JSON fallback ── */
	$json        = class_exists( 'ADN_Real_Loader' ) ? ADN_Real_Loader::json( 'post_sidebar' ) : array();
	$newsletter  = isset( $json['newsletter'] ) && is_array( $json['newsletter'] ) ? $json['newsletter'] : array();

	return array(
		'calculators' => array(
			'view_all_url' => $view_all_url,
			'items'        => $items,
		),
		'newsletter' => $newsletter,
	);
}

/**
 * Read a single setting from the contact group in ah_site_settings.
 * Cached per page load; returns empty string if the key is absent or empty.
 */
function adn_get_contact_setting( string $key ): string {
	static $cache = null;
	if ( null === $cache ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_site_settings';
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table from $wpdb->prefix, WHERE is a static literal
		$rows  = $wpdb->get_results(
			"SELECT setting_key, setting_val FROM `{$table}` WHERE group_name = 'contact'",
			ARRAY_A
		);
		$cache = array();
		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$cache[ (string) $row['setting_key'] ] = (string) $row['setting_val'];
			}
		}
	}
	return $cache[ $key ] ?? '';
}

/**
 * Read a single setting from the social group in ah_site_settings.
 * Cached per page load; returns empty string if the key is absent or empty.
 */
function adn_get_social_setting( string $key ): string {
	static $cache = null;
	if ( null === $cache ) {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_site_settings';
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table from $wpdb->prefix, WHERE is a static literal
		$rows  = $wpdb->get_results(
			"SELECT setting_key, setting_val FROM `{$table}` WHERE group_name = 'social'",
			ARRAY_A
		);
		$cache = array();
		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$cache[ (string) $row['setting_key'] ] = (string) $row['setting_val'];
			}
		}
	}
	return $cache[ $key ] ?? '';
}

/**
 * Contact page content - JSON base overlaid with DB contact-group settings.
 * whatsapp, email, phone and address are editable in WP Admin → Settings → Contact.
 */
function adn_service_contact_data(): array {
	static $cache = null;
	if ( null !== $cache ) { return $cache; }

	$data = class_exists( 'ADN_Real_Loader' ) ? ADN_Real_Loader::json( 'contact' ) : array();
	$data = is_array( $data ) ? $data : array();

	$sidebar = isset( $data['contact_sidebar'] ) && is_array( $data['contact_sidebar'] )
		? $data['contact_sidebar'] : array();

	$db_whatsapp = adn_get_contact_setting( 'whatsapp_number' );
	if ( '' !== $db_whatsapp ) {
		if ( ! isset( $sidebar['whatsapp'] ) || ! is_array( $sidebar['whatsapp'] ) ) {
			$sidebar['whatsapp'] = array();
		}
		$sidebar['whatsapp']['number'] = $db_whatsapp;
		$wa_digits = preg_replace( '/[^0-9]/', '', $db_whatsapp );
		$sidebar['whatsapp']['url']    = 'https://wa.me/' . $wa_digits;
	}

	$db_email = adn_get_contact_setting( 'contact_email' );
	if ( '' !== $db_email ) {
		if ( ! isset( $sidebar['email'] ) || ! is_array( $sidebar['email'] ) ) {
			$sidebar['email'] = array();
		}
		$sidebar['email']['address'] = $db_email;
		$sidebar['email']['url']     = 'mailto:' . $db_email;
	}

	$db_phone = adn_get_contact_setting( 'contact_phone' );
	if ( '' !== $db_phone ) {
		if ( ! isset( $sidebar['phone'] ) || ! is_array( $sidebar['phone'] ) ) {
			$sidebar['phone'] = array();
		}
		$sidebar['phone']['number'] = $db_phone;
		$sidebar['phone']['url']    = 'tel:' . preg_replace( '/[^0-9+]/', '', $db_phone );
	}

	$db_address  = adn_get_contact_setting( 'address' );
	$db_maps_url = adn_get_contact_setting( 'google_maps_url' );
	if ( '' !== $db_address || '' !== $db_maps_url ) {
		$sidebar['address'] = array(
			'text'     => $db_address,
			'maps_url' => $db_maps_url,
		);
	}

	if ( ! empty( $sidebar ) ) {
		$data['contact_sidebar'] = $sidebar;
	}

	$cache = $data;
	return $cache;
}

/**
 * Get Expert Guidance page content - loads data/json/guidance.json.
 */
function adn_service_guidance_data() {
	return class_exists( 'ADN_Real_Loader' ) ? ADN_Real_Loader::json( 'guidance' ) : array();
}

/**
 * Ask an Expert directory content - loads data/json/ask-expert.json.
 */
function adn_service_ask_expert_data() {
	return class_exists( 'ADN_Real_Loader' ) ? ADN_Real_Loader::json( 'ask-expert' ) : array();
}

function adn_service_faqs_data() {
	// DB-only: return FAQs from the CMS plugin when available, otherwise empty.
	if ( class_exists( 'AH_Faqs_Model' ) ) {
		try {
			$model = new AH_Faqs_Model();
			$rows  = $model->get_global();
			return is_array( $rows ) ? $rows : array();
		} catch ( Throwable $e ) {
			return array();
		}
	}
	return array();
}

/**
 * Parse marquee admin settings into args for point_marque.php.
 *
 * @param  array $settings  Array that contains marquee_enabled, marquee_mode, marquee_items keys.
 * @return array|null       Ready-to-pass args { trust, is_string, is_icon }, or null when disabled/empty.
 */
function adn_parse_marquee_settings( $settings ) {
	if ( empty( $settings['marquee_enabled'] ) || empty( $settings['marquee_items'] ) ) {
		return null;
	}
	$mode  = ( isset( $settings['marquee_mode'] ) && 'icon' === $settings['marquee_mode'] ) ? 'icon' : 'string';
	$lines = array_values( array_filter( array_map( 'trim', explode( "\n", (string) $settings['marquee_items'] ) ) ) );
	if ( empty( $lines ) ) {
		return null;
	}
	$trust = array();
	if ( 'string' === $mode ) {
		$trust = $lines;
	} else {
		foreach ( $lines as $line ) {
			$parts   = array_pad( explode( '|', $line, 3 ), 3, '' );
			$trust[] = array(
				'icon'  => trim( $parts[0] ),
				'label' => trim( $parts[1] ),
				'note'  => trim( $parts[2] ),
			);
		}
	}
	return array(
		'trust'     => $trust,
		'is_string' => 'string' === $mode,
		'is_icon'   => 'icon' === $mode,
	);
}

/**
 * Resolve a JSON-stored link for output:
 *  - ""            → "#"
 *  - "#..."        → unchanged (placeholder anchors)
 *  - "http(s)"     → unchanged (external)
 *  - "mailto:"     → unchanged (email)
 *  - "tel:"        → unchanged (phone)
 *  - "callto:"     → unchanged (phone)
 *  - "/path/"      → home_url( "/path/" )
 */
function adn_link( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return '#';
	}
	if ( '#' === $url[0] || preg_match( '#^(https?:)?//#i', $url ) ) {
		return $url;
	}
	if ( preg_match( '#^(mailto|tel|sms|callto):#i', $url ) ) {
		return $url;
	}
	if ( '/' === $url[0] ) {
		return home_url( $url );
	}
	return home_url( '/' . ltrim( $url, '/' ) );
}

