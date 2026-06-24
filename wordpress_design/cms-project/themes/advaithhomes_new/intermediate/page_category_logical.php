<?php
/**
 * intermediate/page_category_logical.php
 *
 * Intermediate logic for all category guide pages (buying, selling, house-movers…).
 * All data comes from the CMS plugin and WordPress - no JSON files.
 *
 * Data sources:
 *   hero / meta / breadcrumb  → ah_taxonomy_parent_terms (CMS plugin)
 *   guides.items              → ah_posts articles for this parent slug (CMS plugin)
 *   news.items                → plugin News Bar → CMS latest news → WP_Query
 *   regulations.items         → latest published WP posts
 *
 * RULE: No markup here - only data shaping.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Category cards for the Guides & Insights section on a parent-term page.
 *
 * Shows one card per child taxonomy term (not per article).
 * Filters to terms whose parent_term_id matches the parent slug's DB row.
 * Falls back to all terms when no parent_term_id associations exist.
 * Card URL → /term-slug/ category listing page.
 */
function adn_category_cms_guides( $slug ) {
	if ( ! function_exists( 'adn_cms_guides_by_category' ) ) {
		return array();
	}

	// Try to filter by the parent term's child terms.
	$topic_ids = array();
	if ( $slug !== '' && function_exists( 'adn_cms_available' ) && adn_cms_available() ) {
		global $wpdb;
		$pt_table = $wpdb->prefix . 'ah_taxonomy_parent_terms';
		$tax_table = $wpdb->prefix . 'ah_taxonomies';

		$parent = $wpdb->get_row( $wpdb->prepare(
			"SELECT id FROM `{$pt_table}` WHERE slug = %s AND status = 'active' LIMIT 1",
			$slug
		) );

		if ( $parent && function_exists( 'adn_cms_topics' ) ) {
			$children = adn_cms_topics( (int) $parent->id, 50 );
			foreach ( (array) $children as $child ) {
				if ( ! empty( $child->id ) ) {
					$topic_ids[] = (int) $child->id;
				}
			}
		}

		// If adn_cms_topics() returned nothing, try a direct column query as fallback.
		if ( empty( $topic_ids ) && $parent ) {
			$has_col = $wpdb->get_var( "SHOW COLUMNS FROM `{$tax_table}` LIKE 'parent_term_id'" );
			if ( $has_col ) {
				$rows = $wpdb->get_results( $wpdb->prepare(
					"SELECT id FROM `{$tax_table}` WHERE parent_term_id = %d AND status = 'active'",
					(int) $parent->id
				) );
				foreach ( (array) $rows as $r ) {
					$topic_ids[] = (int) $r->id;
				}
			}
		}

		// Parent exists but has no linked child topics - don't fall back to showing all site guides.
		if ( ! empty( $parent ) && empty( $topic_ids ) ) {
			return array();
		}
	}

	// Fetch category rows (pass topic_ids filter; empty = all active terms).
	$rows  = adn_cms_guides_by_category( 10, $topic_ids );
	$items = array();
	foreach ( $rows as $i => $post ) {
		$cat_name = isset( $post->category_name ) ? (string) $post->category_name : '';
		if ( '' === $cat_name ) {
			continue;
		}
		$term_url = home_url( '/' . trim( (string) $post->_term_slug, '/' ) . '/' );
		$_cg_img = '';
		if ( ! empty( $post->term_image_id ) ) {
			$_cgu    = wp_get_attachment_image_url( (int) $post->term_image_id, 'medium' );
			$_cg_img = $_cgu ? (string) $_cgu : '';
		}
		$items[] = array(
			'icon'        => ! empty( $post->term_icon ) ? $post->term_icon : ( ! empty( $post->parent_icon ) ? $post->parent_icon : '📚' ),
			'gradient'    => adn_cms_gradient( $i ),
			'image'       => $_cg_img,
			'parent_name' => ! empty( $post->parent_name ) ? $post->parent_name : '',
			'category'    => $cat_name,
			'title'       => '',
			'description' => ! empty( $post->_term_desc ) ? $post->_term_desc : '',
			'read_more'   => SITE_BTN_EXPLORE_ARROW,
			'url'         => $term_url,
		);
	}
	return $items;
}

/**
 * Fetch news items for the category page.
 * Priority: Plugin News Bar → CMS latest news → WP_Query.
 */
