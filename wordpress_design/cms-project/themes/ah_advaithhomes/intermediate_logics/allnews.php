<?php
defined( 'ABSPATH' ) || exit;
global $wpdb;
$base_url = get_permalink();
$today    = current_time( 'Y-m-d' );
$item_id  = absint( $_GET['item'] ?? 0 );

// ── DETAIL VIEW ──────────────────────────────────────────────────────────────
if ( $item_id && class_exists( 'AH_DB_Helper' ) ) {
	$nb_table = AH_DB_Helper::table( 'news_bar_items' );
	$single   = $wpdb->get_row( $wpdb->prepare(
		"SELECT * FROM `{$nb_table}` WHERE id = %d AND status = 'active' LIMIT 1",
		$item_id
	) );

	if ( ! $single ) {
		wp_safe_redirect( $base_url );
		exit;
	}

	$s_terms = [];
	if ( class_exists( 'AH_Theme_Content_Taxonomy' ) ) {
		$_td     = AH_Theme_Content_Taxonomy::get_terms_for_items( [ $single ], 'news_bar_item' );
		$s_terms = $_td['item_terms'][ $single->id ] ?? [];
	}

	$rel_term_id = ! empty( $s_terms ) ? (int) ( $s_terms[0]->id ?? $s_terms[0]->taxonomy_id ?? 0 ) : 0;
	$related     = [];
	$nb_tbl      = AH_DB_Helper::table( 'news_bar_items' );

	if ( $rel_term_id && class_exists( 'AH_Theme_Content_Taxonomy' ) ) {
		$ct_tbl  = AH_DB_Helper::table( 'content_taxonomies' );
		$related = $wpdb->get_results( $wpdb->prepare(
			"SELECT DISTINCT n.* FROM `{$nb_tbl}` n
			 INNER JOIN `{$ct_tbl}` ct ON ct.object_id = n.id AND ct.object_type = 'news_bar_item'
			 WHERE n.status = 'active' AND n.id <> %d AND ct.taxonomy_id = %d
			 ORDER BY COALESCE(n.start_date,'1970-01-01') DESC, n.id DESC LIMIT 3",
			$single->id, $rel_term_id
		) ) ?: [];
	}
	if ( empty( $related ) ) {
		$related = $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM `{$nb_tbl}` WHERE status = 'active' AND id <> %d
			 ORDER BY COALESCE(start_date,'1970-01-01') DESC, id DESC LIMIT 3",
			$single->id
		) ) ?: [];
	}

	$rel_terms = [];
	if ( ! empty( $related ) && class_exists( 'AH_Theme_Content_Taxonomy' ) ) {
		$_rd       = AH_Theme_Content_Taxonomy::get_terms_for_items( $related, 'news_bar_item' );
		$rel_terms = $_rd['item_terms'] ?? [];
	}

	$parent_terms_sb = [];
	if ( class_exists( 'AH_DB_Helper' ) ) {
		$_pt_tbl_sb      = AH_DB_Helper::table( 'taxonomy_parent_terms' );
		$parent_terms_sb = $wpdb->get_results(
			"SELECT id, name, slug, color, icon_emoji FROM `{$_pt_tbl_sb}` WHERE status = 1 ORDER BY name ASC"
		) ?: [];
	}

	return [
		'view'           => 'detail',
		'base_url'       => $base_url,
		'single'         => $single,
		's_terms'        => $s_terms,
		's_cat'          => ! empty( $s_terms ) ? $s_terms[0]->name : 'News',
		's_title'        => $single->text ?? '',
		's_date'         => ! empty( $single->start_date ) ? date_i18n( 'd M Y', strtotime( $single->start_date ) ) : '',
		's_content'      => $single->content ?? '',
		's_thumb'        => ! empty( $single->image_id )
			? ( wp_get_attachment_image_url( (int) $single->image_id, 'large' )
			  ?: wp_get_attachment_image_url( (int) $single->image_id, 'medium_large' ) )
			: '',
		'related'        => $related,
		'rel_terms'      => $rel_terms,
		'sidebar'        => [
			'site_stats'     => function_exists( 'ah_get_site_stats' )     ? ah_get_site_stats()     : [],
			'news_bar_items' => function_exists( 'ah_get_news_bar_items' ) ? ah_get_news_bar_items() : [],
			'popular_posts'  => get_posts( [ 'posts_per_page' => 5, 'post_status' => 'publish', 'orderby' => 'date', 'order' => 'DESC', 'meta_key' => '_ah_is_popular', 'meta_value' => '1' ] ),
			'cats'           => get_categories( [ 'hide_empty' => true ] ),
			'parent_terms'   => $parent_terms_sb,
			'permalink'      => home_url( '/news-info-feeder/' ),
		],
	];
}

