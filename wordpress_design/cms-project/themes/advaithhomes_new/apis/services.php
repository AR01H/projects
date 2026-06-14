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
 * Data sources, in order of precedence:
 *   1. The CMS plugin (the SAME options it already serves to other client
 *      sites): ah_cms_navigation, ah_cms_nav_cta, ah_cms_footer. The plugin's
 *      Navigation Editor saves these and notes "themes can render this data
 *      with their own markup" - this is that render side.
 *   2. data/json/site_chrome.json - defaults for everything the plugin does
 *      not manage (logo, social icons, copyright, disclaimer, search copy).
 *
 * If the plugin is inactive or a section is empty, the JSON default is used,
 * so the header/footer always render.
 */
function adn_service_site_chrome() {
	$chrome = ADN_Real_Loader::json( 'site_chrome' );
	$chrome = is_array( $chrome ) ? $chrome : array();

	// Navigation (with dropdown submenus) from the plugin, if present.
	$plugin_nav = adn_chrome_plugin_nav();
	if ( ! empty( $plugin_nav ) ) {
		$chrome['nav'] = $plugin_nav;
	}

	// Header CTA from the plugin, if present.
	$plugin_cta = adn_chrome_plugin_cta();
	if ( ! empty( $plugin_cta ) ) {
		$chrome['header_cta'] = $plugin_cta;
	}

	// Footer (brand copy, columns, legal links) from the plugin, overlaid on
	// the JSON footer so logo/social/copyright/disclaimer keep their defaults.
	$json_footer = isset( $chrome['footer'] ) && is_array( $chrome['footer'] ) ? $chrome['footer'] : array();
	$plugin_footer = adn_chrome_plugin_footer( $json_footer );
	if ( ! empty( $plugin_footer ) ) {
		$chrome['footer'] = $plugin_footer;
	}

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
			$children[] = array( 'label' => $sub_label, 'url' => $sub_url );
		}

		$node = array(
			'label' => $label,
			'url'   => '' !== $url ? $url : '#',
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
				'label' => $label,
				'url'   => isset( $link['url'] ) ? (string) $link['url'] : '#',
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
	$calcs_raw = function_exists( 'adn_calculators' ) ? adn_calculators() : array();
	$meta_all  = get_option( 'adn_calculators_meta', array() );
	if ( ! is_array( $meta_all ) ) {
		$meta_all = array();
	}

	$view_all_url = home_url( '/calculators/' );

	$items = array();
	foreach ( array_slice( $calcs_raw, 0, 5, true ) as $key => $reg ) {
		$cmeta   = isset( $meta_all[ $key ] ) && is_array( $meta_all[ $key ] ) ? $meta_all[ $key ] : array();
		$items[] = array(
			'icon'  => isset( $reg['icon'] )  && '' !== $reg['icon']  ? (string) $reg['icon']  : '🧮',
			'label' => isset( $reg['title'] ) && '' !== $reg['title'] ? (string) $reg['title'] : (string) $key,
			'url'   => ! empty( $cmeta['card_url'] )
				? (string) $cmeta['card_url']
				: home_url( '/?ah_calc_page=' . rawurlencode( $key ) ),
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
 * Contact page content - loads data/json/contact.json.
 */
function adn_service_contact_data() {
	return class_exists( 'ADN_Real_Loader' ) ? ADN_Real_Loader::json( 'contact' ) : array();
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

/**
 * Resolve a JSON-stored link for output:
 *  - ""        → "#"
 *  - "#..."    → unchanged (placeholder anchors)
 *  - "http(s)" → unchanged (external)
 *  - "/path/"  → home_url( "/path/" )
 */
function adn_link( $url ) {
	$url = (string) $url;
	if ( '' === $url ) {
		return '#';
	}
	if ( '#' === $url[0] || preg_match( '#^https?://#i', $url ) ) {
		return $url;
	}
	return home_url( $url );
}