function adn_category_cms_news( $limit = 3 ) {
	$items = array();

	// 1. Plugin News Bar.
	if ( function_exists( 'adn_cms_newsbar_items' ) ) {
		foreach ( adn_cms_newsbar_items( $limit ) as $i => $item ) {
			$title = isset( $item->text ) ? $item->text : '';
			if ( '' === $title ) {
				continue;
			}
			$stamp     = ! empty( $item->start_date ) ? $item->start_date : '';
			$thumb_url = '';
			if ( ! empty( $item->image_id ) ) {
				$_tu = wp_get_attachment_image_url( (int) $item->image_id, 'thumbnail' );
				$thumb_url = $_tu ? (string) $_tu : '';
			}
			$items[] = array(
				'title'     => $title,
				'date'      => $stamp ? date_i18n( 'M j, Y', strtotime( $stamp ) ) : '',
				'tag'       => ! empty( $item->label ) ? (string) $item->label : '',
				'thumbnail' => $thumb_url,
				'gradient'  => adn_cms_gradient( $i ),
				'url'       => ! empty( $item->link_url ) ? $item->link_url : SITE_NEWS_URL,
			);
		}
	}

	// 2. CMS latest news taxonomy posts.
	if ( empty( $items ) && function_exists( 'adn_cms_latest_news' ) ) {
		foreach ( adn_cms_latest_news( $limit ) as $i => $post ) {
			$title = isset( $post->title ) ? (string) $post->title : '';
			if ( '' === $title ) {
				continue;
			}
			$_thumb = '';
			if ( ! empty( $post->featured_image_id ) ) {
				$_u = wp_get_attachment_image_url( (int) $post->featured_image_id, 'medium' );
				if ( $_u ) { $_thumb = (string) $_u; }
			}
			$items[] = array(
				'title'     => $title,
				'date'      => adn_cms_post_date( $post ),
				'tag'       => 'NEWS',
				'thumbnail' => $_thumb,
				'gradient'  => adn_cms_gradient( $i ),
				'url'       => adn_cms_post_url( $post ),
			);
		}
	}

	// 3. WP_Query fallback.
	if ( empty( $items ) ) {
		$q = new WP_Query( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		if ( $q->have_posts() ) {
			foreach ( $q->posts as $i => $wp_post ) {
				$_thumb = get_the_post_thumbnail_url( $wp_post->ID, 'medium' );
				$items[] = array(
					'title'     => $wp_post->post_title,
					'date'      => get_the_date( 'M j, Y', $wp_post ),
					'tag'       => 'NEWS',
					'thumbnail' => $_thumb ? (string) $_thumb : '',
					'gradient'  => adn_cms_gradient( $i ),
					'url'      => get_permalink( $wp_post ),
				);
			}
			wp_reset_postdata();
		}
	}

	return $items;
}

/**
 * Latest posts for this category, shaped for regulation_item cards.
 * Primary source: CMS articles linked to the parent term slug.
 * Fallback: most recent WP posts site-wide.
 */
function adn_category_latest_updates( $slug, $limit = 4 ) {
	$items = array();

	// 1. CMS articles filtered by this parent term's topic taxonomy.
	if ( function_exists( 'adn_cms_articles_for_parent' ) ) {
		$rows = adn_cms_articles_for_parent( $slug, $limit );
		foreach ( (array) $rows as $post ) {
			if ( empty( $post->title ) ) { continue; }
			$_thumb = '';
			if ( ! empty( $post->featured_image_id ) ) {
				$_u = wp_get_attachment_image_url( (int) $post->featured_image_id, 'medium' );
				if ( $_u ) { $_thumb = (string) $_u; }
			}
			$items[] = array(
				'thumbnail' => $_thumb,
				'icon'      => '📋',
				'title'     => (string) $post->title,
				'url'       => function_exists( 'adn_cms_post_url' )  ? adn_cms_post_url( $post )  : '#',
			);
		}
	}

	// 2. WP_Query fallback when no CMS articles exist for this category.
	if ( empty( $items ) ) {
		$q = new WP_Query( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		if ( $q->have_posts() ) {
			foreach ( $q->posts as $wp_post ) {
				$_thumb = get_the_post_thumbnail_url( $wp_post->ID, 'medium' );
				$items[] = array(
					'thumbnail' => $_thumb ? (string) $_thumb : '',
					'overlay'   => 'Latest Update',
					'icon'      => '📋',
					'title'     => $wp_post->post_title,
					'date'      => get_the_date( 'M j, Y', $wp_post ),
					'url'       => get_permalink( $wp_post ),
				);
			}
			wp_reset_postdata();
		}
	}

	return $items;
}

/**
 * Fetch the active parent term row for $slug. Returns null when not found.
 */
function adn_category_parent_term( $slug ) {
	if ( ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() ) {
		return null;
	}
	global $wpdb;
	$table = $wpdb->prefix . 'ah_taxonomy_parent_terms';
	if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
		return null;
	}
	return $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM {$table} WHERE slug = %s AND status = 'active' LIMIT 1",
		$slug
	) );
}

