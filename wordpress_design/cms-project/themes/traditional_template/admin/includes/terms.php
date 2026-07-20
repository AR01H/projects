<?php
/**
 * admin/includes/terms.php - Content term levels (loaded on EVERY request
 * via config/files.php 'always', because templates need the labels too).
 *
 * Defines the naming of the 3-level content hierarchy this template uses
 * (like Guide -> Category -> Article on a sample site) plus helpers that read
 * the demo tree from admin/data/terms.json until a real CMS/DB is wired in.
 *
 * To plug in a real data source later, ONLY rewrite nt_terms_tree() (or hook
 * the 'nt_terms_tree' filter) - every template already goes through it.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Term level registry: level key => labels.
 * Base labels come from the GLOBAL constants in config/theme.php
 * (NT_TERM_PARENT / NT_TERM_SECTION / NT_TERM_CONTENT); plurals default to
 * label + 's' and can be overridden through the 'nt_term_levels' filter.
 */
function nt_term_levels() {
	return apply_filters( 'nt_term_levels', array(
		'parent'  => array( 'label' => NT_TERM_PARENT,  'plural' => NT_TERM_PARENT . 's' ),
		'section' => array( 'label' => NT_TERM_SECTION, 'plural' => 'Categories' ),
		'content' => array( 'label' => NT_TERM_CONTENT, 'plural' => NT_TERM_CONTENT . 's' ),
	) );
}

/**
 * Label for a term level: nt_term_label( 'parent' ) -> 'Guide'.
 */
function nt_term_label( $level, $plural = false ) {
	$levels = nt_term_levels();
	$key    = $plural ? 'plural' : 'label';
	return (string) ( $levels[ $level ][ $key ] ?? ucfirst( (string) $level ) );
}

/**
 * The term tree used by menus, listing pages and sidebars.
 *
 * Today: served from admin/data/terms.json (instant availability, no DB).
 * Later: swap the body for a plugin/DB query - the shape must stay:
 *
 * array(
 *   array(
 *     'slug' => 'buying', 'name' => 'Buying', 'icon' => 'fa-house',
 *     'children' => array( array( 'slug' => ..., 'name' => ... ), ... ),
 *   ),
 *   ...
 * )
 */
function nt_terms_tree() {
	$tree = nt_data( 'terms' );
	$tree = isset( $tree['tree'] ) && is_array( $tree['tree'] ) ? $tree['tree'] : array();
	return apply_filters( 'nt_terms_tree', $tree );
}
