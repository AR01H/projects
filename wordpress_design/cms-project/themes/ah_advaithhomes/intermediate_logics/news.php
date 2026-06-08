<?php
defined( 'ABSPATH' ) || exit;
$per_page    = 10;
$paged       = max( 1, get_query_var( 'paged' ) ?: ( get_query_var( 'page' ) ?: 1 ) );
$active_slug = sanitize_text_field( $_GET['term'] ?? '' );

$model      = class_exists( 'AH_Newsbar_Model' ) ? new AH_Newsbar_Model() : null;
$all_active = $model ? $model->get_active() : [];

$taxonomy_result = $all_active
	? AH_Theme_Content_Taxonomy::get_terms_for_items( $all_active, 'news_bar_item' )
	: [ 'item_terms' => [], 'unique_terms' => [] ];

$item_terms   = $taxonomy_result['item_terms'];
$unique_terms = $taxonomy_result['unique_terms'];

$filtered = $all_active;
if ( $active_slug ) {
	$filtered = array_values( array_filter( $all_active, function( $item ) use ( $active_slug, $item_terms ) {
		foreach ( $item_terms[ (int) $item->id ] ?? [] as $t ) {
			if ( $t->slug === $active_slug ) return true;
		}
		return false;
	} ) );
}

$total     = count( $filtered );
$max_pages = $total ? (int) ceil( $total / $per_page ) : 1;

return [
	'items'       => array_slice( $filtered, ( $paged - 1 ) * $per_page, $per_page ),
	'item_terms'  => $item_terms,
	'unique_terms'=> $unique_terms,
	'all_count'   => count( $all_active ),
	'active_slug' => $active_slug,
	'paged'       => $paged,
	'max_pages'   => $max_pages,
];