/**
 * Build the full render context for a category guide page.
 * All data from CMS plugin and WordPress - no JSON dependency.
 */
function adn_category_get_context( $slug = '' ) {

	// ── 1. Resolve slug ──────────────────────────────────────────────
	if ( '' === $slug ) {
		// Virtual route: slug injected by adn_route_parent_term_template().
		$qv = (string) get_query_var( 'adn_cat_slug', '' );
		if ( '' !== $qv ) {
			$slug = $qv;
		} else {
			$page = get_queried_object();
			$slug = ( $page instanceof WP_Post ) ? (string) $page->post_name : '';
		}
	}
	$slug = sanitize_key( $slug );

	$chrome = function_exists( 'adn_service_site_chrome' ) ? adn_service_site_chrome() : array();

	// ── 2. Parent term (primary CMS data source) ─────────────────────
	$term = adn_category_parent_term( $slug );
	$name = $term && isset( $term->name )        ? (string) $term->name        : ucwords( str_replace( '-', ' ', $slug ) );
	$desc = $term && isset( $term->description ) ? (string) $term->description : '';
	$icon = $term && isset( $term->icon_emoji )  ? (string) $term->icon_emoji  : '';
	$img  = $term && ! empty( $term->image_id )  ? (int)    $term->image_id    : 0;

	// ── 2.5. Load all category settings (thumbnail overrides parent term image) ──
	$_cs_all      = class_exists( 'AH_Category_Settings' ) ? AH_Category_Settings::get_all( $slug ) : array();
	$_cs_app      = isset( $_cs_all['appearance'] ) && is_array( $_cs_all['appearance'] ) ? $_cs_all['appearance'] : array();
	$_cs_thumb_id = ! empty( $_cs_app['thumbnail_id'] ) ? (int) $_cs_app['thumbnail_id'] : 0;

	// ── 3. Hero ──────────────────────────────────────────────────────
	// 1st: per-term marquee (adn-theme-category-pages → Marquee tab).
	$_cs_mq     = isset( $_cs_all['marquee'] ) && is_array( $_cs_all['marquee'] ) ? $_cs_all['marquee'] : array();
	$_mq_parsed = function_exists( 'adn_parse_marquee_settings' ) ? adn_parse_marquee_settings( $_cs_mq ) : null;

	// 2nd fallback: home-page marquee (adn-theme-home → Sections → Marquee bar).
	if ( ! $_mq_parsed ) {
		$_home_s    = get_option( 'adn_home_sections', array() );
		$_mq_parsed = function_exists( 'adn_parse_marquee_settings' ) ? adn_parse_marquee_settings( $_home_s ) : null;
	}

	$_trust_items = $_mq_parsed
		? $_mq_parsed['trust']
		:[];
	$hero = array(
		'title'       => $name,
		'description' => $desc,
		'image_icon'  => $icon,
		'image_id'    => $_cs_thumb_id ?: $img,
		'trust_items' => $_trust_items,
	);

	// ── 4. Meta & Breadcrumb ──────────────────────────────────────────
	$meta = array(
		'slug'             => $slug,
		'page_title'       => $name . ' - ' . SITE_BRAND_NAME,
		'meta_description' => $desc,
	);
	$breadcrumb = array(
		array( 'label' => PAGE_TITLE_HOME, 'url' => '/' ),
		array( 'label' => $name,           'url' => null ),
	);

	// ── 5. Guides: CMS articles for this parent slug ─────────────────
	$guides = array(
		'heading' => array(
			'title'      => sprintf( adn_term( 'category_page.explore_guides_title', 'Explore %s %s' ), $name, adn_term( 'taxonomy.parent_plural', 'Guides' ) ),
			'link_label' => adn_term( 'content.view_all_guides', 'View all →' ),
			'link_url'   => SITE_GUIDES_URL,
		),
		'items' => adn_category_cms_guides( $slug ),
	);

	// ── 7. Latest Updates - CMS articles for this category (fallback: all posts) ──
	$regulations = array(
		'heading' => array(
			'title'      => adn_term( 'category_page.latest_updates_title', 'Latest Updates' ),
			'link_label' => adn_term( 'category_page.latest_updates_view_all', 'View all →' ),
			'link_url'   => SITE_NEWS_URL,
		),
		'items' => adn_category_latest_updates( $slug, 2 ),
	);

	// ── 8. Admin-managed sections (AH_Category_Settings DB model) ───────
	$_cs_journey  = isset( $_cs_all['journey'] )        && is_array( $_cs_all['journey'] )        ? $_cs_all['journey']        : array();
	$_cs_ht       = isset( $_cs_all['hot_topics'] )     && is_array( $_cs_all['hot_topics'] )     ? $_cs_all['hot_topics']     : array();
	$_cs_pp       = isset( $_cs_all['popular_posts'] )  && is_array( $_cs_all['popular_posts'] )  ? $_cs_all['popular_posts']  : array();
	$_cs_ft       = isset( $_cs_all['featured_topics'] ) && is_array( $_cs_all['featured_topics'] ) ? $_cs_all['featured_topics'] : array();
	$_cs_calc     = isset( $_cs_all['calculators'] )    && is_array( $_cs_all['calculators'] )    ? $_cs_all['calculators']    : array();
	$_cs_sidebar  = isset( $_cs_all['sidebar'] )        && is_array( $_cs_all['sidebar'] )        ? $_cs_all['sidebar']        : array();
	$_cs_cta      = isset( $_cs_all['cta_banner'] )     && is_array( $_cs_all['cta_banner'] )     ? $_cs_all['cta_banner']     : array();
	$_cs_faqs     = isset( $_cs_all['faqs'] )           && is_array( $_cs_all['faqs'] )           ? $_cs_all['faqs']           : array();
	$_cs_sp       = isset( $_cs_all['spotlights'] )     && is_array( $_cs_all['spotlights'] )     ? $_cs_all['spotlights']     : array();

	// Journey.
	$journey = array();
	if ( ! empty( $_cs_journey['steps'] ) && is_array( $_cs_journey['steps'] ) ) {
		$steps = array();
		foreach ( $_cs_journey['steps'] as $s ) {
			if ( empty( $s['label'] ) ) { continue; }
			$steps[] = array(
				'icon'   => ! empty( $s['icon'] ) ? (string) $s['icon'] : '',
				'num'    => (string) ( count( $steps ) + 1 ),
				'label'  => (string) $s['label'],
				'desc'   => ! empty( $s['desc'] ) ? (string) $s['desc'] : '',
				'active' => ( 0 === count( $steps ) ),
			);
		}
		$tip = array();
		if ( ! empty( $_cs_journey['tip_text'] ) ) {
			$tip = array(
				'icon'       => ! empty( $_cs_journey['tip_icon'] )       ? (string) $_cs_journey['tip_icon']       : '💡',
				'text'       => (string) $_cs_journey['tip_text'],
				'link_label' => ! empty( $_cs_journey['tip_link_label'] ) ? (string) $_cs_journey['tip_link_label'] : '',
				'link_url'   => ! empty( $_cs_journey['tip_link_url'] )   ? (string) $_cs_journey['tip_link_url']   : '',
			);
		}
		$journey = array(
			'heading' => ! empty( $_cs_journey['heading'] ) ? (string) $_cs_journey['heading'] : sprintf( adn_term( 'category_page.journey_heading', 'Your %s Journey' ), $name ),
			'steps'   => $steps,
			'tip'     => $tip,
		);
	}

	// Calculators - built from selected_keys checked against the registry.
	$calculators = array();
	if ( ! empty( $_cs_calc['selected_keys'] ) && is_array( $_cs_calc['selected_keys'] )
		&& function_exists( 'adn_calculators' ) ) {
		$all_tools    = adn_calculators();
		$calc_meta    = get_option( 'adn_calculators_meta', array() );
		$items        = array();
		foreach ( $_cs_calc['selected_keys'] as $key ) {
			$key = sanitize_key( $key );
			if ( ! isset( $all_tools[ $key ] ) ) { continue; }
			$reg  = $all_tools[ $key ];
			$cmeta = function_exists( 'adn_calculator_meta' ) ? adn_calculator_meta( $key ) : array();
			$_pclg_thumb = '';
			if ( ! empty( $cmeta['thumbnail_id'] ) ) {
				$_pclg_t = wp_get_attachment_image_url( (int) $cmeta['thumbnail_id'], 'medium' );
				$_pclg_thumb = $_pclg_t ? (string) $_pclg_t : '';
			}
			$items[] = array(
				'icon'      => ! empty( $cmeta['icon'] ) ? (string) $cmeta['icon'] : ( ! empty( $reg['icon'] ) ? (string) $reg['icon'] : '🧮' ),
				'name'      => ! empty( $cmeta['label'] ) ? (string) $cmeta['label'] : ( ! empty( $reg['title'] ) ? (string) $reg['title'] : $key ),
				'url'       => ! empty( $cmeta['card_url'] ) ? (string) $cmeta['card_url'] : home_url( SITE_CALCULATORS_URL . '?calc=' . rawurlencode( $key ) ),
				'thumbnail' => $_pclg_thumb,
			);
		}
		if ( ! empty( $items ) ) {
			$calculators = array(
				'heading' => array(
					'title'      => ! empty( $_cs_calc['heading'] ) ? (string) $_cs_calc['heading'] : sprintf( adn_term( 'category_page.calculators_heading', '%s for %s' ), SITE_TOOLS_PLURAL, $name ),
					'link_label' => sprintf( adn_term( 'category_page.related_tools_heading', 'View all %s →' ), strtolower( SITE_TOOLS_PLURAL ) ),
					'link_url'   => SITE_CALCULATORS_URL,
				),
				'items' => $items,
			);
		}
	}

	// Sidebar quick tools.
	$sidebar = array();
	if ( ! empty( $_cs_sidebar['tools'] ) && is_array( $_cs_sidebar['tools'] ) ) {
		$tools = array();
		foreach ( $_cs_sidebar['tools'] as $t ) {
			if ( empty( $t['label'] ) ) { continue; }
			$tools[] = array(
				'icon'  => ! empty( $t['icon'] )  ? (string) $t['icon']  : '',
				'label' => (string) $t['label'],
				'url'   => ! empty( $t['url'] )   ? (string) $t['url']   : '#',
			);
		}
		if ( ! empty( $tools ) ) {
			$sidebar['quick_tools'] = array(
				'heading' => adn_term( 'category_page.quick_tools_heading', 'Quick Tools' ),
				'items'   => $tools,
				'cta'     => array(
					'label' => ! empty( $_cs_sidebar['cta_label'] ) ? (string) $_cs_sidebar['cta_label'] : '',
					'url'   => ! empty( $_cs_sidebar['cta_url'] )   ? (string) $_cs_sidebar['cta_url']   : '',
				),
			);
		}
	}

	// Sidebar hot topics (from DB, items shaped {icon, label, url}).
	if ( ! empty( $_cs_ht['items'] ) && is_array( $_cs_ht['items'] ) ) {
		$topics = array();
		foreach ( $_cs_ht['items'] as $t ) {
			if ( empty( $t['label'] ) ) { continue; }
			$topics[] = array(
				'icon'  => ! empty( $t['icon'] )  ? (string) $t['icon']  : '',
				'label' => (string) $t['label'],
				'url'   => ! empty( $t['url'] )   ? (string) $t['url']   : '#',
			);
		}
		if ( ! empty( $topics ) ) {
			$sidebar['hot_topics'] = array(
				'heading'  => ! empty( $_cs_ht['heading'] )        ? (string) $_cs_ht['heading']        : adn_term( 'category_page.hot_topics_heading', '🔥 Hot Topics' ),
				'items'    => $topics,
				'view_all' => array(
					'label' => ! empty( $_cs_ht['view_all_label'] ) ? (string) $_cs_ht['view_all_label'] : '',
					'url'   => ! empty( $_cs_ht['view_all_url'] )   ? (string) $_cs_ht['view_all_url']   : '',
				),
			);
		}
	}

	// Sidebar expert help.
	if ( ! empty( $_cs_sidebar['experts'] ) && is_array( $_cs_sidebar['experts'] ) ) {
		$expert_list = array();
		foreach ( $_cs_sidebar['experts'] as $e ) {
			if ( empty( $e['name'] ) ) { continue; }
			$expert_list[] = array(
				'icon' => ! empty( $e['icon'] ) ? (string) $e['icon'] : '',
				'name' => (string) $e['name'],
				'desc' => ! empty( $e['desc'] ) ? (string) $e['desc'] : '',
				'url'  => ! empty( $e['url'] )  ? (string) $e['url']  : '#',
			);
		}
		if ( ! empty( $expert_list ) ) {
			$sidebar['expert_help'] = array(
				'heading'  => ! empty( $_cs_sidebar['expert_heading'] )   ? (string) $_cs_sidebar['expert_heading']   : adn_term( 'sidebar.expert_help_heading', 'Need Expert Help?' ),
				'subtitle' => ! empty( $_cs_sidebar['expert_subtitle'] )  ? (string) $_cs_sidebar['expert_subtitle']  : '',
				'experts'  => $expert_list,
				'cta'      => array(
					'label' => ! empty( $_cs_sidebar['expert_cta_label'] ) ? (string) $_cs_sidebar['expert_cta_label'] : '',
					'url'   => ! empty( $_cs_sidebar['expert_cta_url'] )   ? (string) $_cs_sidebar['expert_cta_url']   : '#',
				),
			);
		}
	}

	// Sidebar featured topics (taxonomy terms picked in admin Content tab).
	if ( ! empty( $_cs_ft['items'] ) && is_array( $_cs_ft['items'] ) ) {
		$ft_items = array();
		foreach ( $_cs_ft['items'] as $t ) {
			if ( empty( $t['name'] ) ) { continue; }
			$ft_items[] = array(
				'icon'  => ! empty( $t['icon'] ) ? (string) $t['icon'] : '',
				'label' => (string) $t['name'],
				'url'   => ! empty( $t['url'] )  ? (string) $t['url']  : '#',
			);
		}
		if ( ! empty( $ft_items ) ) {
			$sidebar['featured_topics'] = array(
				'heading' => ! empty( $_cs_ft['heading'] ) ? (string) $_cs_ft['heading'] : adn_term( 'category_page.browse_topics_heading', 'Browse Topics' ),
				'items'   => $ft_items,
			);
		}
	}

	// Sidebar: related calculators (remapped to quick_tools shape for sidebar_quick_tools).
	if ( ! empty( $calculators['items'] ) ) {
		$_calc_tools = array();
		foreach ( $calculators['items'] as $c ) {
			$_calc_tools[] = array(
				'icon'  => isset( $c['icon'] ) ? (string) $c['icon'] : '🧮',
				'label' => isset( $c['name'] ) ? (string) $c['name'] : '',
				'url'   => isset( $c['url'] )  ? (string) $c['url']  : '#',
			);
		}
		if ( empty( $sidebar['quick_tools'] ) ) {
			$sidebar['quick_tools'] = array(
				'heading' => ! empty( $calculators['heading']['title'] ) ? (string) $calculators['heading']['title'] : sprintf( adn_term( 'category_page.related_tools_heading', 'Related %s' ), SITE_TOOLS_PLURAL ),
				'items'   => $_calc_tools,
				'cta'     => array( 'label' => sprintf( adn_term( 'category_page.related_tools_heading', 'All %s →' ), SITE_TOOLS_PLURAL ), 'url' => SITE_CALCULATORS_URL ),
			);
		}
	}

	// Sidebar: expert help - fallback to global page settings when not set in admin.
	if ( empty( $sidebar['expert_help'] ) ) {
		$_eh_pg = get_option( 'adn_calculators_page', array() );
		$sidebar['expert_help'] = array(
			'heading'  => ! empty( $_cs_sidebar['expert_heading'] )   ? (string) $_cs_sidebar['expert_heading']   : ( ! empty( $_eh_pg['sidebar_help_title'] ) ? (string) $_eh_pg['sidebar_help_title'] : adn_term( 'sidebar.expert_help_heading', 'Need Expert Help?' ) ),
			'subtitle' => ! empty( $_cs_sidebar['expert_subtitle'] )  ? (string) $_cs_sidebar['expert_subtitle']  : ( ! empty( $_eh_pg['sidebar_help_text'] )  ? (string) $_eh_pg['sidebar_help_text']  : adn_term( 'sidebar.expert_help_subtitle', 'Get personalised guidance from our property experts.' ) ),
			'experts'  => array(),
			'cta'      => array(
				'label' => ! empty( $_cs_sidebar['expert_cta_label'] ) ? (string) $_cs_sidebar['expert_cta_label'] : ( ! empty( $_eh_pg['sidebar_help_btn_label'] ) ? (string) $_eh_pg['sidebar_help_btn_label'] : SITE_EXPERT_LABEL ),
				'url'   => ! empty( $_cs_sidebar['expert_cta_url'] )   ? (string) $_cs_sidebar['expert_cta_url']   : ( ! empty( $_eh_pg['sidebar_help_btn_url'] )   ? (string) $_eh_pg['sidebar_help_btn_url']   : SITE_EXPERT_URL ),
			),
		);
	}

	// Resources (PDFs, external links, YouTube videos) from admin Resources tab.
	$resources  = array( 'pdfs' => array(), 'links' => array(), 'videos' => array() );
	$_cs_res    = isset( $_cs_all['resources'] ) && is_array( $_cs_all['resources'] ) ? $_cs_all['resources'] : array();

	foreach ( (array) ( isset( $_cs_res['pdfs'] ) ? $_cs_res['pdfs'] : array() ) as $p ) {
		if ( empty( $p['title'] ) ) { continue; }
		$fid      = ! empty( $p['file_id'] )  ? (int)    $p['file_id']  : 0;
		$furl     = ! empty( $p['file_url'] ) ? (string) $p['file_url'] : '';
		// Prefer WP attachment URL (always fresh) over stored URL.
		if ( $fid ) {
			$_att = wp_get_attachment_url( $fid );
			if ( $_att ) { $furl = $_att; }
		}
		if ( ! $furl ) { continue; }
		$resources['pdfs'][] = array(
			'title'    => (string) $p['title'],
			'desc'     => ! empty( $p['desc'] ) ? (string) $p['desc'] : '',
			'file_url' => $furl,
		);
	}

	foreach ( (array) ( isset( $_cs_res['links'] ) ? $_cs_res['links'] : array() ) as $l ) {
		if ( empty( $l['title'] ) ) { continue; }
		$resources['links'][] = array(
			'icon'  => ! empty( $l['icon'] )  ? (string) $l['icon']  : '🔗',
			'title' => (string) $l['title'],
			'desc'  => ! empty( $l['desc'] )  ? (string) $l['desc']  : '',
			'url'   => ! empty( $l['url'] )   ? (string) $l['url']   : '#',
		);
	}

	foreach ( (array) ( isset( $_cs_res['videos'] ) ? $_cs_res['videos'] : array() ) as $v ) {
		if ( empty( $v['title'] ) ) { continue; }
		$vid_url = ! empty( $v['url'] ) ? (string) $v['url'] : '';
		$vid_id  = '';
		if ( preg_match( '#(?:youtube\.com/watch\?v=|youtu\.be/)([a-zA-Z0-9_-]{11})#', $vid_url, $_m ) ) {
			$vid_id = $_m[1];
		}
		$resources['videos'][] = array(
			'title'  => (string) $v['title'],
			'desc'   => ! empty( $v['desc'] ) ? (string) $v['desc'] : '',
			'url'    => $vid_url,
			'vid_id' => $vid_id,
			'thumb'  => $vid_id ? 'https://img.youtube.com/vi/' . $vid_id . '/mqdefault.jpg' : '',
		);
	}

	// FAQs: plugin items selected by ID in admin, loaded fresh from DB.
	$faqs = array();
	if ( ! empty( $_cs_faqs['items'] ) && is_array( $_cs_faqs['items'] ) ) {
		$_faq_ids = array();
		foreach ( (array) $_cs_faqs['items'] as $_fi ) {
			if ( ! empty( $_fi['faq_id'] ) ) {
				$_faq_ids[] = (int) $_fi['faq_id'];
			}
			if ( count( $_faq_ids ) >= 10 ) { break; }
		}
		$_faq_ids = array_filter( $_faq_ids );

		if ( ! empty( $_faq_ids ) ) {
			global $wpdb;
			$_faq_table = $wpdb->prefix . 'ah_faqs';
			if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $_faq_table ) ) === $_faq_table ) {
				$_placeholders = implode( ',', array_fill( 0, count( $_faq_ids ), '%d' ) );
				$_faq_rows     = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT id, question, answer, link_url, link_text FROM `{$_faq_table}` WHERE id IN ({$_placeholders}) AND status = 'active'",
						...$_faq_ids
					)
				);
				// Restore admin-defined pill order.
				$_id_pos = array_flip( $_faq_ids );
				usort( $_faq_rows, function ( $a, $b ) use ( $_id_pos ) {
					return ( isset( $_id_pos[ $a->id ] ) ? $_id_pos[ $a->id ] : 0 )
					     - ( isset( $_id_pos[ $b->id ] ) ? $_id_pos[ $b->id ] : 0 );
				} );
				$_faq_built = array();
				foreach ( $_faq_rows as $_fr ) {
					$_faq_built[] = array(
						'id'        => (int)    $_fr->id,
						'question'  => (string) $_fr->question,
						'answer'    => (string) $_fr->answer,
						'link_url'  => (string) ( $_fr->link_url  ?? '' ),
						'link_text' => (string) ( $_fr->link_text ?? '' ),
					);
				}
				if ( ! empty( $_faq_built ) ) {
					$faqs = array(
						'heading' => ! empty( $_cs_faqs['heading'] ) ? (string) $_cs_faqs['heading'] : sprintf( '%s FAQs', $name ),
						'items'   => $_faq_built,
					);
				}
			}
		}
	}

	// CTA banner.
	$cta_banner = array();
	if ( ! empty( $_cs_cta['title'] ) ) {
		$cta_banner = array(
			'icon'        => ! empty( $_cs_cta['icon'] )        ? (string) $_cs_cta['icon']        : '🏡',
			'title'       => (string) $_cs_cta['title'],
			'description' => ! empty( $_cs_cta['description'] ) ? (string) $_cs_cta['description'] : '',
			'cta'         => array(
				'label' => ! empty( $_cs_cta['btn_label'] ) ? (string) $_cs_cta['btn_label'] : '',
				'url'   => ! empty( $_cs_cta['btn_url'] )   ? (string) $_cs_cta['btn_url']   : '#',
			),
		);
	}

	// Popular Posts (curated WP posts, loaded live by post ID).
	$popular_posts = array();
	if ( ! empty( $_cs_pp['items'] ) && is_array( $_cs_pp['items'] ) ) {
		// Collect IDs preserving admin-defined order (up to 6).
		$_pp_ids = array();
		foreach ( (array) $_cs_pp['items'] as $_item ) {
			if ( ! empty( $_item['post_id'] ) ) {
				$_pp_ids[] = (int) $_item['post_id'];
			}
			if ( count( $_pp_ids ) >= 6 ) { break; }
		}
		$_pp_ids = array_filter( $_pp_ids );

		if ( ! empty( $_pp_ids ) ) {
			$_pp_q = new WP_Query( array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'post__in'       => $_pp_ids,
				'orderby'        => 'post__in',
				'posts_per_page' => 6,
				'no_found_rows'  => true,
			) );
			$pp_cards = array();
			foreach ( $_pp_q->posts as $i => $_pp_post ) {
				$_ex    = $_pp_post->post_excerpt ? $_pp_post->post_excerpt : $_pp_post->post_content;
				$_thumb = get_the_post_thumbnail_url( $_pp_post->ID, 'medium_large' );
				$pp_cards[] = array(
					'image'       => $_thumb ? $_thumb : '',
					'icon'        => 'fa-solid fa-book-open',
					'gradient'    => adn_cms_gradient( $i ),
					'category'    => PARENT_TERM,
					'title'       => $_pp_post->post_title,
					'description' => wp_trim_words( $_ex, 18, '…' ),
					'read_more'   => adn_term( 'content.read_more', 'Explore →' ),
					'url'         => get_permalink( $_pp_post ),
				);
			}
			wp_reset_postdata();

			if ( ! empty( $pp_cards ) ) {
				$popular_posts = array(
					'heading' => array(
						'title'      => ! empty( $_cs_pp['heading'] ) ? (string) $_cs_pp['heading'] : sprintf( adn_term( 'category_page.popular_guides_heading', 'Popular %s' ), adn_term( 'taxonomy.parent_plural', 'Guides' ) ),
						'link_label' => '',
						'link_url'   => '',
					),
					'items' => $pp_cards,
				);
			}
		}
	}

	// News for main content area (2 items).
	$_main_news_items = array();
	foreach ( array_slice( adn_category_cms_news( 2 ), 0, 2 ) as $n ) {
		$_entry = array(
			'title' => $n['title'],
			'url'   => $n['url'],
			'date'  => $n['date'],
			'tag'   => isset( $n['tag'] ) ? $n['tag'] : '',
		);
		if ( ! empty( $n['thumbnail'] ) ) {
			$_entry['thumbnail'] = $n['thumbnail'];
		} else {
			$_entry['icon'] = '📰';
		}
		$_main_news_items[] = $_entry;
	}
	$news = array(
		'heading' => array(
			'title'      => adn_term( 'labels.latest_news', 'Latest News' ),
			'link_label' => adn_term( 'buttons.view_all', 'View all →' ),
			'link_url'   => SITE_NEWS_URL,
		),
		'items' => $_main_news_items,
	);

	return array(
		'slug'          => $slug,
		'meta'          => $meta,
		'breadcrumb'    => $breadcrumb,
		'hero'          => $hero,
		'journey'       => $journey,
		'spotlights'    => array(
			'terms' => isset( $_cs_sp['terms'] ) && is_array( $_cs_sp['terms'] )
				? array_values( array_filter( array_map( 'sanitize_key', $_cs_sp['terms'] ) ) )
				: array(),
		),
		'guides'        => $guides,
		'popular_posts' => $popular_posts,
		'news'          => $news,
		'regulations'   => $regulations,
		'calculators'   => $calculators,
		'resources'     => $resources,
		'faqs'          => $faqs,
		'sidebar'       => $sidebar,
		'cta_banner'    => $cta_banner,
		'newsletter'    => array(
			'icon'         => '📬',
			'title'        => defined( 'SITE_NEWSLETTER_TITLE' ) ? SITE_NEWSLETTER_TITLE : 'Stay Informed',
			'description'  => defined( 'SITE_NEWSLETTER_DESC' )  ? SITE_NEWSLETTER_DESC  : 'Get the latest guides and updates delivered to your inbox.',
			'placeholder'  => defined( 'SITE_NEWSLETTER_PH' )    ? SITE_NEWSLETTER_PH    : 'Your email address',
			'button_label' => defined( 'SITE_BTN_SUBSCRIBE' )    ? SITE_BTN_SUBSCRIBE    : 'Subscribe',
			'note'         => defined( 'SITE_NEWSLETTER_NOTE' )  ? SITE_NEWSLETTER_NOTE  : 'No spam. Unsubscribe anytime.',
		),
		'chrome'        => $chrome,
	);
}
