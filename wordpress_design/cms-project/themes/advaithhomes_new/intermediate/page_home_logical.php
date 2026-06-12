<?php
/**
 * intermediate/page_home_logical.php — Home page container logic.
 *
 * RULE: This layer fetches via the services (apis/services.php +
 *       apis/services_cms.php), applies defaults so the template never crashes
 *       on missing keys, and hands page-home.php one ready-to-render $ctx array.
 *       No markup here; no direct data-source reads in the template.
 *
 * Data sources:
 *   - hero / calculators / newsletter / regulations / hot_topics → home_page.json
 *     (calculators stay hardcoded in JSON by design).
 *   - journey cards  ← CMS Guide parent terms (Buying/Selling/Moving)
 *   - guides items   ← CMS articles (ah_posts, type article/guide) by hierarchy
 *   - news items     ← CMS latest news (independent of the Guide tree)
 *   When the CMS plugin/tables/data are absent each of these keeps its JSON
 *   default, so the page always renders.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build the full render context for the home page.
 *
 * @return array  See keys assembled below.
 */
function adn_home_get_context() {
	$data   = adn_service_home_data();
	$chrome = adn_service_site_chrome();

	$section = static function ( $key, $defaults = array() ) use ( $data ) {
		$value = isset( $data[ $key ] ) && is_array( $data[ $key ] ) ? $data[ $key ] : array();
		return array_merge( $defaults, $value );
	};

	$ctx = array(
		'chrome'      => is_array( $chrome ) ? $chrome : array(),
		'hero'        => $section( 'hero', array( 'title_lines' => array(), 'actions' => array(), 'trust_items' => array(), 'diagram' => array() ) ),
		'journey'     => $section( 'journey', array( 'heading' => array(), 'cards' => array() ) ),
		'news'        => $section( 'news', array( 'heading' => array(), 'items' => array() ) ),
		'regulations' => $section( 'regulations', array( 'heading' => array(), 'items' => array() ) ),
		'hot_topics'  => $section( 'hot_topics', array( 'title' => '', 'items' => array(), 'cta' => array() ) ),
		'calculators' => $section( 'calculators', array( 'heading' => array(), 'items' => array() ) ),
		'guides'      => $section( 'guides', array( 'heading' => array(), 'items' => array() ) ),
		'newsletter'  => $section( 'newsletter' ),
	);

	// Admin overrides (Theme → Home Page → Hero & Intro).
	$hero_opt = get_option( 'adn_home_hero' );
	if ( is_array( $hero_opt ) ) {
		$ctx['hero'] = adn_home_apply_hero_overrides( $ctx['hero'], $hero_opt );
	}

	// Overlay live CMS content where it exists; JSON stays as the fallback.
	if ( function_exists( 'adn_cms_available' ) && adn_cms_available() ) {
		$journey_cards = adn_home_cms_journey_cards();
		if ( ! empty( $journey_cards ) ) {
			$ctx['journey']['cards'] = $journey_cards;
		}

		// Featured topics + count come from Theme → Home Page → Featured Guides.
		$featured  = get_option( 'adn_home_featured' );
		$count     = ( is_array( $featured ) && ! empty( $featured['count'] ) ) ? (int) $featured['count'] : 5;
		$topic_ids = ( is_array( $featured ) && ! empty( $featured['topics'] ) ) ? array_map( 'intval', (array) $featured['topics'] ) : array();

		$guide_items = adn_home_cms_guide_items( $count, $topic_ids );
		if ( ! empty( $guide_items ) ) {
			$ctx['guides']['items'] = $guide_items;
		}

		$news_items = adn_home_cms_news_items();
		if ( ! empty( $news_items ) ) {
			$ctx['news']['items'] = $news_items;
		}
	}

	return $ctx;
}

/**
 * Whether a home-page section should render.
 * Controlled by Theme → Home Page → Sections; defaults to visible.
 */
function adn_home_section_visible( $key ) {
	$sections = get_option( 'adn_home_sections' );
	if ( ! is_array( $sections ) ) {
		return true;
	}
	return ! array_key_exists( $key, $sections ) || ! empty( $sections[ $key ] );
}

