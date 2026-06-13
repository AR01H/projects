<?php
/**
 * includes/core_terms.php - CPT & Taxonomy Registration
 *
 * RULE: Loop over $cfg['cpt'] and $cfg['taxonomies'].
 *       Never register a CPT/taxonomy with raw repetitive code.
 */

defined( 'ABSPATH' ) || exit;

$cfg = $GLOBALS['theme_config'];

add_action( 'init', function () use ( $cfg ): void {

    // ── Register Custom Post Types ────────────────────────────────
    foreach ( $cfg['cpt'] as $cpt ) {
        $labels = [
            'name'               => $cpt['plural'],
            'singular_name'      => $cpt['singular'],
            'add_new_item'       => 'Add New ' . $cpt['singular'],
            'edit_item'          => 'Edit ' . $cpt['singular'],
            'view_item'          => 'View ' . $cpt['singular'],
            'search_items'       => 'Search ' . $cpt['plural'],
            'not_found'          => 'No ' . strtolower( $cpt['plural'] ) . ' found.',
            'all_items'          => 'All ' . $cpt['plural'],
        ];

        register_post_type( $cpt['slug'], [
            'labels'      => $labels,
            'public'      => $cpt['public'],
            'has_archive' => $cpt['has_archive'],
            'supports'    => $cpt['supports'],
            'menu_icon'   => $cpt['icon'] ?? 'dashicons-admin-post',
            'rewrite'     => [ 'slug' => $cpt['slug'] ],
            'show_in_rest' => true, // enables Gutenberg + REST API
        ] );
    }

    // ── Register Taxonomies ───────────────────────────────────────
    foreach ( $cfg['taxonomies'] as $tax ) {
        $labels = [
            'name'          => $tax['plural'],
            'singular_name' => $tax['singular'],
            'search_items'  => 'Search ' . $tax['plural'],
            'all_items'     => 'All ' . $tax['plural'],
            'edit_item'     => 'Edit ' . $tax['singular'],
            'add_new_item'  => 'Add New ' . $tax['singular'],
        ];

        register_taxonomy( $tax['slug'], $tax['post_types'], [
            'labels'       => $labels,
            'hierarchical' => $tax['hierarchical'],
            'rewrite'      => [ 'slug' => $tax['slug'] ],
            'show_in_rest' => true,
        ] );
    }
}, 0 ); // priority 0 so CPTs are ready before other hooks
