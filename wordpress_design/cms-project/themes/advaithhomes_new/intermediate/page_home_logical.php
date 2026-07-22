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
 * @param array $skip  Section keys whose EXPENSIVE data fetches should be
 *                     skipped (banners|news|guides|tools). Used by the
 *                     deferred-fragment flow: the first paint skips these,
 *                     then /api/v1/fragment/home/{section} builds them on
 *                     demand. Default array() = full build (REST /home and
 *                     any existing callers are unchanged).
 *
 * @return array  See keys assembled below.
 */
function adn_home_get_context( $skip = array() ) {
	$skip   = is_array( $skip ) ? $skip : array();
	$cache_key = 'home_context_' . md5( wp_json_encode( $skip ) );
	if ( class_exists( 'ADN_Cache' ) ) {
		$cached = ADN_Cache::get( $cache_key, 'pages' );
		if ( false !== $cached ) {
			return $cached;
		}
	}

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
		'banners'     => array(
			'heading' => $section( 'banners', array( 'heading' => array() ) )['heading'],
			'items'   => ( ! in_array( 'banners', $skip, true ) && class_exists( 'AH_Banners_Helper' ) )
				? AH_Banners_Helper::get_all( true )
				: array(),
		),
		'news'        => $section( 'news', array( 'heading' => array(), 'items' => array() ) ),
		'regulations' => $section( 'regulations', array( 'heading' => array(), 'items' => array() ) ),
		'hot_topics'  => $section( 'hot_topics', array( 'title' => '', 'items' => array(), 'cta' => array() ) ),
		'tools' => $section( 'tools', array( 'heading' => array(), 'items' => array() ) ),
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

	// Home banner: admin-configured image/GIF/video overrides the default,
	// with an optional separate asset for mobile screens.
	if ( ! empty( $_hs['home_banner'] ) && function_exists( 'adn_settings_media_url_type' ) ) {
		$_desktop_media = adn_settings_media_url_type( $_hs['home_banner'] );
		if ( '' !== $_desktop_media['url'] ) {
			$ctx['hero']['image'] = $_desktop_media['url']; // back-compat for anything still reading ['image']
			$ctx['hero']['media'] = $_desktop_media;

			$ctx['hero']['media_mobile'] = null;
			if ( ! empty( $_hs['home_banner_mobile'] ) ) {
				$_mobile_media = adn_settings_media_url_type( $_hs['home_banner_mobile'] );
				if ( '' !== $_mobile_media['url'] ) {
					$ctx['hero']['media_mobile'] = $_mobile_media;
				}
			}
		}
	}

	// Overlay live CMS content where it exists; JSON stays as the fallback.
	if ( function_exists( 'adn_cms_available' ) && adn_cms_available() ) {
		$journey_cards = adn_home_cms_journey_cards();
		if ( ! empty( $journey_cards ) ) {
			$ctx['journey']['cards'] = array_merge( $journey_cards, $ctx['journey']['cards'] );
		}

		if ( ! in_array( 'guides', $skip, true ) ) {
			$guide_items = adn_home_cms_guide_items();
			$ctx['guides']['items'] = $guide_items; // DB only; empty array when no data - no JSON fallback
		}

		if ( ! in_array( 'news', $skip, true ) ) {
			$ctx['news']['items'] = adn_home_cms_news_items(); // DB only; empty array when no news data
		}
	}

	// Apply theme-level image overrides to JSON/static journey cards (keyed by URL slug).
	$_jni = get_option( 'adn_journey_json_images', array() );
	if ( ! empty( $_jni ) && is_array( $_jni ) ) {
		foreach ( $ctx['journey']['cards'] as &$_jcard ) {
			$_jcard_title = isset( $_jcard['title'] ) ? (string) $_jcard['title'] : '';
			$_jcard_url   = isset( $_jcard['url'] ) ? (string) $_jcard['url'] : '';
			if ( '' === $_jcard_title ) {
				continue;
			}
			$_jkey = sanitize_key( sanitize_title( $_jcard_title ) );
			$_old_jkey = sanitize_key( sanitize_title( trim( $_jcard_url, '/' ) ) );

			$_img_id = 0;
			if ( ! empty( $_jni[ $_jkey ] ) ) {
				$_img_id = (int) $_jni[ $_jkey ];
			} elseif ( '' !== $_old_jkey && ! empty( $_jni[ $_old_jkey ] ) ) {
				$_img_id = (int) $_jni[ $_old_jkey ];
			}

			if ( $_img_id > 0 ) {
				$_jimg = wp_get_attachment_image_url( $_img_id, 'large' );
				if ( $_jimg ) {
					$_jcard['image'] = $_jimg;
				}
			}
		}
		unset( $_jcard );
	}

	// Overlay admin-selected posts for regulations and hot_topics (no CMS plugin required).
	// Both belong to the news row, so they follow the 'news' skip flag.
	if ( ! in_array( 'news', $skip, true ) ) {
		$reg_items = adn_home_cms_regulations_items();
		if ( ! empty( $reg_items ) ) {
			$ctx['regulations']['items'] = $reg_items;
		}
		$ht_items = adn_home_cms_hot_topics_items();
		if ( ! empty( $ht_items ) ) {
			$ctx['hot_topics']['items'] = $ht_items;
		}
	}

	// Overlay popular calculators from registry (is_popular flag) - always replaces JSON if any exist.
	if ( ! in_array( 'tools', $skip, true ) && function_exists( 'adn_calculators' ) ) {
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
				'icon'      => ! empty( $_hpc['icon'] )      ? (string) $_hpc['icon']      : adn_term( 'icons.tools', '🧮' ),
				'name'      =>  $_hpc['title']?? '',
				'url'       => ! empty( $_hpm['card_url'] )  ? (string) $_hpm['card_url']  : home_url( '/?ah_calc_page=' . rawurlencode( $_hpk ) ),
				'thumbnail' => $_hpthumb,
				'highlight' => ! empty( $_hpm['highlight'] ) ? (string) $_hpm['highlight'] : '',
				'desc' =>  $_hpm['desc']?? ''
			);
		}
		if ( ! empty( $_hp_items ) ) {
			$ctx['tools']['items'] = $_hp_items;
		}
	}

	if ( class_exists( 'ADN_Cache' ) ) {
		ADN_Cache::set( $cache_key, $ctx, 'pages', get_option( 'ah_cache_expiry', 3600 ) );
	}
	return $ctx;
}

