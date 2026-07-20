<?php
/**
 * config/routes.php - DYNAMIC route registry (DB-driven URLs).
 *
 * For URLs that are not fixed pages - e.g. /buying/, /selling/ where the
 * slugs live in a database table (CMS taxonomy terms, city names, ...).
 * core/router.php loops these rules for every single-segment URL that WP
 * would otherwise 404 (or serve as the wrong post).
 *
 * Entry keys:
 *   match    (callable) function ( string $slug ): array|false
 *            Return false when the slug is not yours. Return an array of
 *            query vars (may be empty) when it is - each pair is exposed
 *            via set_query_var() so the template can read it.
 *   template (string)   Template path relative to theme root. Required.
 *   title    (callable|string) Optional. Document title, or a
 *            function ( string $slug ): string.
 *   css      (array)    Page-specific stylesheets for this route.
 *   js       (array)    Page-specific scripts for this route.
 *
 * EXAMPLE - route /<term>/ to a category template when the slug exists in a
 * plugin table (uncomment and adapt; the matcher MUST use $wpdb->prepare):
 *
 * 'guide_category' => array(
 *     'match'    => function ( $slug ) {
 *         global $wpdb;
 *         $t   = $wpdb->prefix . 'ah_taxonomy_parent_terms';
 *         if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $t ) ) !== $t ) {
 *             return false;
 *         }
 *         $row = $wpdb->get_row( $wpdb->prepare(
 *             "SELECT id, name FROM `{$t}` WHERE slug = %s AND status = 'active' LIMIT 1",
 *             $slug
 *         ) );
 *         return $row ? array( 'nt_term_slug' => $slug, 'nt_term_id' => (int) $row->id ) : false;
 *     },
 *     'template' => 'pages/page-category.php',
 *     'title'    => function ( $slug ) { return ucwords( str_replace( '-', ' ', $slug ) ); },
 *     'css'      => array( 'assets/css/pages/category.css' ),
 * ),
 */

defined( 'ABSPATH' ) || exit;

return array();
