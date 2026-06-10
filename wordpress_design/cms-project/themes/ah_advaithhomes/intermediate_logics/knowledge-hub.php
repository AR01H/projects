<?php
/**
 * Data layer for the Knowledge Hub home page.
 *
 * Source of truth (in priority order):
 *   1. `ah_khub_data` filter        - lets a DB model / plugin override anything.
 *   2. real_data/json/knowledge-hub.json - editable content file (no code change).
 *
 * This file stays thin: it loads the content, resolves relative URLs / asset
 * paths to absolute ones, and injects the only truly dynamic block (Latest
 * Articles, pulled live from published posts). All copy lives in the JSON file
 * so it can be changed without touching PHP, and the components stay generic.
 */
defined( 'ABSPATH' ) || exit;

/* ── Load editable content ────────────────────────────────────────────────── */
$data = class_exists( 'AH_Real_Loader' ) ? AH_Real_Loader::json( 'knowledge-hub' ) : array();
if ( ! is_array( $data ) ) {
	$data = array();
}

/* ── Resolve relative links + asset paths to absolute URLs ────────────────── */
$resolve = static function ( array &$node ) use ( &$resolve ): void {
	foreach ( $node as $key => &$val ) {
		if ( is_array( $val ) ) {
			$resolve( $val );
		} elseif ( 'url' === $key && is_string( $val ) && isset( $val[0] ) && '/' === $val[0] ) {
			$val = home_url( $val );
		} elseif ( 'image' === $key && is_string( $val ) && 0 === strpos( $val, '/assets' ) ) {
			$val = get_template_directory_uri() . $val;
		}
	}
	unset( $val );
};
$resolve( $data );

/* ── Point audience cards at their parent-term group (Buying → only Buying) ── */
if ( ! empty( $data['audience'] ) && is_array( $data['audience'] ) && function_exists( 'ah_parent_term_by_hint' ) ) {
	foreach ( $data['audience'] as &$_card ) {
		if ( empty( $_card['group'] ) ) {
			continue;
		}
		$_pt = ah_parent_term_by_hint( (string) $_card['group'] );
		if ( $_pt ) {
			$_card['url'] = ah_parent_term_guides_url( $_pt->slug );
		}
	}
	unset( $_card );
}

/* ── Inject the dynamic block: Latest Insights & Articles (newest posts) ───── */
$latest = get_posts( array(
	'post_type'      => 'post',
	'post_status'    => 'publish',
	'posts_per_page' => 3,
	'orderby'        => 'date',
	'order'          => 'DESC',
) );
$items = array();
foreach ( $latest as $i => $p ) {
	$items[] = array(
		'title' => get_the_title( $p ),
		'url'   => get_permalink( $p ),
		'image' => get_the_post_thumbnail_url( $p, 'medium' ) ?: '',
		'tag'   => 0 === $i ? 'New' : '',
		'meta'  => get_the_date( '', $p ),
	);
}
$data['articles']['items'] = $items;

/**
 * Final override seam - a DB model or site customisation can hook this to
 * replace any section without editing the JSON file or the components.
 */
return apply_filters( 'ah_khub_data', $data );
