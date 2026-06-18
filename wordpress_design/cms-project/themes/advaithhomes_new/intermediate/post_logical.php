<?php
/**
 * intermediate/post_logical.php
 *
 * Context builder for single.php (WordPress post template).
 *
 * Reads WP post data via template tags (must be called AFTER the_post()),
 * fires two WP_Query calls for related guides and latest news,
 * and merges in static sidebar data from data/json/post_sidebar.json.
 *
 * Post meta keys this function reads:
 *   _adn_article_icon      (string)  - emoji displayed in the article header
 *   _adn_read_time         (string)  - e.g. "12 min read"
 *   _adn_key_takeaways     (string)  - JSON-encoded array of strings
 *   _adn_category_tag      (string)  - override display category name
 *
 * RULE: No markup here - only data shaping.
 * RULE: Caller is single.php; the_post() must already have been called.
 */

defined( 'ABSPATH' ) || exit;

function adn_post_get_context() {
	global $post;

	$sidebar = function_exists( 'adn_service_post_sidebar_data' ) ? adn_service_post_sidebar_data() : array();
	$chrome  = function_exists( 'adn_service_site_chrome' )       ? adn_service_site_chrome()       : array();

	/* ── Post meta ── */
	$article_icon = (string) get_post_meta( $post->ID, '_adn_article_icon', true );
	if ( '' === $article_icon ) {
		$article_icon = SITE_BRAND_ICON;
	}
	$read_time = (string) get_post_meta( $post->ID, '_adn_read_time', true );

	$kt_raw        = get_post_meta( $post->ID, '_adn_key_takeaways', true );
	$key_takeaways = $kt_raw ? json_decode( $kt_raw, true ) : array();
	if ( ! is_array( $key_takeaways ) ) {
		$key_takeaways = array();
	}

	/* ── Category / tag ── */
	$cats         = get_the_category( $post->ID );
	$category_tag = ! empty( $cats ) ? $cats[0]->name : '';
	$custom_tag   = (string) get_post_meta( $post->ID, '_adn_category_tag', true );
	if ( '' !== $custom_tag ) {
		$category_tag = $custom_tag;
	}

	/* ── Breadcrumb ── */
	// Prefer CMS taxonomy path: Home > ParentTerm > CategoryTerm > PostTitle.
	// Falls back to WP native category when CMS tables are absent.
	$_cms_bc    = function_exists( 'adn_cms_post_breadcrumb' )
	              ? adn_cms_post_breadcrumb( $post->ID, get_the_title() )
	              : null;
	if ( $_cms_bc ) {
		$breadcrumb = $_cms_bc;
	} else {
		$breadcrumb = array( array( 'label' => PAGE_TITLE_HOME, 'url' => '/' ) );
		if ( ! empty( $cats ) ) {
			$breadcrumb[] = array(
				'label' => $cats[0]->name,
				'url'   => get_category_link( $cats[0]->term_id ),
			);
		}
		$breadcrumb[] = array( 'label' => get_the_title(), 'url' => null );
	}

	/* ── Author ── */
	$author_name = get_the_author_meta( 'display_name' );
	if ( empty( $author_name ) ) {
		$author_name = defined( 'COMPANY_NAME' ) ? COMPANY_NAME . ' Team' : SITE_EXPERT_NOUN . 's';
	}

	/* ── Related guides ── */
	$manual_guides = array();
	$related_content = array();
	if ( class_exists( 'AH_Related_Links_Model' ) ) {
		$related_model   = new AH_Related_Links_Model();
		$related_rows    = $related_model->get_for( 'wp_post', $post->ID, array( 'only_active' => true ) );
		$link_types      = AH_Related_Links_Model::link_types();
		foreach ( $related_rows as $row ) {
			$container = $row->container ?: 'related';
			$norm_container = strtolower( trim( $container ) );

			// Determine if container is empty/default or explicitly guides-related
			$is_guides_container = empty( $row->container ) || 'related' === $norm_container || 'related guides' === $norm_container || 'guides' === $norm_container || 'related_guides' === $norm_container;

			// Route to manual_guides only if it's an article under guides container, or container is explicitly guides
			if ( ( 'article' === $row->link_type && $is_guides_container ) || 'related guides' === $norm_container || 'guides' === $norm_container || 'related_guides' === $norm_container ) {
				$icon = '🏠';
				$read_time = '';
				if ( 'wp_post' === $row->target_kind && ! empty( $row->target_id ) ) {
					$icon = get_post_meta( $row->target_id, '_adn_article_icon', true ) ?: '🏠';
					$read_time = (string) get_post_meta( $row->target_id, '_adn_read_time', true );
				} else {
					$icon = $link_types[ $row->link_type ]['icon'] ?? '🏠';
				}

				$manual_guides[] = array(
					'icon'      => $icon,
					'title'     => $related_model->resolve_label( $row ),
					'read_time' => $read_time,
					'url'       => $related_model->resolve_url( $row ),
				);
			} else {
				if ( ! isset( $related_content[ $container ] ) ) {
					$related_content[ $container ] = array();
				}
				$icon = $link_types[ $row->link_type ]['icon'] ?? '🔗';
				$related_content[ $container ][] = array(
					'title' => $related_model->resolve_label( $row ),
					'url'   => $related_model->resolve_url( $row ),
					'icon'  => $icon,
				);
			}
		}
	}

	// If manual guides were added in the admin, use ONLY those. Otherwise, fall back to dynamic guides.
	if ( ! empty( $manual_guides ) ) {
		$related_guides = $manual_guides;
	} else {
		$related_guides = array();
		$rq_args        = array(
			'post_type'      => 'post',
			'posts_per_page' => 4,
			'post__not_in'   => array( $post->ID ),
			'orderby'        => 'date',
			'order'          => 'DESC',
		);
		if ( ! empty( $cats ) ) {
			$rq_args['category__in'] = array( $cats[0]->term_id );
		}
		$rq = new WP_Query( $rq_args );
		if ( $rq->have_posts() ) {
			while ( $rq->have_posts() ) {
				$rq->the_post();
				$related_guides[] = array(
					'icon'      => get_post_meta( get_the_ID(), '_adn_article_icon', true ) ?: SITE_BRAND_ICON,
					'title'     => get_the_title(),
					'read_time' => (string) get_post_meta( get_the_ID(), '_adn_read_time', true ),
					'url'       => get_permalink(),
				);
			}
			wp_reset_postdata();
		}
	}
	/* ── Latest news - most recent 3 posts ── */
	$latest_news = array();
	$nq          = new WP_Query( array(
		'post_type'      => 'post',
		'posts_per_page' => 3,
		'post__not_in'   => array( $post->ID ),
		'orderby'        => 'date',
		'order'          => 'DESC',
	) );
	if ( $nq->have_posts() ) {
		while ( $nq->have_posts() ) {
			$nq->the_post();
			$latest_news[] = array(
				'icon'          => get_post_meta( get_the_ID(), '_adn_article_icon', true ) ?: '📰',
				'title'         => get_the_title(),
				'date'          => get_the_date( 'M j, Y' ),
				'url'           => get_permalink(),
				'thumbnail_url' => get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ) ?: '',
			);
		}
		wp_reset_postdata();
	}

	/* ── Hero image - featured image or theme default ── */
	$thumbnail_url = get_the_post_thumbnail_url( null, 'large' );
	$default_img   = get_template_directory_uri() . '/assets/images/backgrounds/home_hero.jpg';
	$hero_image    = $thumbnail_url ?: $default_img;

	/* ── Sidebar Meta: Highlight Links and Related Content ── */
	$hl_raw          = get_post_meta( $post->ID, '_ah_highlight_links', true );
	$highlight_links = $hl_raw ? json_decode( $hl_raw, true ) : array();
	if ( ! is_array( $highlight_links ) ) {
		$highlight_links = array();
	}

	$read_time = (string) get_post_meta( $post->ID, '_adn_read_time', true );

	return array(
		'breadcrumb'     => $breadcrumb,
		'article'        => array(
			'category_tag' => $category_tag,
			'title'        => get_the_title(),
			'intro'        => get_the_excerpt(),
			'icon'         => $article_icon,
			'image_url'    => $hero_image,
			'date'         => get_the_date( 'F j, Y' ),
			'read_time'    => $read_time,
		),
		'key_takeaways'  => $key_takeaways,
		'author'         => array(
			'name'         => $author_name,
			'role'         => adn_term( 'content.author_role', SITE_INDUSTRY . ' Information Experts' ),
			'last_updated' => get_the_modified_date( 'F j, Y' ),
		),
		'share'          => array(
			'url'   => get_permalink(),
			'title' => get_the_title(),
		),
		'related_guides' => $related_guides,
		'latest_news'    => $latest_news,
		'highlight_links' => $highlight_links,
		'related_content' => $related_content,
		'sidebar'        => $sidebar,
		'chrome'         => $chrome,
	);
}
