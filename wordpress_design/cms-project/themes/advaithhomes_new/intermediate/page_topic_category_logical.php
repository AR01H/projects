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

	$per_page = defined( 'ADN_TOPIC_ARTICLES_PER_PAGE' ) ? (int) ADN_TOPIC_ARTICLES_PER_PAGE : 12;
	$paged    = max( 1, isset( $_GET['paged'] ) ? (int) $_GET['paged'] : 1 ); // phpcs:ignore WordPress.Security.NonceVerification

	$ctx = array(
		'chrome'             => is_array( $chrome ) ? $chrome : array(),
		'slug'               => $slug,
		'term'               => null,
		'parent'             => null,
		'hero'               => array(),
		'breadcrumb'         => array(),
		'search'             => array( 'query' => '', 'base_url' => '' ),
		'articles'           => array(),
		'pagination'         => array(),
		'related_categories' => array(),
		'highlight_posts'    => array(),
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
			? ( ! empty( $parent->icon_emoji ) ? adn_icon( $parent->icon_emoji ) . ' ' : '' ) . esc_html( $parent->name )
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

	// ── Search (category-scoped) ─────────────────────────────────────────────────
	$search_q = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$ctx['search'] = array(
		'query'    => $search_q,
		'base_url' => home_url( '/' . $slug . '/' ),
	);

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

		// Apply category-scoped search filter when a query is present.
		if ( $search_q !== '' && ! empty( $cms_posts ) ) {
			$sq = strtolower( $search_q );
			$cms_posts = array_values( array_filter( $cms_posts, function( $p ) use ( $sq ) {
				$t = strtolower( isset( $p->title )   ? $p->title   : ( isset( $p->post_title )   ? $p->post_title   : '' ) );
				$e = strtolower( isset( $p->excerpt ) ? $p->excerpt : ( isset( $p->post_excerpt ) ? $p->post_excerpt : '' ) );
				return ( strpos( $t, $sq ) !== false || strpos( $e, $sq ) !== false );
			} ) );
		}
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
					'icon'      => ! empty( $term->icon_emoji ) ? $term->icon_emoji : '🏡',
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
			if ( $search_q !== '' ) {
				$q_args['s'] = $search_q;
			}

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
						'icon'      => ! empty( $term->icon_emoji ) ? $term->icon_emoji : '🏡',
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

	// Sidebar: latest updates for this category (first 4 articles, used instead of global news).
	$_sb_updates = array();
	foreach ( array_slice( $articles, 0, 4 ) as $_ua ) {
		$_ub_label = isset( $_ua['title'] ) ? (string) $_ua['title'] : '';
		if ( '' === $_ub_label ) { continue; }
		$_sb_updates[] = array(
			'label'     => $_ub_label,
			'url'       => isset( $_ua['url'] )       ? (string) $_ua['url']       : '',
			'thumbnail' => isset( $_ua['thumbnail'] ) ? (string) $_ua['thumbnail'] : '',
			'meta'      => isset( $_ua['date'] )      ? (string) $_ua['date']      : '',
		);
	}
	$ctx['sidebar']['latest_updates'] = $_sb_updates;

	$ctx['pagination'] = array(
		'current'  => $paged,
		'total'    => $total_pages,
		'base_url' => home_url( '/' . $slug . '/' ),
	);

	// ── Related categories (sibling sub-terms within same parent) ───────────────
	// Show other topics that belong to the same parent, not other parent areas.
	$related  = array();
	$tax_t    = $wpdb->prefix . 'ah_taxonomies';
	$types_t  = $wpdb->prefix . 'ah_taxonomy_types';

	if ( $parent && (int) $term->id ) {
		if ( ! empty( $term->parent_term_id ) ) {
			$sibs = $wpdb->get_results( $wpdb->prepare(
				"SELECT t.id, t.name, t.slug, t.description, t.icon_emoji, t.image_id
				 FROM `{$tax_t}` t
				 LEFT JOIN `{$types_t}` tt ON tt.id = t.type_id
				 WHERE t.parent_term_id = %d AND t.id != %d AND t.status = 'active'
				   AND (tt.slug IS NULL OR tt.slug != 'glossary')
				 ORDER BY t.sort_order ASC, t.name ASC LIMIT 6",
				(int) $term->parent_term_id, (int) $term->id
			) ) ?: array();
		} else {
			$sibs = $wpdb->get_results( $wpdb->prepare(
				"SELECT t.id, t.name, t.slug, t.description, t.icon_emoji, t.image_id
				 FROM `{$tax_t}` t
				 LEFT JOIN `{$types_t}` tt ON tt.id = t.type_id
				 WHERE t.parent_id = %d AND t.id != %d AND t.status = 'active'
				   AND (tt.slug IS NULL OR tt.slug != 'glossary')
				 ORDER BY t.sort_order ASC, t.name ASC LIMIT 6",
				(int) $parent->id, (int) $term->id
			) ) ?: array();
		}

		$_seen_rel = array();
		foreach ( $sibs as $i => $sib ) {
			if ( isset( $_seen_rel[ $sib->slug ] ) ) { continue; }
			$_seen_rel[ $sib->slug ] = true;
			$_rel_img = '';
			if ( ! empty( $sib->image_id ) ) {
				$_t = wp_get_attachment_image_url( (int) $sib->image_id, 'medium' );
				$_rel_img = $_t ? (string) $_t : '';
			}
			$related[] = array(
				'icon'        => ! empty( $sib->icon_emoji ) ? $sib->icon_emoji : '📚',
				'gradient'    => adn_cms_gradient( $i + 1 ),
				'image'       => $_rel_img,
				'parent_name' => '',
				'category'    => '',
				'title'       => (string) $sib->name,
				'description' => ! empty( $sib->description ) ? (string) $sib->description : '',
				'read_more'   => adn_term( 'content.read_more', 'Explore' ),
				'url'         => home_url( '/' . trim( $sib->slug, '/' ) . '/' ),
			);
		}
	}
	$ctx['related_categories'] = $related;

	// ── Featured / Popular / Suggested posts for this category ──────────────────
	$_hl_panels = array();
	$ct_table   = $wpdb->prefix . 'ah_content_taxonomies';

	if ( $term ) {
		$term_post_ids = $wpdb->get_col( $wpdb->prepare(
			"SELECT object_id FROM `{$ct_table}` WHERE object_type = 'wp_post' AND taxonomy_id = %d",
			(int) $term->id
		) );

		if ( ! empty( $term_post_ids ) ) {
			$_post_ids  = array_map( 'intval', $term_post_ids );
			$_flag_defs = array(
				'featured'  => array( 'meta_key' => '_ah_is_featured',  'heading' => '⭐ Featured',  'fa' => 'fa-star' ),
				'popular'   => array( 'meta_key' => '_ah_is_popular',   'heading' => '🔥 Popular',   'fa' => 'fa-fire' ),
				'suggested' => array( 'meta_key' => '_ah_is_suggested', 'heading' => '💡 Suggested', 'fa' => 'fa-lightbulb' ),
			);

			foreach ( $_flag_defs as $_fkey => $_fdef ) {
				$_fq = new WP_Query( array(
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'post__in'       => $_post_ids,
					'posts_per_page' => -1,
					'orderby'        => 'date',
					'order'          => 'DESC',
					'meta_query'     => array(
						array( 'key' => $_fdef['meta_key'], 'value' => '1', 'compare' => '=' ),
					),
				) );

				if ( ! $_fq->have_posts() ) {
					wp_reset_postdata();
					continue;
				}

				$_items = array();
				foreach ( $_fq->posts as $_fp ) {
					$_fp_id    = (int) $_fp->ID;
					$_thumb_id = get_post_thumbnail_id( $_fp_id );
					$_thumb    = $_thumb_id ? ( wp_get_attachment_image_url( $_thumb_id, 'thumbnail' ) ?: '' ) : '';
					$_items[]  = array(
						'icon'      => $_fdef['fa'],
						'title'     => $_fp->post_title,
						'text'      => $_fp->post_title,
						'label'     => $_fp->post_title,
						'date'      => get_the_date( 'M j, Y', $_fp_id ),
						'meta'      => get_the_date( 'M j, Y', $_fp_id ),
						'thumbnail' => $_thumb,
						'url'       => get_permalink( $_fp_id ),
					);
				}
				wp_reset_postdata();

				$_hl_panels[ $_fkey ] = array(
					'heading'  => $_fdef['heading'],
					'fa_icon'  => $_fdef['fa'],
					'items'    => $_items,
					'view_all' => array(),
				);
			}
		}
	}

	// ── Sidebar ───────────────────────────────────────────────────────────────────
	$sidebar = array();
	$tax_t   = $wpdb->prefix . 'ah_taxonomies';

	// Sidebar topic navigation - sub-categories of the same parent term only.
	$topic_items = array();

	if ( $parent ) {
		// Exclude Glossary-type terms - they are definitions, not navigable topic pages.
		if ( ! empty( $term->parent_term_id ) ) {
			$all_sibs = $wpdb->get_results( $wpdb->prepare(
				"SELECT t.id, t.name, t.slug, t.icon_emoji, t.image_id
				 FROM `{$tax_t}` t
				 LEFT JOIN `{$types_t}` tt ON tt.id = t.type_id
				 WHERE t.parent_term_id = %d AND t.id != %d AND t.status = 'active'
				   AND (tt.slug IS NULL OR tt.slug != 'glossary')
				 ORDER BY t.sort_order ASC, t.name ASC",
				(int) $term->parent_term_id, (int) $term->id
			) ) ?: array();
		} else {
			$all_sibs = $wpdb->get_results( $wpdb->prepare(
				"SELECT t.id, t.name, t.slug, t.icon_emoji, t.image_id
				 FROM `{$tax_t}` t
				 LEFT JOIN `{$types_t}` tt ON tt.id = t.type_id
				 WHERE t.parent_id = %d AND t.id != %d AND t.status = 'active'
				   AND (tt.slug IS NULL OR tt.slug != 'glossary')
				 ORDER BY t.sort_order ASC, t.name ASC",
				(int) $parent->id, (int) $term->id
			) ) ?: array();
		}

		$_seen_slugs = array();
		foreach ( $all_sibs as $sib ) {
			if ( isset( $_seen_slugs[ $sib->slug ] ) ) { continue; }
			$_seen_slugs[ $sib->slug ] = true;
			$_sb_thumb = '';
			if ( ! empty( $sib->image_id ) ) {
				$_t = wp_get_attachment_image_url( (int) $sib->image_id, 'thumbnail' );
				$_sb_thumb = $_t ? (string) $_t : '';
			}
			$topic_items[] = array(
				'icon'      => ! empty( $sib->icon_emoji ) ? $sib->icon_emoji : '📚',
				'label'     => $sib->name,
				'url'       => home_url( '/' . trim( $sib->slug, '/' ) . '/' ),
				'thumbnail' => $_sb_thumb,
			);
		}
	}

	if ( ! empty( $topic_items ) ) {
		$sidebar['buying_topics'] = array(
			'heading'  => 'Explore ' . $parent_label,
			'items'    => $topic_items,
			'view_all' => $parent ? array(
				'label' => 'View all →',
				'url'   => home_url( '/' . trim( $parent->slug, '/' ) . '/' ),
			) : array(),
		);
	}

	// Quick tools - top calculators as sidebar links, filtered by parent term.
	if ( function_exists( 'adn_get_parent_term_calculator_cards' ) ) {
		$parent_slug = $parent ? ( isset( $parent->slug ) ? (string) $parent->slug : '' ) : $slug;
		$calc_links = array();
		foreach ( adn_get_parent_term_calculator_cards( $parent_slug, 5 ) as $card ) {
			$calc_links[] = array(
				'icon'  => $card['icon'],
				'label' => $card['label'],
				'url'   => $card['url'],
			);
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

	if ( ! empty( $_hl_panels ) ) {
		$ctx['highlight_posts'] = $_hl_panels;
	}

	// ── Popular calculators (full section below fold) ─────────────────────────────
	$calc_items = array();
	if ( function_exists( 'adn_get_parent_term_calculator_cards' ) ) {
		$parent_slug = $parent ? ( isset( $parent->slug ) ? (string) $parent->slug : '' ) : $slug;
		foreach ( adn_get_parent_term_calculator_cards( $parent_slug, 7 ) as $card ) {
			$calc_items[] = array(
				'icon'      => $card['icon'],
				'title'     => $card['label'],
				'desc'      => $card['desc'] ?? '',
				'url'       => $card['url'],
				'thumbnail' => $card['thumbnail'],
				'highlight' => $card['highlight'],
			);
		}
	}
	if ( ! empty( $calc_items ) ) {
		$ctx['calculators'] = array(
			'heading' => array(
				'title'      => $parent_label . ' ' . SITE_TOOLS_PLURAL,
				'link_label' => 'View all →',
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
