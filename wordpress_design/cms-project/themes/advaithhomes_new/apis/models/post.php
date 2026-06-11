<?php
/**
 * apis/models/post.php - API Models: data shape & formatters.
 *
 * RULE: Models transform raw WP data into clean, API-ready arrays.
 *       They do NOT run queries or register hooks. Meta keys come from the
 *       ADN_META_* constants (includes/core_terms.php).
 *
 * Pattern: adn_model_<thing>( WP_Post $post ): array
 */

defined( 'ABSPATH' ) || exit;

// ═══════════════════════════════════════════════════════════════════
// A. SHARED URL BLOCK (used by every model)
// ═══════════════════════════════════════════════════════════════════

/**
 * Standard URL block: canonical permalink, optional redirect, alternate alias.
 */
function adn_model_urls( WP_Post $post ): array {
	$permalink = get_permalink( $post );
	$redirect  = get_post_meta( $post->ID, ADN_META_REDIRECT,  true ) ?: null;
	$canonical = get_post_meta( $post->ID, ADN_META_CANONICAL, true ) ?: $permalink;

	return array(
		'url'          => $permalink,
		'canonical'    => $canonical,
		'redirect_url' => $redirect,
		'has_redirect' => ! empty( $redirect ),
		'alternate'    => array(
			'url'   => get_post_meta( $post->ID, ADN_META_ALT_URL,   true ) ?: null,
			'title' => get_post_meta( $post->ID, ADN_META_ALT_TITLE, true ) ?: null,
			'slug'  => get_post_meta( $post->ID, ADN_META_ALT_SLUG,  true ) ?: null,
		),
	);
}

// ═══════════════════════════════════════════════════════════════════
// B. POST MODEL
// ═══════════════════════════════════════════════════════════════════

/**
 * Format a standard WP post as an API response object.
 */
function adn_model_post( WP_Post $post ): array {
	return array(
		'id'         => $post->ID,
		'title'      => get_the_title( $post ),
		'slug'       => $post->post_name,
		'excerpt'    => get_the_excerpt( $post ),
		'content'    => apply_filters( 'the_content', $post->post_content ),
		'date'       => get_the_date( 'c', $post ),
		'modified'   => get_the_modified_date( 'c', $post ),
		'thumbnail'  => get_the_post_thumbnail_url( $post, 'adn-thumbnail' ) ?: null,
		'author'     => get_the_author_meta( 'display_name', $post->post_author ),
		'categories' => adn_model_terms( $post->ID, 'category' ),
		'tags'       => adn_model_terms( $post->ID, 'post_tag' ),
		'urls'       => adn_model_urls( $post ),
	);
}

// ═══════════════════════════════════════════════════════════════════
// C. TAXONOMY TERMS MODEL
// ═══════════════════════════════════════════════════════════════════

/**
 * Flatten a post's taxonomy terms into a simple array.
 */
function adn_model_terms( int $post_id, string $taxonomy ): array {
	$terms = get_the_terms( $post_id, $taxonomy );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}
	return array_map(
		static function ( $t ) {
			return array(
				'id'   => $t->term_id,
				'name' => $t->name,
				'slug' => $t->slug,
				'url'  => get_term_link( $t ),
			);
		},
		$terms
	);
}

// ═══════════════════════════════════════════════════════════════════
// D. REDIRECT HELPER (call at the top of any single-item callback)
// ═══════════════════════════════════════════════════════════════════

/**
 * If a model has a redirect set, build the redirect response / perform it.
 *
 * @param array  $model   Shaped model (must contain the 'urls' block).
 * @param string $context 'rest' | 'template'
 * @param int    $status  HTTP status (default 301).
 * @return WP_REST_Response|void
 */
function adn_maybe_redirect( array $model, string $context = 'template', int $status = 301 ) {
	if ( empty( $model['urls']['has_redirect'] ) ) {
		return; // no redirect - continue normally
	}

	$destination = esc_url_raw( $model['urls']['redirect_url'] );

	if ( 'rest' === $context ) {
		$response = new WP_REST_Response( null, $status );
		$response->header( 'Location', $destination );
		return $response;
	}

	wp_redirect( $destination, $status );
	exit;
}
