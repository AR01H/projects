<?php
/**
 * Repository for topic/category listing page database queries.
 *
 * Extracts raw $wpdb queries from page_topic_category_logical.php into a
 * single, testable class. All public methods accept primitive
 * arguments and return plain objects or arrays.
 *
 * @package Adn\Theme\Repository
 */

namespace Adn\Theme\Repository;

defined( 'ABSPATH' ) || exit;

class TopicCategoryRepository {

	/**
	 * Fetch a taxonomy term by slug from ah_taxonomies.
	 */
	public function get_term_by_slug( string $slug ) {
		if ( ! function_exists( 'adn_cms_taxonomy_term_by_slug' ) ) { return null; }
		$term = adn_cms_taxonomy_term_by_slug( $slug );
		if ( $term && ! empty( $term->name ) ) { $term->name = wp_unslash( $term->name ); }
		if ( $term && ! empty( $term->description ) ) { $term->description = wp_unslash( $term->description ); }
		return $term;
	}

	/**
	 * Get parent term by ID from ah_taxonomy_parent_terms.
	 */
	public function get_parent_term( int $parent_term_id ) {
		global $wpdb;
		$pt_table = $wpdb->prefix . 'ah_taxonomy_parent_terms';
		$parent = $wpdb->get_row( $wpdb->prepare(
			"SELECT id, name, slug, icon_emoji FROM `{$pt_table}` WHERE id = %d LIMIT 1",
			$parent_term_id
		) );
		return $parent;
	}

	/**
	 * Fetch a parent term row by ID from ah_taxonomy_parent_terms.
	 */
	public function get_parent_term_by_id( int $id ): ?object {
		return $this->get_parent_term( $id );
	}

