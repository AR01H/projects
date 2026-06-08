<?php
defined( 'ABSPATH' ) || exit;
global $wpdb;

if ( ! function_exists( 'ah_guide_topic_icon' ) ) {
	function ah_guide_topic_icon( $name = '', $slug = '', $explicit = '' ) {
		$explicit = trim( (string) $explicit );
		if ( $explicit !== '' && preg_match( '/[^\x00-\x7F]/u', $explicit ) ) return $explicit;
		$h   = strtolower( $name . ' ' . $slug );
		$map = [
			'first-time' => '🔑', 'first time' => '🔑',
			'mortgage'   => '🏦', 'finance'    => '💷', 'remortgage' => '🏦',
			'calculat'   => '🧮', 'stamp duty' => '🧾',
			'legal'      => '⚖️', 'conveyanc'  => '⚖️',
			'invest'     => '📈', 'btl'        => '📈', 'buy-to-let' => '📈',
			'market'     => '📊', 'news'       => '📰',
			'reloc'      => '✈️', 'international' => '🌍',
			'luxury'     => '💎',
			'tip'        => '💡', 'advice'     => '💡',
			'sell'       => '🏷️',
			'buying'     => '🏡', 'home'       => '🏡', 'purchase'   => '🏡',
			'rent'       => '🔑', 'landlord'   => '🏘️',
		];
		foreach ( $map as $needle => $icon ) {
			if ( strpos( $h, $needle ) !== false ) return $icon;
		}
		return '📂';
	}
}

$categories     = ah_get_guide_categories();
$_raw_cat       = sanitize_text_field( $_GET['category'] ?? '' );
$active_cat     = sanitize_title( strtok( $_raw_cat, '?' ) );
$active_pt_slug = sanitize_title( $_GET['parent_term'] ?? '' );
$paged          = max( 1, absint( $_GET['pg'] ?? get_query_var( 'paged', 1 ) ) );
$base_url       = get_permalink();

$active_pt        = null;
$pt_child_cat_ids = [];
$_child_slugs     = [];
if ( $active_pt_slug && class_exists( 'AH_Taxonomy_Parent_Model' ) ) {
	$_ptm = new AH_Taxonomy_Parent_Model();
	foreach ( $_ptm->get_all_active() as $_pt ) {
		if ( ( $_pt->slug ?? '' ) === $active_pt_slug ) { $active_pt = $_pt; break; }
	}
	if ( $active_pt ) {
		$_tax_table   = AH_DB_Helper::table( 'taxonomies' );
		$_child_slugs = $wpdb->get_col( $wpdb->prepare(
			"SELECT slug FROM `{$_tax_table}` WHERE parent_term_id = %d AND status = 1",
			(int) $active_pt->id
		) ) ?: [];
		foreach ( $_child_slugs as $_cs ) {
			$_wc = get_term_by( 'slug', $_cs, 'category' );
			if ( $_wc ) $pt_child_cat_ids[] = $_wc->term_id;
		}
	}
}

$display_cats = ( $active_pt_slug && ! empty( $_child_slugs ) )
	? array_values( array_filter( $categories, function( $c ) use ( $_child_slugs ) {
		$c = is_object( $c ) ? (array) $c : $c;
		return in_array( $c['slug'] ?? '', $_child_slugs, true );
	} ) )
	: $categories;

$active_cat_obj = null;
if ( $active_cat && $categories ) {
	foreach ( $categories as $c ) {
		$c = is_object( $c ) ? (array) $c : $c;
		if ( ( $c['slug'] ?? '' ) === $active_cat ) { $active_cat_obj = $c; break; }
	}
}

$sidebar_pts = [];
if ( class_exists( 'AH_Taxonomy_Parent_Model' ) && class_exists( 'AH_DB_Helper' ) ) {
	$_ptm_sb = new AH_Taxonomy_Parent_Model();
	$_tax_sb = AH_DB_Helper::table( 'taxonomies' );
	foreach ( $_ptm_sb->get_all_active() as $_sb_pt ) {
		$_sb_children = $wpdb->get_results( $wpdb->prepare(
			"SELECT slug, name FROM `{$_tax_sb}` WHERE parent_term_id = %d AND status = 1 ORDER BY name ASC",
			(int) $_sb_pt->id
		) ) ?: [];
		$sidebar_pts[] = [ 'pt' => $_sb_pt, 'children' => $_sb_children ];
	}
}

$cat_pt_map = [];
foreach ( $sidebar_pts as $_sb ) {
	foreach ( $_sb['children'] as $_sbc ) {
		$cat_pt_map[ $_sbc->slug ] = $_sb['pt'];
	}
}

$is_filtered    = $active_cat || $active_pt_slug;
$guides_query   = null;
$latest_guides  = [];
$popular_guides = [];

if ( $is_filtered ) {
	$query_args = [
		'post_type'      => 'post',
		'posts_per_page' => 12,
		'paged'          => $paged,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
	];
	if ( $active_cat ) {
		$term = get_term_by( 'slug', $active_cat, 'category' );
		if ( $term ) $query_args['cat'] = $term->term_id;
	} elseif ( $pt_child_cat_ids ) {
		$query_args['category__in'] = $pt_child_cat_ids;
	} else {
		$query_args['post__in'] = [ 0 ];
	}
	$guides_query = new WP_Query( $query_args );
} else {
	$latest_guides  = get_posts( [ 'posts_per_page' => 8,  'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' ] );
	$popular_guides = get_posts( [ 'posts_per_page' => 4,  'post_status' => 'publish', 'meta_key' => '_ah_is_popular', 'meta_value' => '1', 'orderby' => 'date', 'order' => 'DESC' ] );
}

return [
	'base_url'       => $base_url,
	'is_filtered'    => $is_filtered,
	'active_cat'     => $active_cat,
	'active_cat_obj' => $active_cat_obj,
	'active_pt'      => $active_pt,
	'active_pt_slug' => $active_pt_slug,
	'display_cats'   => $display_cats,
	'sidebar_pts'    => $sidebar_pts,
	'cat_pt_map'     => $cat_pt_map,
	'guides_query'   => $guides_query,
	'latest_guides'  => $latest_guides,
	'popular_guides' => $popular_guides,
	'paged'          => $paged,
];
