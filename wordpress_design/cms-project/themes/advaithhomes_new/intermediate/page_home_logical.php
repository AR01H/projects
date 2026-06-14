<?php
/**
 * intermediate/page_home_logical.php - Home page container logic.
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

	// Marquee: admin-configured trust items override the JSON default.
	$_hs = get_option( 'adn_home_sections', array() );
	$_mq = function_exists( 'adn_parse_marquee_settings' ) ? adn_parse_marquee_settings( $_hs ) : null;
	if ( $_mq ) {
		$ctx['hero']['trust_items'] = $_mq['trust'];
	}

	// Overlay live CMS content where it exists; JSON stays as the fallback.
	if ( function_exists( 'adn_cms_available' ) && adn_cms_available() ) {
		$journey_cards = adn_home_cms_journey_cards();
		if ( ! empty( $journey_cards ) ) {
			$ctx['journey']['cards'] = array_merge($journey_cards,$ctx['journey']['cards']);
		}

		$guide_items = adn_home_cms_guide_items();
		$ctx['guides']['items'] = $guide_items; // DB only; empty array when no data - no JSON fallback

		$ctx['news']['items'] = adn_home_cms_news_items(); // DB only; empty array when no news data
	}

	// Overlay admin-selected posts for regulations and hot_topics (no CMS plugin required).
	$reg_items = adn_home_cms_regulations_items();
	if ( ! empty( $reg_items ) ) {
		$ctx['regulations']['items'] = $reg_items;
	}
	$ht_items = adn_home_cms_hot_topics_items();
	if ( ! empty( $ht_items ) ) {
		$ctx['hot_topics']['items'] = $ht_items;
	}

	// Overlay popular calculators from registry (is_popular flag) - always replaces JSON if any exist.
	if ( function_exists( 'adn_calculators' ) ) {
		$_hp_registry = adn_calculators();
		$_hp_meta_all = get_option( 'adn_calculators_meta', array() );
		$_hp_items    = array();
		foreach ( $_hp_registry as $_hpk => $_hpc ) {
			$_hpm = ( isset( $_hp_meta_all[ $_hpk ] ) && is_array( $_hp_meta_all[ $_hpk ] ) ) ? $_hp_meta_all[ $_hpk ] : array();
			if ( array_key_exists( 'enabled', $_hpm ) && empty( $_hpm['enabled'] ) ) { continue; }
			if ( ! empty( $_hpm['hidden_from_listing'] ) ) { continue; }
			if ( empty( $_hpm['is_popular'] ) ) { continue; }
			$_hpthumb = '';
			if ( ! empty( $_hpm['thumbnail_id'] ) ) {
				$_hpt = wp_get_attachment_image_url( (int) $_hpm['thumbnail_id'], 'thumbnail' );
				$_hpthumb = $_hpt ? (string) $_hpt : '';
			}
			$_hp_items[] = array(
				'icon'      => ! empty( $_hpc['icon'] )      ? (string) $_hpc['icon']      : '🧮',
				'name'      => ! empty( $_hpm['label'] )     ? (string) $_hpm['label']     : ( ! empty( $_hpc['title'] ) ? (string) $_hpc['title'] : $_hpk ),
				'url'       => ! empty( $_hpm['card_url'] )  ? (string) $_hpm['card_url']  : home_url( '/?ah_calc_page=' . rawurlencode( $_hpk ) ),
				'thumbnail' => $_hpthumb,
				'highlight' => ! empty( $_hpm['highlight'] ) ? (string) $_hpm['highlight'] : '',
			);
		}
		if ( ! empty( $_hp_items ) ) {
			$ctx['calculators']['items'] = $_hp_items;
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
 * { image, icon, gradient, title, description, link_label, url }.
 */