/**
 * Build only the data needed for one deferred home fragment.
 * Keeps REST fragment requests narrow so they do not pay for unrelated queries.
 */
function adn_home_get_fragment_context( $section ) {
	static $cache = array();

	$section = sanitize_key( (string) $section );
	if ( isset( $cache[ $section ] ) ) {
		return $cache[ $section ];
	}

	$data = adn_service_home_data();
	$home = static function ( $key, $defaults = array() ) use ( $data ) {
		$value = isset( $data[ $key ] ) && is_array( $data[ $key ] ) ? $data[ $key ] : array();
		return array_merge( $defaults, $value );
	};

	$ctx = array();

	switch ( $section ) {
		case 'banners':
			$ctx['banners'] = $home( 'banners', array( 'heading' => array(), 'items' => array() ) );
			$ctx['banners']['items'] = ( class_exists( 'AH_Banners_Helper' ) )
				? AH_Banners_Helper::get_all( true )
				: array();
			break;

		case 'news_row':
			$ctx['news']        = $home( 'news', array( 'heading' => array(), 'items' => array() ) );
			$ctx['regulations'] = $home( 'regulations', array( 'heading' => array(), 'items' => array() ) );
			$ctx['hot_topics']  = $home( 'hot_topics', array( 'title' => '', 'items' => array(), 'cta' => array() ) );

			if ( function_exists( 'adn_cms_available' ) && adn_cms_available() ) {
				$ctx['news']['items'] = adn_home_cms_news_items();

				$reg_items = adn_home_cms_regulations_items();
				if ( ! empty( $reg_items ) ) {
					$ctx['regulations']['items'] = $reg_items;
				}

				$ht_items = adn_home_cms_hot_topics_items();
				if ( ! empty( $ht_items ) ) {
					$ctx['hot_topics']['items'] = $ht_items;
				}
			}

			break;

		case 'tools':
			$ctx['tools'] = $home( 'tools', array( 'heading' => array(), 'items' => array() ) );
			if ( ! empty( $ctx['tools'] ) && function_exists( 'adn_calculators' ) ) {
				$_hp_registry  = adn_calculators();
				$_hp_meta_all  = get_option( 'adn_calculators_meta', array() );
				$_hp_items     = array();
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
						'icon'      => ! empty( $_hpc['icon'] ) ? (string) $_hpc['icon'] : adn_term( 'icons.tools', '🧮' ),
						'name'      => $_hpc['title'] ?? '',
						'url'       => ! empty( $_hpm['card_url'] ) ? (string) $_hpm['card_url'] : home_url( '/?ah_calc_page=' . rawurlencode( $_hpk ) ),
						'thumbnail' => $_hpthumb,
						'highlight' => ! empty( $_hpm['highlight'] ) ? (string) $_hpm['highlight'] : '',
						'desc'      => $_hpm['desc'] ?? '',
					);
				}
				if ( ! empty( $_hp_items ) ) {
					$ctx['tools']['items'] = $_hp_items;
				}
			}
			break;

		case 'guides':
			$ctx['guides'] = $home( 'guides', array( 'heading' => array(), 'items' => array() ) );
			if ( function_exists( 'adn_cms_available' ) && adn_cms_available() ) {
				$ctx['guides']['items'] = adn_home_cms_guide_items();
			}
			break;

		case 'resources':
			$ctx = array();
			break;
	}

	$cache[ $section ] = $ctx;
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

	// ── Diagram overrides ──────────────────────────────────────────────────
	$diagram      = isset( $hero['diagram'] ) && is_array( $hero['diagram'] ) ? $hero['diagram'] : array();
	$diag_changed = false;

	if ( ! empty( $opt['diagram_center_icon'] ) ) {
		$diagram['center_icon'] = sanitize_text_field( wp_unslash( $opt['diagram_center_icon'] ) );
		$diag_changed = true;
	}

	$center_lines = array();
	if ( ! empty( $opt['diagram_center_line1'] ) ) {
		$center_lines[] = sanitize_text_field( wp_unslash( $opt['diagram_center_line1'] ) );
	}
	if ( ! empty( $opt['diagram_center_line2'] ) ) {
		$center_lines[] = sanitize_text_field( wp_unslash( $opt['diagram_center_line2'] ) );
	}
	if ( ! empty( $center_lines ) ) {
		$diagram['center_lines'] = $center_lines;
		$diag_changed = true;
	}

	if ( ! empty( $opt['diagram_nodes'] ) ) {
		$nodes = array();
		foreach ( explode( "\n", wp_unslash( $opt['diagram_nodes'] ) ) as $line ) {
			if ( count( $nodes ) >= 8 ) {
				break; /* hard cap: circle layout supports max 8 nodes */
			}
			$line = trim( $line );
			if ( '' === $line ) {
				continue;
			}
			$parts   = explode( '|', $line, 2 );
			$nodes[] = array(
				'icon'  => sanitize_text_field( isset( $parts[0] ) ? trim( $parts[0] ) : '' ),
				'label' => sanitize_text_field( isset( $parts[1] ) ? trim( $parts[1] ) : '' ),
			);
		}
		if ( ! empty( $nodes ) ) {
			$diagram['nodes'] = $nodes;
			$diag_changed = true;
		}
	}

	if ( $diag_changed ) {
		$hero['diagram'] = $diagram;
	}

	return $hero;
}

