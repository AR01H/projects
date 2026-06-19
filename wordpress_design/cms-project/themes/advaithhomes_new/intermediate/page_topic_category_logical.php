<?php
/**
 * intermediate/page_topic_category_logical.php
 *
 * Data layer for the topic/category listing page (page-topic_category_guide.php).
 * Reads the taxonomy term from `wp_ah_taxonomies` by slug, loads its parent info,
 * fetches linked WP posts with pagination, sibling category terms, latest news and
 * popular calculators.
 *
 * RULE: No markup here. All data normalised so the template never crashes on missing keys.
 */

defined( 'ABSPATH' ) || exit;

function adn_topic_category_get_context() {
	$slug   = sanitize_key( (string) get_query_var( 'adn_guide_term_slug', '' ) );
	$chrome = adn_service_site_chrome();

	$per_page = 12;
	$paged    = max( 1, isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1 ); // phpcs:ignore WordPress.Security.NonceVerification

	$ctx = array(
		'chrome'             => is_array( $chrome ) ? $chrome : array(),
		'slug'               => $slug,
		'term'               => null,
		'parent'             => null,
		'hero'               => array(),
		'breadcrumb'         => array(),
		'articles'           => array(),
		'pagination'         => array(),
		'related_categories' => array(),
		'sidebar'            => array(),
		'news'               => array( 'heading' => array(), 'items' => array() ),
		'calculators'        => array( 'heading' => array(), 'items' => array() ),
		'cta_help'           => array(),
		'newsletter'         => array(
			'icon'         => '📬',
			'title'        => defined( 'SITE_NEWSLETTER_TITLE' ) ? SITE_NEWSLETTER_TITLE : 'Stay Informed',
			'description'  => defined( 'SITE_NEWSLETTER_DESC' )  ? SITE_NEWSLETTER_DESC  : 'Get the latest guides and updates delivered to your inbox.',
			'placeholder'  => defined( 'SITE_NEWSLETTER_PH' )    ? SITE_NEWSLETTER_PH    : 'Your email address',
			'button_label' => defined( 'SITE_BTN_SUBSCRIBE' )    ? SITE_BTN_SUBSCRIBE    : 'Subscribe',
			'note'         => defined( 'SITE_NEWSLETTER_NOTE' )  ? SITE_NEWSLETTER_NOTE  : 'No spam. Unsubscribe anytime.',
		),
	);

	if ( '' === $slug ) {
		return $ctx;
	}

	if ( ! function_exists( 'adn_cms_taxonomy_term_by_slug' ) ) {
		return $ctx;
	}

	$term = adn_cms_taxonomy_term_by_slug( $slug );
	if ( ! $term ) {
		return $ctx;
	}

	// Unslash name/description - data may have been inserted with addslashes().
	if ( ! empty( $term->name ) )        { $term->name        = wp_unslash( $term->name ); }
	if ( ! empty( $term->description ) ) { $term->description = wp_unslash( $term->description ); }

	$ctx['term'] = $term;

	// ── Parent term ──────────────────────────────────────────────────────────────
	global $wpdb;
	$parent = null;

	if ( ! empty( $term->parent_term_id ) ) {
		$pt_table = $wpdb->prefix . 'ah_taxonomy_parent_terms';
		$parent   = $wpdb->get_row( $wpdb->prepare(
			"SELECT id, name, slug, icon_emoji FROM `{$pt_table}` WHERE id = %d LIMIT 1",
			(int) $term->parent_term_id
		) );
	}
	if ( ! $parent && ! empty( $term->parent_id ) ) {
		$tax_table = $wpdb->prefix . 'ah_taxonomies';
		$parent    = $wpdb->get_row( $wpdb->prepare(
			"SELECT id, name, slug, icon_emoji FROM `{$tax_table}` WHERE id = %d LIMIT 1",
			(int) $term->parent_id
		) );
	}
	$ctx['parent'] = $parent;

	// ── Hero ─────────────────────────────────────────────────────────────────────
	$_cs_all      = class_exists( 'AH_Category_Settings' ) ? AH_Category_Settings::get_all( $slug ) : array();
	$_cs_app      = isset( $_cs_all['appearance'] ) && is_array( $_cs_all['appearance'] ) ? $_cs_all['appearance'] : array();
	$_cs_thumb_id = ! empty( $_cs_app['thumbnail_id'] ) ? (int) $_cs_app['thumbnail_id'] : 0;
	$_term_img_id = ! empty( $term->image_id ) ? (int) $term->image_id : 0;

	$ctx['hero'] = array(
		'eyebrow'     => $parent
			? ( ! empty( $parent->icon_emoji ) ? $parent->icon_emoji . ' ' : '' ) . $parent->name
			: '',
		'title'       => isset( $term->name )        ? wp_unslash( (string) $term->name )        : '',
		'description' => isset( $term->description ) ? wp_unslash( (string) $term->description ) : '',
		'image_id'    => $_cs_thumb_id ?: $_term_img_id,
		'trust_items' => array(),
	);

	$parent_label = $parent && ! empty( $parent->name ) ? (string) $parent->name : SITE_DOMAIN_NOUN;

	// ── Breadcrumb ───────────────────────────────────────────────────────────────
	$breadcrumb = array( array( 'label' => PAGE_TITLE_HOME, 'url' => home_url( '/' ) ) );
	if ( $parent && ! empty( $parent->name ) ) {
		$breadcrumb[] = array(
			'label' => $parent->name,
			'url'   => home_url( '/' . trim( $parent->slug, '/' ) . '/' ),
		);
	}
	$breadcrumb[] = array( 'label' => isset( $term->name ) ? $term->name : $slug, 'url' => '' );
	$ctx['breadcrumb'] = $breadcrumb;

	// ── Articles ─────────────────────────────────────────────────────────────────
	$gradients = array(
		'guide-img-green', 'guide-img-blue', 'guide-img-brown', 'guide-img-purple',
		'guide-img-olive', 'guide-img-copper', 'guide-img-teal', 'guide-img-forest',
	);

	$articles       = array();
	$total_pages    = 1;

	// Try CMS posts first.
	if ( function_exists( 'adn_cms_posts_for_term_slug' ) ) {
		$cms_posts = adn_cms_posts_for_term_slug( $slug, $per_page * 10 );
		if ( ! empty( $cms_posts ) ) {
			$total_pages    = (int) ceil( count( $cms_posts ) / $per_page );
			$cms_page_posts = array_slice( $cms_posts, ( $paged - 1 ) * $per_page, $per_page );

			foreach ( $cms_page_posts as $i => $post ) {
				$title   = isset( $post->title )   ? (string) $post->title   : ( isset( $post->post_title )   ? (string) $post->post_title   : '' );
				$excerpt = isset( $post->excerpt ) ? (string) $post->excerpt : ( isset( $post->post_excerpt ) ? (string) $post->post_excerpt : '' );
				$post_id = isset( $post->ID )      ? (int) $post->ID         : 0;

				$thumb_id  = $post_id ? get_post_thumbnail_id( $post_id ) : 0;
				$thumb_url = $thumb_id ? ( wp_get_attachment_image_url( $thumb_id, 'medium_large' ) ?: '' ) : '';

				$word_count = $post_id ? str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) ) : 200;
				$read_mins  = max( 1, round( $word_count / 200 ) );

				$articles[] = array(
					'icon'      => ! empty( $term->icon_emoji ) ? $term->icon_emoji : '📄',
					'img_class' => $gradients[ ( (int) $i ) % count( $gradients ) ],
					'thumbnail' => $thumb_url,
					'category'  => strtoupper( isset( $term->name ) ? $term->name : '' ),
					'title'     => $title,
					'desc'      => $excerpt ?: wp_trim_words( wp_strip_all_tags( $post_id ? get_post_field( 'post_content', $post_id ) : '' ), 20 ),
					'date'      => $post_id ? get_the_date( 'M j, Y', $post_id ) : '',
					'read_time' => $read_mins . ' min read',
					'url'       => $post_id ? get_permalink( $post_id ) : '#',
				);
			}
		}
	}

	// WP_Query fallback when CMS has no linked posts.
	// Only runs when a specific WP category match is found - never queries all posts.
	if ( empty( $articles ) ) {
		$match_terms = array();

		// 1. Exact slug match.
		$wp_cat = get_category_by_slug( $slug );
		if ( $wp_cat ) {
			$match_terms[] = $slug;
		}

		// 2. Term name match (e.g. "Home Buyers' Guide" → find WP category by that name).
		if ( empty( $match_terms ) && ! empty( $term->name ) ) {
			$by_name = get_term_by( 'name', $term->name, 'category' );
			if ( $by_name ) {
				$match_terms[] = $by_name->slug;
			}
		}

		// 3. Parent slug match as last resort.
		if ( empty( $match_terms ) && $parent && ! empty( $parent->slug ) ) {
			$wp_parent_cat = get_category_by_slug( $parent->slug );
			if ( $wp_parent_cat ) {
				$match_terms[] = $parent->slug;
			}
		}

		// Only run query when a specific category was found - avoid returning ALL posts.
		if ( ! empty( $match_terms ) ) {
			$q_args = array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => $per_page,
				'paged'          => $paged,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'tax_query'      => array( array(
					'taxonomy' => 'category',
					'field'    => 'slug',
					'terms'    => $match_terms,
				) ),
			);

			$q = new WP_Query( $q_args );
			$total_pages = $q->max_num_pages ?: 1;

			if ( $q->have_posts() ) {
				foreach ( $q->posts as $i => $wp_post ) {
					$post_id   = (int) $wp_post->ID;
					$thumb_id  = get_post_thumbnail_id( $post_id );
					$thumb_url = $thumb_id ? ( wp_get_attachment_image_url( $thumb_id, 'medium_large' ) ?: '' ) : '';

					$post_cats = get_the_category( $post_id );
					$cat_name  = ! empty( $post_cats ) ? $post_cats[0]->name : $parent_label;

					$word_count = str_word_count( wp_strip_all_tags( $wp_post->post_content ) );
					$read_mins  = max( 1, round( $word_count / 200 ) );

					$articles[] = array(
						'icon'      => ! empty( $term->icon_emoji ) ? $term->icon_emoji : '📄',
						'img_class' => $gradients[ (int) $i % count( $gradients ) ],
						'thumbnail' => $thumb_url,
						'category'  => strtoupper( $cat_name ),
						'title'     => $wp_post->post_title,
						'desc'      => $wp_post->post_excerpt ?: wp_trim_words( wp_strip_all_tags( $wp_post->post_content ), 20 ),
						'date'      => get_the_date( 'M j, Y', $wp_post ),
						'read_time' => $read_mins . ' min read',
						'url'       => get_permalink( $wp_post ),
					);
				}
			}
			wp_reset_postdata();
		} // end if ( ! empty( $match_terms ) )
	} // end if ( empty( $articles ) )

	$ctx['articles']   = $articles;
	$ctx['pagination'] = array(
		'current'  => $paged,
		'total'    => $total_pages,
		'base_url' => home_url( '/' . $slug . '/' ),
	);

	// ── Related categories (sibling terms) ───────────────────────────────────────
	$related = array();
	$tax_t   = $wpdb->prefix . 'ah_taxonomies';

	if ( $parent ) {
		if ( ! empty( $term->parent_term_id ) ) {
			$sibs = $wpdb->get_results( $wpdb->prepare(
				"SELECT id, name, slug, description, icon_emoji FROM `{$tax_t}`
				 WHERE parent_term_id = %d AND id != %d AND status = 'active'
				 ORDER BY sort_order ASC, name ASC LIMIT 6",
				(int) $term->parent_term_id,
				(int) $term->id
			) ) ?: array();
		} else {
			$sibs = $wpdb->get_results( $wpdb->prepare(
				"SELECT id, name, slug, description, icon_emoji FROM `{$tax_t}`
				 WHERE parent_id = %d AND id != %d AND status = 'active'
				 ORDER BY sort_order ASC, name ASC LIMIT 6",
				(int) $parent->id,
				(int) $term->id
			) ) ?: array();
		}

		foreach ( $sibs as $i => $sib ) {
			$related[] = array(
				'icon'        => ! empty( $sib->icon_emoji )  ? $sib->icon_emoji  : '📚',
				'gradient'    => adn_cms_gradient( $i + 1 ),
				'parent_name' => $parent_label,
				'category'    => (string) $sib->name,
				'title'       => '',
				'description' => ! empty( $sib->description ) ? (string) $sib->description : '',
				'read_more'   => adn_term( 'content.read_more', 'Explore →' ),
				'url'         => home_url( '/' . trim( $sib->slug, '/' ) . '/' ),
			);
		}
	}
	$ctx['related_categories'] = $related;

	// ── Sidebar ───────────────────────────────────────────────────────────────────
	$sidebar = array();

	// Buying/parent topic types - all sibling terms including current, for navigation.
	$topic_items = array();
	if ( $parent ) {
		if ( ! empty( $term->parent_term_id ) ) {
			$all_sibs = $wpdb->get_results( $wpdb->prepare(
				"SELECT id, name, slug, icon_emoji FROM `{$tax_t}`
				 WHERE parent_term_id = %d AND status = 'active'
				 ORDER BY sort_order ASC, name ASC",
				(int) $term->parent_term_id
			) ) ?: array();
		} else {
			$all_sibs = $wpdb->get_results( $wpdb->prepare(
				"SELECT id, name, slug, icon_emoji FROM `{$tax_t}`
				 WHERE parent_id = %d AND status = 'active'
				 ORDER BY sort_order ASC, name ASC",
				(int) $parent->id
			) ) ?: array();
		}

		foreach ( $all_sibs as $sib ) {
			$topic_items[] = array(
				'icon'      => ! empty( $sib->icon_emoji ) ? $sib->icon_emoji : '📚',
				'label'     => $sib->name,
				'url'       => home_url( '/' . trim( $sib->slug, '/' ) . '/' ),
				'is_active' => (int) $sib->id === (int) $term->id,
			);
		}
	}

	if ( ! empty( $topic_items ) ) {
		$sidebar['buying_topics'] = array(
			'heading'  => sprintf( adn_term( 'category_page.explore_guides_title', 'Explore %s %s' ), $parent_label, '' ),
			'items'    => $topic_items,
			'view_all' => $parent ? array(
				'label' => 'View all ' . $parent_label . ' guides →',
				'url'   => home_url( '/' . trim( $parent->slug, '/' ) . '/' ),
			) : array(),
		);
	}

	// Quick tools - top 4 calculators as sidebar links.
	if ( function_exists( 'adn_calculators' ) ) {
		$all_tools  = adn_calculators();
		$meta_all   = get_option( 'adn_calculators_meta', array() );
		$calc_links = array();
		foreach ( $all_tools as $ckey => $creg ) {
			$cmeta = isset( $meta_all[ $ckey ] ) && is_array( $meta_all[ $ckey ] ) ? $meta_all[ $ckey ] : array();
			if ( array_key_exists( 'enabled', $cmeta ) && empty( $cmeta['enabled'] ) ) { continue; }
			if ( ! empty( $cmeta['hidden_from_listing'] ) ) { continue; }
			$calc_links[] = array(
				'icon'  => ! empty( $creg['icon'] ) ? (string) $creg['icon'] : '🧮',
				'label' => ! empty( $cmeta['label'] ) ? (string) $cmeta['label'] : ( ! empty( $creg['title'] ) ? (string) $creg['title'] : $ckey ),
				'url'   => ! empty( $cmeta['card_url'] ) ? (string) $cmeta['card_url'] : home_url( '/?ah_calc_page=' . rawurlencode( $ckey ) ),
			);
			if ( count( $calc_links ) >= 5 ) { break; }
		}
		if ( ! empty( $calc_links ) ) {
			$sidebar['quick_tools'] = array(
				'heading' => $parent_label . ' ' . SITE_TOOLS_PLURAL,
				'items'   => $calc_links,
				'cta'     => array( 'label' => 'All ' . strtolower( SITE_TOOLS_PLURAL ) . ' →', 'url' => home_url( SITE_CALCULATORS_URL ) ),
			);
		}
	}

	// Expert help - from global calculator page option.
	$_eh = get_option( 'adn_calculators_page', array() );
	$sidebar['expert_help'] = array(
		'heading'  => ! empty( $_eh['sidebar_help_title'] )     ? $_eh['sidebar_help_title']     : adn_term( 'sidebar.expert_help_heading',  'Need Expert Help?' ),
		'subtitle' => ! empty( $_eh['sidebar_help_text'] )      ? $_eh['sidebar_help_text']      : adn_term( 'sidebar.expert_help_subtitle', 'Get personalised guidance from our experts.' ),
		'experts'  => array(),
		'cta'      => array(
			'label' => ! empty( $_eh['sidebar_help_btn_label'] ) ? $_eh['sidebar_help_btn_label'] : adn_term( 'sidebar.expert_help_cta', 'Talk to an Expert' ),
			'url'   => ! empty( $_eh['sidebar_help_btn_url'] )   ? $_eh['sidebar_help_btn_url']   : home_url( SITE_CONTACT_URL ),
		),
	);

	$ctx['sidebar'] = $sidebar;

	// ── Latest news ──────────────────────────────────────────────────────────────
	$news_items = array();

	if ( function_exists( 'adn_cms_newsbar_items' ) ) {
		foreach ( adn_cms_newsbar_items( 3 ) as $i => $nitem ) {
			$ntitle = isset( $nitem->text ) ? $nitem->text : '';
			if ( '' === $ntitle ) { continue; }
			$stamp        = ! empty( $nitem->start_date ) ? $nitem->start_date : '';
			$news_items[] = array(
				'title'       => $ntitle,
				'description' => ! empty( $nitem->content ) ? wp_strip_all_tags( (string) $nitem->content ) : '',
				'date'        => $stamp ? date_i18n( 'M j, Y', strtotime( $stamp ) ) : '',
				'tag'         => 'NEWS',
				'gradient'    => adn_cms_gradient( $i ),
				'url'         => ! empty( $nitem->link_url ) ? $nitem->link_url : '#',
			);
		}
	}

	if ( empty( $news_items ) ) {
		$q = new WP_Query( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 3,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );
		if ( $q->have_posts() ) {
			foreach ( $q->posts as $i => $wp_post ) {
				$news_items[] = array(
					'title'       => $wp_post->post_title,
					'description' => $wp_post->post_excerpt,
					'date'        => get_the_date( 'M j, Y', $wp_post ),
					'tag'         => 'NEWS',
					'gradient'    => adn_cms_gradient( $i ),
					'url'         => get_permalink( $wp_post ),
				);
			}
			wp_reset_postdata();
		}
	}

	$ctx['news'] = array(
		'heading' => array(
			'title'      => 'Latest ' . $parent_label . ' News',
			'link_label' => 'View all →',
			'link_url'   => home_url( SITE_NEWS_URL ),
		),
		'items' => $news_items,
	);

	// Sidebar news shares the same items (capped at 3).
	$ctx['sidebar']['news'] = array(
		'heading'  => defined( 'SITE_LABEL_LATEST_NEWS' ) ? SITE_LABEL_LATEST_NEWS : 'Latest News',
		'items'    => array_slice( $news_items, 0, 3 ),
		'view_all' => array( 'label' => 'All news →', 'url' => home_url( SITE_NEWS_URL ) ),
	);

	// ── Popular calculators (full section below fold) ─────────────────────────────
	$calc_items = array();
	if ( function_exists( 'adn_calculators' ) ) {
		$all_tools = adn_calculators();
		$meta_all  = get_option( 'adn_calculators_meta', array() );
		foreach ( $all_tools as $ckey => $creg ) {
			$cmeta = isset( $meta_all[ $ckey ] ) && is_array( $meta_all[ $ckey ] ) ? $meta_all[ $ckey ] : array();
			if ( array_key_exists( 'enabled', $cmeta ) && empty( $cmeta['enabled'] ) ) { continue; }
			if ( ! empty( $cmeta['hidden_from_listing'] ) ) { continue; }
			$thumb = '';
			if ( ! empty( $cmeta['thumbnail_id'] ) ) {
				$t = wp_get_attachment_image_url( (int) $cmeta['thumbnail_id'], 'thumbnail' );
				$thumb = $t ? (string) $t : '';
			}
			$calc_items[] = array(
				'icon'      => ! empty( $creg['icon'] )      ? (string) $creg['icon']      : '🧮',
				'name'      => ! empty( $cmeta['label'] )    ? (string) $cmeta['label']    : ( ! empty( $creg['title'] ) ? (string) $creg['title'] : $ckey ),
				'url'       => ! empty( $cmeta['card_url'] ) ? (string) $cmeta['card_url'] : home_url( '/?ah_calc_page=' . rawurlencode( $ckey ) ),
				'thumbnail' => $thumb,
				'highlight' => ! empty( $cmeta['highlight'] ) ? (string) $cmeta['highlight'] : '',
			);
			if ( count( $calc_items ) >= 7 ) { break; }
		}
	}
	if ( ! empty( $calc_items ) ) {
		$ctx['calculators'] = array(
			'heading' => array(
				'title'      => $parent_label . ' ' . SITE_TOOLS_PLURAL,
				'link_label' => 'View all ' . strtolower( SITE_TOOLS_PLURAL ) . ' →',
				'link_url'   => home_url( SITE_CALCULATORS_URL ),
			),
			'items' => $calc_items,
		);
	}

	// ── Help / contact CTA ────────────────────────────────────────────────────────
	$ctx['cta_help'] = array(
		'icon'        => '🏡',
		'title'       => adn_term( 'content.need_help_title', 'Need Help With' ) . ' ' . ( isset( $term->name ) ? $term->name : $parent_label ) . '?',
		'description' => adn_term( 'content.need_help_description', 'Speak to one of our expert advisors and get personalised guidance tailored to your situation.' ),
		'cta'         => array( 'label' => adn_term( 'content.need_help_cta', 'Talk to an Expert' ), 'url' => home_url( SITE_CONTACT_URL ) ),
		'trust_items' => (function(){
			$items = adn_term( 'content.trust_items', '' );
			$decoded = $items ? json_decode( $items, true ) : array();
			if ( empty( $decoded ) ) {
				$decoded = array(
					'Independent & Unbiased',
					'No hidden fees',
					'Plain English advice'
				);
			}
			return $decoded;
		})(),
	);

	return $ctx;
}