	/**
	 * Fetch a parent term row by ID from ah_taxonomies (fallback).
	 */
	public function get_parent_taxonomy_by_id( int $id ): ?object {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_taxonomies';
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT id, name, slug, icon_emoji FROM `{$table}` WHERE id = %d LIMIT 1",
			$id
		) );
	}

	/**
	 * Fetch a parent term by slug from ah_taxonomy_parent_terms.
	 */
	public function get_parent_term_by_slug( string $slug ) {
		global $wpdb;
		$pt_table = $wpdb->prefix . 'ah_taxonomy_parent_terms';
		return $wpdb->get_row( $wpdb->prepare(
			"SELECT id, name, slug, icon_emoji FROM `{$pt_table}` WHERE slug = %s LIMIT 1",
			sanitize_title( $slug )
		) );
	}

	/**
	 * Fetch articles for a taxonomy term with pagination and search.
	 *
	 * Tries CMS posts first, falls back to WP_Query by category slug.
	 *
	 * @param int    $term_id
	 * @param int    $paged
	 * @param int    $per_page
	 * @param string $search
	 * @return array  ['items' => [...], 'total' => int, 'total_pages' => int]
	 */
	public function get_articles( int $term_id, int $paged, int $per_page, string $search ): array {
		$term = $this->get_term_by_slug( '' );
		$result = array( 'items' => array(), 'total' => 0, 'total_pages' => 1 );

		// Get the term object by ID to find slug for cms_posts lookup
		global $wpdb;
		$tax_table = $wpdb->prefix . 'ah_taxonomies';
		$term_obj = $wpdb->get_row( $wpdb->prepare(
			"SELECT id, name, slug, icon_emoji FROM `{$tax_table}` WHERE id = %d LIMIT 1",
			$term_id
		) );
		if ( ! $term_obj ) { return $result; }

		$slug = (string) $term_obj->slug;
		$parent_label = SITE_DOMAIN_NOUN;

		$gradients = array(
			'guide-img-green', 'guide-img-blue', 'guide-img-brown', 'guide-img-purple',
			'guide-img-olive', 'guide-img-copper', 'guide-img-teal', 'guide-img-forest',
		);

		// Try CMS posts first.
		if ( function_exists( 'adn_cms_posts_for_term_slug' ) ) {
			$cms_posts = adn_cms_posts_for_term_slug( $slug, $per_page * 10 );

			// Apply search filter
			if ( $search !== '' && ! empty( $cms_posts ) ) {
				$sq = strtolower( $search );
				$cms_posts = array_values( array_filter( $cms_posts, function( $p ) use ( $sq ) {
					$t = strtolower( isset( $p->title )   ? $p->title   : ( isset( $p->post_title )   ? $p->post_title   : '' ) );
					$e = strtolower( isset( $p->excerpt ) ? $p->excerpt : ( isset( $p->post_excerpt ) ? $p->post_excerpt : '' ) );
					return ( strpos( $t, $sq ) !== false || strpos( $e, $sq ) !== false );
				} ) );
			}

			if ( ! empty( $cms_posts ) ) {
				$total = count( $cms_posts );
				$total_pages = (int) ceil( $total / $per_page );
				$page_posts = array_slice( $cms_posts, ( $paged - 1 ) * $per_page, $per_page );

				foreach ( $page_posts as $i => $post ) {
					$title   = isset( $post->title )   ? (string) $post->title   : ( isset( $post->post_title ) ? (string) $post->post_title : '' );
					$excerpt = isset( $post->excerpt ) ? (string) $post->excerpt : ( isset( $post->post_excerpt ) ? (string) $post->post_excerpt : '' );
					$post_id = isset( $post->ID )      ? (int) $post->ID         : 0;

					$thumb_id  = $post_id ? get_post_thumbnail_id( $post_id ) : 0;
					$thumb_url = $thumb_id ? ( wp_get_attachment_image_url( $thumb_id, 'medium_large' ) ?: '' ) : '';

					$word_count = $post_id ? str_word_count( wp_strip_all_tags( get_post_field( 'post_content', $post_id ) ) ) : 200;
					$read_mins  = max( 1, round( $word_count / 200 ) );

					$result['items'][] = array(
						'icon'      => ! empty( $term_obj->icon_emoji ) ? $term_obj->icon_emoji : '🏡',
						'img_class' => $gradients[ ( (int) $i ) % count( $gradients ) ],
						'thumbnail' => $thumb_url,
						'category'  => strtoupper( $term_obj->name ?? '' ),
						'title'     => $title,
						'desc'      => $excerpt ?: wp_trim_words( wp_strip_all_tags( $post_id ? get_post_field( 'post_content', $post_id ) : '' ), 20 ),
						'date'      => $post_id ? get_the_date( 'M j, Y', $post_id ) : '',
						'read_time' => $read_mins . ' min read',
						'url'       => $post_id ? get_permalink( $post_id ) : '#',
					);
				}
				$result['total'] = $total;
				$result['total_pages'] = $total_pages;
				return $result;
			}
		}

		// WP_Query fallback
		$match_terms = array();
		$wp_cat = get_category_by_slug( $slug );
		if ( $wp_cat ) { $match_terms[] = $slug; }

		if ( empty( $match_terms ) && ! empty( $term_obj->name ) ) {
			$by_name = get_term_by( 'name', $term_obj->name, 'category' );
			if ( $by_name ) { $match_terms[] = $by_name->slug; }
		}

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
			if ( $search !== '' ) { $q_args['s'] = $search; }

			$q = new \WP_Query( $q_args );
			$result['total_pages'] = $q->max_num_pages ?: 1;

			if ( $q->have_posts() ) {
				foreach ( $q->posts as $i => $wp_post ) {
					$post_id   = (int) $wp_post->ID;
					$thumb_id  = get_post_thumbnail_id( $post_id );
					$thumb_url = $thumb_id ? ( wp_get_attachment_image_url( $thumb_id, 'medium_large' ) ?: '' ) : '';

					$post_cats = get_the_category( $post_id );
					$cat_name  = ! empty( $post_cats ) ? $post_cats[0]->name : $parent_label;

					$word_count = str_word_count( wp_strip_all_tags( $wp_post->post_content ) );
					$read_mins  = max( 1, round( $word_count / 200 ) );

					$result['items'][] = array(
						'icon'      => ! empty( $term_obj->icon_emoji ) ? $term_obj->icon_emoji : '🏡',
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
			$result['total'] = $q->found_posts;
		}

		return $result;
	}

	/**
	 * Get sibling topics for sidebar navigation.
	 */
	public function get_sibling_topics( string $slug ): array {
		global $wpdb;
		$tax_t   = $wpdb->prefix . 'ah_taxonomies';
		$types_t = $wpdb->prefix . 'ah_taxonomy_types';

		// Find the term first
		$term = $wpdb->get_row( $wpdb->prepare(
			"SELECT id, parent_term_id, parent_id FROM `{$tax_t}` WHERE slug = %s LIMIT 1",
			$slug
		) );
		if ( ! $term ) { return array(); }

		$items = array();
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
		} elseif ( ! empty( $term->parent_id ) ) {
			$all_sibs = $wpdb->get_results( $wpdb->prepare(
				"SELECT t.id, t.name, t.slug, t.icon_emoji, t.image_id
				 FROM `{$tax_t}` t
				 LEFT JOIN `{$types_t}` tt ON tt.id = t.type_id
				 WHERE t.parent_id = %d AND t.id != %d AND t.status = 'active'
				   AND (tt.slug IS NULL OR tt.slug != 'glossary')
				 ORDER BY t.sort_order ASC, t.name ASC",
				(int) $term->parent_id, (int) $term->id
			) ) ?: array();
		} else {
			return array();
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
			$items[] = array(
				'icon'      => ! empty( $sib->icon_emoji ) ? $sib->icon_emoji : '📚',
				'label'     => $sib->name,
				'url'       => home_url( '/' . trim( $sib->slug, '/' ) . '/' ),
				'thumbnail' => $_sb_thumb,
			);
		}
		return $items;
	}

	/**
	 * Get related categories (sibling sub-terms within same parent) for the related section.
	 */
	public function get_related_categories( string $slug ): array {
		global $wpdb;
		$tax_t   = $wpdb->prefix . 'ah_taxonomies';
		$types_t = $wpdb->prefix . 'ah_taxonomy_types';

		$term = $wpdb->get_row( $wpdb->prepare(
			"SELECT id, name, slug, description, icon_emoji, image_id, parent_term_id, parent_id FROM `{$tax_t}` WHERE slug = %s LIMIT 1",
			$slug
		) );
		if ( ! $term || ! (int) $term->id ) { return array(); }

		// Find parent
		$parent = null;
		if ( ! empty( $term->parent_term_id ) ) {
			$pt_table = $wpdb->prefix . 'ah_taxonomy_parent_terms';
			$parent = $wpdb->get_row( $wpdb->prepare(
				"SELECT id, name, slug FROM `{$pt_table}` WHERE id = %d LIMIT 1",
				(int) $term->parent_term_id
			) );
		}
		if ( ! $parent && ! empty( $term->parent_id ) ) {
			$parent = $wpdb->get_row( $wpdb->prepare(
				"SELECT id, name, slug FROM `{$tax_t}` WHERE id = %d LIMIT 1",
				(int) $term->parent_id
			) );
		}
		if ( ! $parent ) { return array(); }

		$sibs = array();
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

		$related = array();
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
				'gradient'    => function_exists( 'adn_cms_gradient' ) ? adn_cms_gradient( $i + 1 ) : '',
				'image'       => $_rel_img,
				'parent_name' => '',
				'category'    => '',
				'title'       => (string) $sib->name,
				'description' => ! empty( $sib->description ) ? (string) $sib->description : '',
				'read_more'   => adn_term( 'content.read_more', 'Explore' ),
				'url'         => home_url( '/' . trim( $sib->slug, '/' ) . '/' ),
			);
		}
		return $related;
	}

	/**
	 * Get sibling terms (sub-categories) belonging to the same parent.
	 */
	public function get_sibling_terms( int $parent_term_id, int $exclude_term_id, string $column = 'parent_term_id', int $limit = 0 ): array {
		global $wpdb;
		$tax_t   = $wpdb->prefix . 'ah_taxonomies';
		$types_t = $wpdb->prefix . 'ah_taxonomy_types';

		$limit_clause = $limit > 0 ? "LIMIT {$limit}" : '';
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT t.id, t.name, t.slug, t.description, t.icon_emoji, t.image_id
			 FROM `{$tax_t}` t
			 LEFT JOIN `{$types_t}` tt ON tt.id = t.type_id
			 WHERE t.{$column} = %d AND t.id != %d AND t.status = 'active'
			   AND (tt.slug IS NULL OR tt.slug != 'glossary')
			 ORDER BY t.sort_order ASC, t.name ASC {$limit_clause}",
			$parent_term_id,
			$exclude_term_id
		) );

		return $rows ?: array();
	}

	/**
	 * Get all sibling terms for the sidebar (no limit).
	 */
	public function get_sidebar_sibling_terms( int $parent_term_id, int $exclude_term_id, string $column = 'parent_term_id' ): array {
		return $this->get_sibling_terms( $parent_term_id, $exclude_term_id, $column, 0 );
	}

	/**
	 * Get WP post IDs linked to a taxonomy term via ah_content_taxonomies.
	 */
	public function get_term_post_ids( int $taxonomy_id ): array {
		global $wpdb;
		$table = $wpdb->prefix . 'ah_content_taxonomies';
		$ids   = $wpdb->get_col( $wpdb->prepare(
			"SELECT object_id FROM `{$table}` WHERE object_type = 'wp_post' AND taxonomy_id = %d",
			$taxonomy_id
		) );
		return array_map( 'intval', (array) $ids );
	}
}