/**
 * Map CMS Guide parent terms → journey-card props
 * { image, icon, gradient, title, description, link_label, url }.
 */
function adn_home_cms_journey_cards() {
	$cards     = array();
	$overrides = get_option( 'adn_journey_card_images', array() );
	if ( ! is_array( $overrides ) ) {
		$overrides = array();
	}
	foreach ( adn_cms_guide_parents() as $i => $term ) {
		$name = isset( $term->name ) ? $term->name : '';
		if ( '' === $name ) {
			continue;
		}
		// Theme-level override takes priority; falls back to term's own image_id.
		$tid         = (int) $term->id;
		$override_id = isset( $overrides[ $tid ] ) ? (int) $overrides[ $tid ] : 0;
		$image_id    = $override_id ?: ( ! empty( $term->image_id ) ? (int) $term->image_id : 0 );
		$image_url   = $image_id ? ( wp_get_attachment_image_url( $image_id, 'large' ) ?: '' ) : '';

		$cards[] = array(
			'image'       => $image_url,
			'icon'        => ! empty( $term->icon_emoji ) ? $term->icon_emoji : adn_term( 'icons.guide_fallback', '🏡' ),
			'gradient'    => adn_cms_gradient( $i ),
			'title'       => $name,
			'description' => isset( $term->description ) ? (string) $term->description : '',
			'link_label'  => adn_term( 'buttons.explore', 'Explore' ),
			'url'         => adn_cms_term_url( $term )
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

		$_term_img_url = '';
		if ( ! empty( $post->term_image_id ) ) {
			$_tiu          = wp_get_attachment_image_url( (int) $post->term_image_id, 'medium' );
			$_term_img_url = $_tiu ? (string) $_tiu : '';
		}
		$items[] = array(
			'icon'        => ! empty( $post->term_icon ) ? $post->term_icon : ( ! empty( $post->parent_icon ) ? $post->parent_icon : adn_term( 'icons.guide_parent', '📚' ) ),
			'gradient'    => adn_cms_gradient( $i ),
			'image'       => $_term_img_url,
			'parent_name' => ! empty( $post->parent_name ) ? $post->parent_name : '',
			'category'    => ! empty( $post->parent_name ) ? $post->parent_name : '',
			'title'       => $cat_name,
			'description' => ! empty( $post->_term_desc ) ? $post->_term_desc : '',
			'read_more'   => adn_term( 'content.read_more', 'Explore' ),
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
		foreach ( adn_cms_newsbar_items( 5 ) as $i => $item ) {
			$title = isset( $item->text ) ? $item->text : '';
			if ( '' === $title ) {
				continue;
			}
			$stamp     = ! empty( $item->created_at ) ? $item->created_at : '';
			$desc      = ! empty( $item->content ) ? wp_strip_all_tags( (string) $item->content ) : '';
			$thumb_url = '';
			if ( ! empty( $item->image_id ) ) {
				$_tu = wp_get_attachment_image_url( (int) $item->image_id, 'thumbnail' );
				$thumb_url = $_tu ? (string) $_tu : '';
			}
			$items[] = array(
				'title'       => $title,
				'description' => $desc,
				'date'        => $stamp ? date_i18n( 'M jS', strtotime( $stamp ) ) : '', // compact - mini cards
				'date_full'   => $stamp ? date_i18n( 'M jS, Y', strtotime( $stamp ) ) : '', // with year - hero card only
				'tag'         => ! empty( $item->label ) ? (string) $item->label : '',
				'gradient'    => adn_cms_gradient( $i ),
				'thumbnail'   => $thumb_url,
				'url'         => function_exists( 'adn_newsbar_item_url' ) ? adn_newsbar_item_url( $item->id ) : '#',
			);
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
	if ( empty( $raw ) ) {
		return array();
	}

	// Collect all post IDs first, then bulk-fetch in ONE query.
	$pids = array();
	$meta = array(); // keyed by pid
	foreach ( $raw as $row ) {
		$pid = (int) ( isset( $row['post_id'] ) ? $row['post_id'] : 0 );
		if ( $pid ) {
			$pids[]        = $pid;
			$meta[ $pid ]  = $row;
		}
	}
	if ( empty( $pids ) ) {
		return array();
	}

	// Single WP query for all posts.
	$posts_by_id = array();
	foreach ( get_posts( array(
		'post__in'       => $pids,
		'post_status'    => 'publish',
		'posts_per_page' => count( $pids ),
		'orderby'        => 'post__in',
	) ) as $p ) {
		$posts_by_id[ $p->ID ] = $p;
	}

	$items = array();
	foreach ( $pids as $pid ) {
		if ( ! isset( $posts_by_id[ $pid ] ) ) {
			continue;
		}
		$post        = $posts_by_id[ $pid ];
		$row         = $meta[ $pid ];
		$badge_raw   = isset( $row['badge'] ) ? sanitize_text_field( $row['badge'] ) : 'GOV UK';
		$badge_lines = array_filter( array_map( 'trim', explode( "\n", $badge_raw ) ) );
		if ( empty( $badge_lines ) ) {
			$badge_lines = array( 'GOV', 'UK' );
		}
		$items[] = array(
			'badge_lines' => array_values( $badge_lines ),
			'title'       => $post->post_title,
			'date'        => get_the_date( 'M j, Y', $post ),
			'thumbnail'   => get_the_post_thumbnail_url( $pid, 'thumbnail' ) ?: '',
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
	global $wpdb;
	$opt = get_option( 'adn_home_newsblocks', array() );
	$raw = ( isset( $opt['hot_topics']['items'] ) && is_array( $opt['hot_topics']['items'] ) )
	       ? $opt['hot_topics']['items'] : array();
	if ( empty( $raw ) ) {
		return array();
	}

	// Collect all post IDs + per-row meta first.
	$pids      = array();
	$row_meta  = array(); // keyed by pid
	foreach ( $raw as $row ) {
		$pid = (int) ( isset( $row['post_id'] ) ? $row['post_id'] : 0 );
		if ( $pid ) {
			$pids[]       = $pid;
			$row_meta[ $pid ] = $row;
		}
	}
	if ( empty( $pids ) ) {
		return array();
	}

	// 1 WP query for all posts at once.
	$posts_by_id = array();
	foreach ( get_posts( array(
		'post__in'       => $pids,
		'post_status'    => 'publish',
		'posts_per_page' => count( $pids ),
		'orderby'        => 'post__in',
	) ) as $p ) {
		$posts_by_id[ $p->ID ] = $p;
	}

	// 1 CMS query for all term icons at once (if CMS tables exist).
	$icon_by_pid = array();
	$cms_ok      = adn_cms_available();
	if ( $cms_ok ) {
		$tax = adn_cms_table( 'taxonomies' );
		$ct  = adn_cms_table( 'content_taxonomies' );
		$id_in = implode( ',', array_map( 'intval', $pids ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$term_rows = $wpdb->get_results(
			"SELECT ct.post_id,
			        t.icon_emoji  AS term_icon,
			        pt.icon_emoji AS parent_icon
			 FROM `{$ct}` ct
			 JOIN `{$tax}` t  ON t.id = ct.taxonomy_id
			 LEFT JOIN `{$tax}` pt ON pt.id = t.parent_id
			 WHERE ct.post_id IN ({$id_in})
			 ORDER BY ct.post_id ASC, t.sort_order ASC"
		) ?: array();
		// Keep only first row per post_id (lowest sort_order = primary term).
		foreach ( $term_rows as $tr ) {
			$pid2 = (int) $tr->post_id;
			if ( ! isset( $icon_by_pid[ $pid2 ] ) ) {
				$icon_by_pid[ $pid2 ] = ! empty( $tr->term_icon )
					? (string) $tr->term_icon
					: ( ! empty( $tr->parent_icon ) ? (string) $tr->parent_icon : '' );
			}
		}
	}

	$items = array();
	foreach ( $pids as $pid ) {
		if ( ! isset( $posts_by_id[ $pid ] ) ) {
			continue;
		}
		$post = $posts_by_id[ $pid ];
		$row  = $row_meta[ $pid ];

		// Icon: row override → CMS term → fallback emoji.
		$icon = ! empty( $row['icon'] ) ? sanitize_text_field( $row['icon'] ) : '';
		if ( '' === $icon && isset( $icon_by_pid[ $pid ] ) && '' !== $icon_by_pid[ $pid ] ) {
			$icon = $icon_by_pid[ $pid ];
		}
		if ( '' === $icon ) {
			$icon = adn_term( 'icons.hot_topics', '🔥' );
		}

		$items[] = array(
			'icon'      => $icon,
			'text'      => $post->post_title,
			'desc'      => $post->post_excerpt,
			'thumbnail' => get_the_post_thumbnail_url( $pid, 'medium' ) ?: '',
			'url'       => get_permalink( $post ),
		);
	}
	return $items;
}
