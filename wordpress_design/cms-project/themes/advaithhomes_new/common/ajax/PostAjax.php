<?php
/**
 * Post AJAX Handlers
 *
 * Handles related articles, helpful/like counter, and post-related AJAX.
 *
 * @package Adn\Theme\Common\Ajax
 */
defined( 'ABSPATH' ) || exit;

/**
 * Post related articles AJAX - returns articles sharing the same CMS taxonomy terms.
 */
function adn_post_related_articles_ajax() {
	$post_id = absint( isset( $_POST['post_id'] ) ? $_POST['post_id'] : 0 );
	if ( ! $post_id || ! function_exists( 'adn_cms_available' ) || ! adn_cms_available() ) {
		wp_send_json_success( array( 'articles' => array() ) );
		return;
	}

	global $wpdb;
	$ct       = adn_cms_table( 'content_taxonomies' );
	$term_ids = $wpdb->get_col( $wpdb->prepare(
		"SELECT taxonomy_id FROM `{$ct}` WHERE object_type = 'wp_post' AND object_id = %d",
		$post_id
	) );
	$term_ids = array_map( 'absint', (array) $term_ids );

	$articles = array();

	if ( ! empty( $term_ids ) && function_exists( 'adn_cms_articles' ) && function_exists( 'adn_cms_post_url' ) ) {
		$pool = (array) adn_cms_articles( 40, $term_ids );
		$pool = array_values( array_filter( $pool, function ( $p ) use ( $post_id ) {
			return (int) $p->ID !== $post_id;
		} ) );
		shuffle( $pool );
		$pool = array_slice( $pool, 0, 6 );

		foreach ( $pool as $p ) {
			$thumb   = get_the_post_thumbnail_url( $p->ID, 'medium' );
			$excerpt = isset( $p->excerpt ) && '' !== $p->excerpt ? (string) $p->excerpt : (string) ( $p->content ?? '' );
			$articles[] = array(
				'title'     => isset( $p->title ) ? (string) $p->title : '',
				'url'       => adn_cms_post_url( $p ),
				'excerpt'   => wp_trim_words( wp_strip_all_tags( $excerpt ), 16, '…' ),
				'date'      => function_exists( 'adn_cms_post_date' ) ? adn_cms_post_date( $p ) : '',
				'thumbnail' => $thumb ? (string) $thumb : '',
			);
		}
	}

	wp_send_json_success( array( 'articles' => $articles ) );
}

/**
 * Post helpful / like counter AJAX
 */
function adn_post_helpful_ajax() {
	check_ajax_referer( 'adn_post_helpful', 'nonce' );

	$post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
	if ( ! $post_id || ! get_post( $post_id ) ) {
		wp_send_json_error( array( 'message' => 'Invalid post' ), 400 );
	}

	$count        = max( 0, (int) get_post_meta( $post_id, '_adn_helpful_count', true ) );
	$already      = isset( $_POST['liked'] ) && '1' === (string) $_POST['liked'];

	if ( $already ) {
		$count = max( 0, $count - 1 );
	} else {
		$count++;
	}

	update_post_meta( $post_id, '_adn_helpful_count', $count );
	wp_send_json_success( array( 'count' => $count, 'liked' => ! $already ) );
}
