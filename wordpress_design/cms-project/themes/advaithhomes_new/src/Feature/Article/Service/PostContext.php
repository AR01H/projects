<?php
namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

class PostContext {

	public static function getContext() {
		global $post;

		$post_id   = isset( $post->ID ) ? (int) $post->ID : 0;
		$cache_key = 'post_context_' . $post_id;
		if ( class_exists( 'ADN_Cache' ) ) {
			$cached = \ADN_Cache::get( $cache_key, 'posts' );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		$sidebar = function_exists( 'adn_service_post_sidebar_data' ) ? adn_service_post_sidebar_data() : array();
		$chrome  = function_exists( 'adn_service_site_chrome' )       ? adn_service_site_chrome()       : array();

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

		$cats         = get_the_category( $post->ID );
		$category_tag = ! empty( $cats ) ? $cats[0]->name : '';
		$custom_tag   = (string) get_post_meta( $post->ID, '_adn_category_tag', true );
		if ( '' !== $custom_tag ) {
			$category_tag = $custom_tag;
		}

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

		$author_name = get_the_author_meta( 'display_name' );
		if ( empty( $author_name ) ) {
			$author_name = defined( 'COMPANY_NAME' ) ? COMPANY_NAME . ' Team' : SITE_EXPERT_NOUN . 's';
		}

		$manual_guides = array();
		$related_content = array();
		if ( class_exists( 'AH_Related_Links_Model' ) ) {
			$related_model   = new \AH_Related_Links_Model();
			$related_rows    = $related_model->get_for( 'wp_post', $post->ID, array( 'only_active' => true ) );
			$link_types      = \AH_Related_Links_Model::link_types();
			foreach ( $related_rows as $row ) {
				$container = $row->container ?: 'related';
				$norm_container = strtolower( trim( $container ) );

				$is_guides_container = empty( $row->container ) || 'related' === $norm_container || 'related guides' === $norm_container || 'guides' === $norm_container || 'related_guides' === $norm_container;

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

		$latest_news = array();
		if ( function_exists( 'adn_cms_newsbar_items' ) ) {
			foreach ( adn_cms_newsbar_items( 3 ) as $nb ) {
				if ( empty( $nb->text ) ) { continue; }
				$_stamp      = ! empty( $nb->start_date ) ? $nb->start_date : ( isset( $nb->created_at ) ? $nb->created_at : '' );
				$_thumb      = '';
				if ( ! empty( $nb->image_id ) ) {
					$_tu = wp_get_attachment_image_url( (int) $nb->image_id, 'thumbnail' );
					if ( $_tu ) { $_thumb = (string) $_tu; }
				}
				$latest_news[] = array(
					'icon'          => '📰',
					'title'         => (string) $nb->text,
					'date'          => $_stamp ? date_i18n( 'M j, Y', strtotime( $_stamp ) ) : '',
					'url'           => ! empty( $nb->link_url ) ? (string) $nb->link_url : home_url( SITE_NEWS_URL ),
					'thumbnail_url' => $_thumb,
				);
			}
		}

		$thumbnail_url = get_the_post_thumbnail_url( null, 'large' );
		$default_img   = get_template_directory_uri() . THEME_DEFAULT_HERO_IMG;
		$hero_image    = adn_versioned_url( $thumbnail_url ?: $default_img );

		$cms_terms = array();
		if ( class_exists( 'AH_Content_Taxonomy_Model' ) ) {
			$_ct_model = new \AH_Content_Taxonomy_Model();
			foreach ( $_ct_model->get_terms( 'wp_post', $post->ID ) as $_t ) {
				$slug = isset( $_t->slug ) && ! empty( $_t->slug ) ? $_t->slug : sanitize_title( $_t->name );
				$cms_terms[] = array(
					'name'  => (string) $_t->name,
					'url'   => home_url( '/' . $slug . '/' ),
					'type'  => (string) ( $_t->type_name ?? '' ),
					'emoji' => (string) ( $_t->icon_emoji ?? '' ),
					'color' => (string) ( $_t->color ?? '' ),
				);
			}
		}

		$hl_raw          = get_post_meta( $post->ID, '_ah_highlight_links', true );
		$highlight_links = $hl_raw ? json_decode( $hl_raw, true ) : array();
		if ( ! is_array( $highlight_links ) ) {
			$highlight_links = array();
		}

		$read_time = (string) get_post_meta( $post->ID, '_adn_read_time', true );

		$expert_contact = array(
			'experts' => function_exists( 'adn_service_ask_expert_data' ) ? adn_service_ask_expert_data() : array(),
			'contact' => function_exists( 'adn_service_contact_data' )    ? adn_service_contact_data()    : array(),
		);

		$ctx = array(
			'breadcrumb'     => $breadcrumb,
			'article'        => array(
				'category_tag' => $category_tag,
				'title'        => get_the_title(),
				'description'  => get_the_excerpt(),
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
			'latest_news'    => $latest_news,
			'cms_terms'       => $cms_terms,
			'highlight_links' => $highlight_links,
			'related_content' => $related_content,
			'sidebar'        => $sidebar,
			'chrome'         => $chrome,
			'expert_contact' => $expert_contact,
		);

		if ( class_exists( 'ADN_Cache' ) ) {
			\ADN_Cache::set( $cache_key, $ctx, 'posts', get_option( 'ah_cache_expiry', 3600 ) );
		}
		return $ctx;
	}
}