/**
 * Overlay admin hero settings onto the JSON hero. Only provided fields win.
 */
function adn_home_apply_hero_overrides( $hero, $opt ) {
	$lines = array();
	if ( ! empty( $opt['heading_1'] ) ) {
		$lines[] = array( 'text' => $opt['heading_1'], 'accent' => false );
	}
	if ( ! empty( $opt['heading_accent'] ) ) {
		$lines[] = array( 'text' => $opt['heading_accent'], 'accent' => true );
	}
	if ( ! empty( $opt['heading_3'] ) ) {
		$lines[] = array( 'text' => $opt['heading_3'], 'accent' => false );
	}
	if ( ! empty( $lines ) ) {
		$hero['title_lines'] = $lines;
	}
	if ( ! empty( $opt['description'] ) ) {
		$hero['description'] = $opt['description'];
	}

	$actions = array();
	if ( ! empty( $opt['cta1_label'] ) ) {
		$actions[] = array( 'label' => $opt['cta1_label'], 'url' => isset( $opt['cta1_url'] ) ? $opt['cta1_url'] : '#', 'style' => 'primary' );
	}
	if ( ! empty( $opt['cta2_label'] ) ) {
		$actions[] = array( 'label' => $opt['cta2_label'], 'url' => isset( $opt['cta2_url'] ) ? $opt['cta2_url'] : '#', 'style' => 'outline' );
	}
	if ( ! empty( $actions ) ) {
		$hero['actions'] = $actions;
	}

	return $hero;
}

/**
 * Map CMS Guide parent terms → journey-card props
 * { icon, gradient, title, description, link_label, url }.
 */
function adn_home_cms_journey_cards() {
	$cards = array();
	foreach ( adn_cms_guide_parents( 4 ) as $i => $term ) {
		$name = isset( $term->name ) ? $term->name : '';
		if ( '' === $name ) {
			continue;
		}
		$cards[] = array(
			'icon'        => ! empty( $term->icon_emoji ) ? $term->icon_emoji : '🏡',
			'gradient'    => adn_cms_gradient( $i ),
			'title'       => $name,
			'description' => isset( $term->description ) ? (string) $term->description : '',
			'link_label'  => 'Explore ' . $name . ' →',
			'url'         => adn_cms_term_url( $term ),
		);
	}
	return $cards;
}

/**
 * Map CMS articles → guide-card props
 * { icon, gradient, category, title, description, read_more, url }.
 *
 * @param int   $count        How many articles.
 * @param int[] $taxonomy_ids Restrict to these topic terms (empty = any).
 */
function adn_home_cms_guide_items( $count = 5, $taxonomy_ids = array() ) {
	$items = array();
	foreach ( adn_cms_articles( $count, $taxonomy_ids ) as $i => $post ) {
		$title = isset( $post->title ) ? $post->title : '';
		if ( '' === $title ) {
			continue;
		}
		$items[] = array(
			'icon'        => '📄',
			'gradient'    => adn_cms_gradient( $i ),
			'category'    => ! empty( $post->category_name ) ? $post->category_name : 'Guide',
			'title'       => $title,
			'description' => isset( $post->excerpt ) ? (string) $post->excerpt : '',
			'read_more'   => 'Read More →',
			'url'         => adn_cms_post_url( $post ),
		);
	}
	return $items;
}

/**
 * Map CMS latest-news posts → news-item props { title, date, tag, gradient, url }.
 */
function adn_home_cms_news_items() {
	$items = array();
	foreach ( adn_cms_latest_news( 4 ) as $i => $post ) {
		$title = isset( $post->title ) ? $post->title : '';
		if ( '' === $title ) {
			continue;
		}
		$stamp = ! empty( $post->published_at ) ? $post->published_at : ( $post->created_at ?? '' );
		$items[] = array(
			'title'    => $title,
			'date'     => $stamp ? date_i18n( 'M j, Y', strtotime( $stamp ) ) : '',
			'tag'      => 'NEWS',
			'gradient' => adn_cms_gradient( $i ),
			'url'      => adn_cms_post_url( $post ),
		);
	}
	return $items;
}