// ── LISTING VIEW ──────────────────────────────────────────────────────────────
$per_page   = 12;
$active_cat = sanitize_text_field( $_GET['category'] ?? '' );
$paged      = max( 1, absint( $_GET['pg'] ?? 1 ) );
$news_items  = [];
$total_items = 0;
$max_pages   = 1;

if ( class_exists( 'AH_DB_Helper' ) ) {
	$nb_table = AH_DB_Helper::table( 'news_bar_items' );
	$offset   = ( $paged - 1 ) * $per_page;
	$where    = $wpdb->prepare(
		"WHERE status = 'active' AND (start_date IS NULL OR start_date <= %s) AND (end_date IS NULL OR end_date >= %s)",
		$today, $today
	);
	$cat_term_id = 0;
	if ( $active_cat ) {
		$_tax_table = AH_DB_Helper::table( 'taxonomies' );
		$_tax_row   = $wpdb->get_row( $wpdb->prepare( "SELECT id FROM `{$_tax_table}` WHERE slug = %s AND status = 1 LIMIT 1", $active_cat ) );
		if ( $_tax_row ) $cat_term_id = (int) $_tax_row->id;
	}
	if ( $cat_term_id ) {
		$ct_table    = AH_DB_Helper::table( 'content_taxonomies' );
		$total_items = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT n.id) FROM `{$nb_table}` n INNER JOIN `{$ct_table}` ct ON ct.object_id = n.id AND ct.object_type = 'news_bar_item' {$where} AND ct.taxonomy_id = {$cat_term_id}" );
		$news_items  = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT n.* FROM `{$nb_table}` n INNER JOIN `{$ct_table}` ct ON ct.object_id = n.id AND ct.object_type = 'news_bar_item' {$where} AND ct.taxonomy_id = {$cat_term_id} ORDER BY COALESCE(n.start_date,'1970-01-01') DESC, n.id DESC LIMIT %d OFFSET %d", $per_page, $offset ) ) ?: [];
	} else {
		$total_items = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$nb_table}` {$where}" );
		$news_items  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$nb_table}` {$where} ORDER BY COALESCE(start_date,'1970-01-01') DESC, id DESC LIMIT %d OFFSET %d", $per_page, $offset ) ) ?: [];
	}
	$max_pages = $total_items > 0 ? (int) ceil( $total_items / $per_page ) : 1;
}

$item_terms = [];
if ( ! empty( $news_items ) && class_exists( 'AH_Theme_Content_Taxonomy' ) ) {
	$_tax_data  = AH_Theme_Content_Taxonomy::get_terms_for_items( $news_items, 'news_bar_item' );
	$item_terms = $_tax_data['item_terms'] ?? [];
}

$unique_terms = [];
if ( class_exists( 'AH_Theme_Content_Taxonomy' ) && class_exists( 'AH_DB_Helper' ) ) {
	$_nb2       = AH_DB_Helper::table( 'news_bar_items' );
	$_all_items = $wpdb->get_results( $wpdb->prepare(
		"SELECT id FROM `{$_nb2}` WHERE status = 'active' AND (start_date IS NULL OR start_date <= %s) AND (end_date IS NULL OR end_date >= %s)",
		$today, $today
	) ) ?: [];
	if ( ! empty( $_all_items ) ) {
		$unique_terms = AH_Theme_Content_Taxonomy::get_unique_terms( $_all_items, 'news_bar_item' );
	}
}

return [
	'view'         => 'listing',
	'base_url'     => $base_url,
	'news_items'   => $news_items,
	'item_terms'   => $item_terms,
	'unique_terms' => $unique_terms,
	'active_cat'   => $active_cat,
	'paged'        => $paged,
	'max_pages'    => $max_pages,
];
