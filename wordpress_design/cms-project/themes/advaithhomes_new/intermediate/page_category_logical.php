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
		$items[] = array(
			'icon'        => ! empty( $post->parent_icon ) ? $post->parent_icon : '📚',
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
			$stamp   = ! empty( $item->start_date ) ? $item->start_date : '';
			$items[] = array(
				'title'    => $title,
				'date'     => $stamp ? date_i18n( 'M j, Y', strtotime( $stamp ) ) : '',
				'tag'      => 'NEWS',
				'gradient' => adn_cms_gradient( $i ),
				'url'      => ! empty( $item->link_url ) ? $item->link_url : '/news/',
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
			$items[] = array(
				'title'    => $title,
				'date'     => adn_cms_post_date( $post ),
				'tag'      => 'NEWS',
				'gradient' => adn_cms_gradient( $i ),
				'url'      => adn_cms_post_url( $post ),
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
				$items[] = array(
					'title'    => $wp_post->post_title,
					'date'     => get_the_date( 'M j, Y', $wp_post ),
					'tag'      => 'NEWS',
					'gradient' => adn_cms_gradient( $i ),
					'url'      => get_permalink( $wp_post ),
				);
			}
			wp_reset_postdata();
		}
	}

	return $items;
}

/**
 * Latest published WP posts shaped for the regulation_item card.
 * Replaces the JSON "Latest Regulations & Updates" section.
 */