function adn_home_cms_journey_cards() {
	$cards = array();
	foreach ( adn_cms_guide_parents( ) as $i => $term ) {
		$name = isset( $term->name ) ? $term->name : '';
		if ( '' === $name ) {
			continue;
		}
		// Use the term's uploaded image when available; else card falls back to gradient+icon.
		$image_id  = ! empty( $term->image_id ) ? (int) $term->image_id : 0;
		$image_url = $image_id ? ( wp_get_attachment_image_url( $image_id, 'medium' ) ?: '' ) : '';

		$cards[] = array(
			'image'       => $image_url,
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
 * Guide cards for the home page - one card per taxonomy category term.
 * Each card links to the term's category listing page (/term-slug/),
 * not to an individual article.
 * Respects adn_home_featured option (topic filter + count).
 * DB only - no JSON fallback; returns empty array when no data.
 */
function adn_home_cms_guide_items() {
	$featured  = get_option( 'adn_home_featured', array() );
	$count     = ( isset( $featured['count'] ) && (int) $featured['count'] > 0 )
	             ? (int) $featured['count'] : 10;
	$topic_ids = ( isset( $featured['topics'] ) && is_array( $featured['topics'] ) )
	             ? array_map( 'intval', $featured['topics'] ) : array();

	$items = array();
	foreach ( adn_cms_guides_by_category( $count, $topic_ids ) as $i => $post ) {
		$cat_name = isset( $post->category_name ) ? (string) $post->category_name : '';
		if ( '' === $cat_name ) {
			continue;
		}

		// Card URL goes to the category listing page for this term.
		$term_url = home_url( '/' . trim( (string) $post->_term_slug, '/' ) . '/' );

		$items[] = array(
			'icon'        => ! empty( $post->term_icon ) ? $post->term_icon : ( ! empty( $post->parent_icon ) ? $post->parent_icon : '📚' ),
			'gradient'    => adn_cms_gradient( $i ),
			'parent_name' => ! empty( $post->parent_name ) ? $post->parent_name : '',
			'category'    => $cat_name,
			'title'       => '',
			'description' => ! empty( $post->_term_desc ) ? $post->_term_desc : '',
			'read_more'   => 'Explore →',
			'url'         => $term_url,
		);
	}
	return $items;
}

/**
 * News items for home "Latest News".
 * Primary: plugin News Bar (ah_news_bar_items - active, date-filtered).
 * Fallback: 4 most recent WP posts via WP_Query.
 * Each item carries a 'description' key for the accordion expand.
 */
function adn_home_cms_news_items() {
	$items = array();

	// Source 1: News Bar
	if ( function_exists( 'adn_cms_newsbar_items' ) ) {
		foreach ( adn_cms_newsbar_items( 4 ) as $i => $item ) {
			$title = isset( $item->text ) ? $item->text : '';
			if ( '' === $title ) {
				continue;
			}
			$stamp   = ! empty( $item->start_date ) ? $item->start_date : '';
			$desc    = ! empty( $item->content ) ? wp_strip_all_tags( (string) $item->content ) : '';
			$items[] = array(
				'title'       => $title,
				'description' => $desc,
				'date'        => $stamp ? date_i18n( 'M j, Y', strtotime( $stamp ) ) : '',
				'tag'         => 'NEWS',
				'gradient'    => adn_cms_gradient( $i ),
				'url'         => ! empty( $item->link_url ) ? $item->link_url : '#',
			);
		}
	}

	// Source 2: WP_Query fallback - plain WP posts, no taxonomy required
	if ( empty( $items ) ) {
		$q = new WP_Query( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 4,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		if ( $q->have_posts() ) {
			foreach ( $q->posts as $i => $post ) {
				$excerpt = $post->post_excerpt
					?: wp_trim_words( wp_strip_all_tags( $post->post_content ), 25, '…' );
				$items[] = array(
					'title'       => $post->post_title,
					'description' => $excerpt,
					'date'        => get_the_date( 'M j, Y', $post ),
					'tag'         => 'NEWS',
					'gradient'    => adn_cms_gradient( $i ),
					'url'         => get_permalink( $post ),
				);
			}
			wp_reset_postdata();
		}
	}

	return $items;
}

/**
 * Regulations items from admin-selected posts (adn_home_newsblocks option).
 * Falls back to empty (JSON defaults stay in place).
 *
 * @return array[]  regulation_item shape: { badge_lines[], title, date, url }
 */
function adn_home_cms_regulations_items() {
	$opt = get_option( 'adn_home_newsblocks', array() );
	$raw = ( isset( $opt['regulations']['items'] ) && is_array( $opt['regulations']['items'] ) )
	       ? $opt['regulations']['items'] : array();
	$items = array();
	foreach ( $raw as $row ) {
		$pid  = (int) ( isset( $row['post_id'] ) ? $row['post_id'] : 0 );
		if ( ! $pid ) {
			continue;
		}
		$post = get_post( $pid );
		if ( ! $post || 'publish' !== $post->post_status ) {
			continue;
		}
		$badge_raw   = isset( $row['badge'] ) ? sanitize_text_field( $row['badge'] ) : 'GOV UK';
		$badge_lines = array_filter( array_map( 'trim', explode( "\n", $badge_raw ) ) );
		if ( empty( $badge_lines ) ) {
			$badge_lines = array( 'GOV', 'UK' );
		}
		$items[] = array(
			'badge_lines' => array_values( $badge_lines ),
			'title'       => $post->post_title,
			'date'        => get_the_date( 'M j, Y', $post ),
			'url'         => get_permalink( $post ),
		);
	}
	return $items;
}

/**
 * Hot Topics items from admin-selected posts (adn_home_newsblocks option).
 * Falls back to empty (JSON defaults stay in place).
 *
 * @return array[]  hot_topic_item shape: { icon, text, desc, url }
 */
function adn_home_cms_hot_topics_items() {
	$opt = get_option( 'adn_home_newsblocks', array() );
	$raw = ( isset( $opt['hot_topics']['items'] ) && is_array( $opt['hot_topics']['items'] ) )
	       ? $opt['hot_topics']['items'] : array();
	$items = array();
	foreach ( $raw as $row ) {
		$pid  = (int) ( isset( $row['post_id'] ) ? $row['post_id'] : 0 );
		if ( ! $pid ) {
			continue;
		}
		$post = get_post( $pid );
		if ( ! $post || 'publish' !== $post->post_status ) {
			continue;
		}
		$items[] = array(
			'icon' => ! empty( $row['icon'] ) ? sanitize_text_field( $row['icon'] ) : '🔥',
			'text' => $post->post_title,
			'desc' => $post->post_excerpt,
			'url'  => get_permalink( $post ),
		);
	}
	return $items;
}