function adn_category_cms_latest_posts( $limit = 4 ) {
	$items = array();
	$q = new WP_Query( array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
	if ( $q->have_posts() ) {
		foreach ( $q->posts as $wp_post ) {
			$items[] = array(
				'badge_lines' => array( 'LATEST', 'UPDATE' ),
				'title'       => $wp_post->post_title,
				'date'        => get_the_date( 'M j, Y', $wp_post ),
				'url'         => get_permalink( $wp_post ),
			);
		}
		wp_reset_postdata();
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
	$hero = array(
		'title'       => $name,
		'description' => $desc,
		'image_icon'  => $icon,
		'image_id'    => $_cs_thumb_id ?: $img,
		'trust_items' => array(
			'Independent & Unbiased',
			'Plain English Guides',
			'Updated with Latest UK Regulations',
			'Tools to Plan Better',
		),
	);

	// ── 4. Meta & Breadcrumb ──────────────────────────────────────────
	$meta = array(
		'slug'             => $slug,
		'page_title'       => $name . ' - Advaith Homes',
		'meta_description' => $desc,
	);
	$breadcrumb = array(
		array( 'label' => 'Home', 'url' => '/' ),
		array( 'label' => $name,  'url' => null ),
	);

	// ── 5. Guides: CMS articles for this parent slug ─────────────────
	$guides = array(
		'heading' => array(
			'title'      => 'Explore ' . $name . ' Guides',
			'link_label' => 'View all guides →',
			'link_url'   => '/guides/',
		),
		'items' => adn_category_cms_guides( $slug ),
	);

	// ── 7. Latest posts (replaces JSON "Regulations & Updates") ───────
	$regulations = array(
		'heading' => array(
			'title'      => 'Latest Updates',
			'link_label' => 'View all →',
			'link_url'   => '/news/',
		),
		'items' => adn_category_cms_latest_posts( 4 ),
	);

	// ── 8. Admin-managed sections (AH_Category_Settings DB model) ───────
	$_cs_journey  = isset( $_cs_all['journey'] )        && is_array( $_cs_all['journey'] )        ? $_cs_all['journey']        : array();
	$_cs_ht       = isset( $_cs_all['hot_topics'] )     && is_array( $_cs_all['hot_topics'] )     ? $_cs_all['hot_topics']     : array();
	$_cs_pp       = isset( $_cs_all['popular_posts'] )  && is_array( $_cs_all['popular_posts'] )  ? $_cs_all['popular_posts']  : array();
	$_cs_ft       = isset( $_cs_all['featured_topics'] ) && is_array( $_cs_all['featured_topics'] ) ? $_cs_all['featured_topics'] : array();
	$_cs_el       = isset( $_cs_all['external_links'] ) && is_array( $_cs_all['external_links'] ) ? $_cs_all['external_links'] : array();
	$_cs_calc     = isset( $_cs_all['calculators'] )    && is_array( $_cs_all['calculators'] )    ? $_cs_all['calculators']    : array();
	$_cs_sidebar  = isset( $_cs_all['sidebar'] )        && is_array( $_cs_all['sidebar'] )        ? $_cs_all['sidebar']        : array();
	$_cs_cta      = isset( $_cs_all['cta_banner'] )     && is_array( $_cs_all['cta_banner'] )     ? $_cs_all['cta_banner']     : array();

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
			'heading' => ! empty( $_cs_journey['heading'] ) ? (string) $_cs_journey['heading'] : 'Your ' . $name . ' Journey',
			'steps'   => $steps,
			'tip'     => $tip,
		);
	}

	// Calculators - built from selected_keys checked against the registry.
	$calculators = array();
	if ( ! empty( $_cs_calc['selected_keys'] ) && is_array( $_cs_calc['selected_keys'] )
		&& function_exists( 'adn_calculators' ) ) {
		$all_calcs    = adn_calculators();
		$calc_meta    = get_option( 'adn_calculators_meta', array() );
		$items        = array();
		foreach ( $_cs_calc['selected_keys'] as $key ) {
			$key = sanitize_key( $key );
			if ( ! isset( $all_calcs[ $key ] ) ) { continue; }
			$reg  = $all_calcs[ $key ];
			$cmeta = function_exists( 'adn_calculator_meta' ) ? adn_calculator_meta( $key ) : array();
			$items[] = array(
				'icon' => ! empty( $reg['icon'] )      ? (string) $reg['icon']        : '🧮',
				'name' => ! empty( $reg['title'] )     ? (string) $reg['title']       : $key,
				'url'  => ! empty( $cmeta['card_url'] ) ? (string) $cmeta['card_url'] : home_url( '/calculators/?calc=' . rawurlencode( $key ) ),
			);
		}
		if ( ! empty( $items ) ) {
			$calculators = array(
				'heading' => array(
					'title'      => ! empty( $_cs_calc['heading'] ) ? (string) $_cs_calc['heading'] : 'Calculators for ' . $name,
					'link_label' => 'View all calculators →',
					'link_url'   => '/calculators/',
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
				'heading' => 'Quick Tools',
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
				'heading'  => ! empty( $_cs_ht['heading'] )        ? (string) $_cs_ht['heading']        : '🔥 Hot Topics',
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
				'heading'  => ! empty( $_cs_sidebar['expert_heading'] )   ? (string) $_cs_sidebar['expert_heading']   : 'Need Expert Help?',
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
				'heading' => ! empty( $_cs_ft['heading'] ) ? (string) $_cs_ft['heading'] : 'Browse Topics',
				'items'   => $ft_items,
			);
		}
	}

	// Sidebar external links (manually entered rows in admin Content tab).
	if ( ! empty( $_cs_el['items'] ) && is_array( $_cs_el['items'] ) ) {
		$el_items = array();
		foreach ( $_cs_el['items'] as $l ) {
			if ( empty( $l['title'] ) && empty( $l['url'] ) ) { continue; }
			$el_items[] = array(
				'icon'  => ! empty( $l['icon'] )  ? (string) $l['icon']  : '',
				'title' => ! empty( $l['title'] ) ? (string) $l['title'] : '',
				'url'   => ! empty( $l['url'] )   ? (string) $l['url']   : '#',
				'desc'  => ! empty( $l['desc'] )  ? (string) $l['desc']  : '',
			);
		}
		if ( ! empty( $el_items ) ) {
			$sidebar['external_links'] = array(
				'heading' => ! empty( $_cs_el['heading'] ) ? (string) $_cs_el['heading'] : 'Useful Links',
				'items'   => $el_items,
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
				'heading' => ! empty( $calculators['heading']['title'] ) ? (string) $calculators['heading']['title'] : 'Related Calculators',
				'items'   => $_calc_tools,
				'cta'     => array( 'label' => 'All Calculators →', 'url' => '/calculators/' ),
			);
		}
	}

	// Sidebar: expert help — fallback to global page settings when not set in admin.
	if ( empty( $sidebar['expert_help'] ) ) {
		$_eh_pg = get_option( 'adn_calculators_page', array() );
		$sidebar['expert_help'] = array(
			'heading'  => ! empty( $_cs_sidebar['expert_heading'] )   ? (string) $_cs_sidebar['expert_heading']   : ( ! empty( $_eh_pg['sidebar_help_title'] ) ? (string) $_eh_pg['sidebar_help_title'] : 'Need Expert Help?' ),
			'subtitle' => ! empty( $_cs_sidebar['expert_subtitle'] )  ? (string) $_cs_sidebar['expert_subtitle']  : ( ! empty( $_eh_pg['sidebar_help_text'] )  ? (string) $_eh_pg['sidebar_help_text']  : 'Speak to one of our property experts today.' ),
			'experts'  => array(),
			'cta'      => array(
				'label' => ! empty( $_cs_sidebar['expert_cta_label'] ) ? (string) $_cs_sidebar['expert_cta_label'] : ( ! empty( $_eh_pg['sidebar_help_btn_label'] ) ? (string) $_eh_pg['sidebar_help_btn_label'] : 'Ask an Expert' ),
				'url'   => ! empty( $_cs_sidebar['expert_cta_url'] )   ? (string) $_cs_sidebar['expert_cta_url']   : ( ! empty( $_eh_pg['sidebar_help_btn_url'] )   ? (string) $_eh_pg['sidebar_help_btn_url']   : '/ask-an-expert/' ),
			),
		);
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
		$pp_cards = array();
		foreach ( $_cs_pp['items'] as $i => $item ) {
			$pid     = ! empty( $item['post_id'] ) ? (int) $item['post_id'] : 0;
			if ( ! $pid ) { continue; }
			$wp_post = get_post( $pid );
			if ( ! $wp_post || 'publish' !== $wp_post->post_status ) { continue; }
			$excerpt   = $wp_post->post_excerpt ? $wp_post->post_excerpt : $wp_post->post_content;
			$pp_cards[] = array(
				'icon'        => '📌',
				'gradient'    => adn_cms_gradient( $i ),
				'category'    => 'Guide',
				'title'       => $wp_post->post_title,
				'description' => wp_trim_words( $excerpt, 18, '…' ),
				'read_more'   => 'Read More →',
				'url'         => get_permalink( $wp_post ),
			);
			if ( count( $pp_cards ) >= 6 ) { break; }
		}
		if ( ! empty( $pp_cards ) ) {
			$popular_posts = array(
				'heading' => array(
					'title'      => ! empty( $_cs_pp['heading'] ) ? (string) $_cs_pp['heading'] : 'Popular Guides',
					'link_label' => '',
					'link_url'   => '',
				),
				'items' => $pp_cards,
			);
		}
	}

	// News for main content area (4 items).
	$_main_news_items = array();
	foreach ( array_slice( adn_category_cms_news( 4 ), 0, 4 ) as $n ) {
		$_main_news_items[] = array(
			'title'    => $n['title'],
			'url'      => $n['url'],
			'date'     => $n['date'],
			'gradient' => $n['gradient'],
		);
	}
	$news = array(
		'heading' => array(
			'title'      => 'Latest Property News',
			'link_label' => 'View all news →',
			'link_url'   => '/news/',
		),
		'items' => $_main_news_items,
	);

	return array(
		'slug'          => $slug,
		'meta'          => $meta,
		'breadcrumb'    => $breadcrumb,
		'hero'          => $hero,
		'journey'       => $journey,
		'guides'        => $guides,
		'popular_posts' => $popular_posts,
		'news'          => $news,
		'regulations'   => $regulations,
		'calculators'   => $calculators,
		'sidebar'       => $sidebar,
		'cta_banner'    => $cta_banner,
		'chrome'        => $chrome,
	);
}
