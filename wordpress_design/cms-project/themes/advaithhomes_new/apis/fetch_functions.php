<?php
/**
 * apis/fetch_functions.php - Theme REST API entry point.
 *
 * All routes and callbacks live in class-theme-rest-routes.php.
 *
 * HOW TO ADD A ROUTE: open class-theme-rest-routes.php, add one line to
 *   $routes and one static _cb_ method. Nothing else needs to change.
 *
 * HOW TO REMOVE A ROUTE: delete (or comment out) its $routes entry.
 *
 * Namespace : ADN_API_NS  (set in includes/core_settings.php)
 * URL prefix : /api/      (rest_url_prefix filter in functions.php)
 * Full base  : https://site.com/api/{ADN_API_NS}/
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/apis/class-theme-rest-routes.php';
// Transient-based fragment cache, invalidation hooks, and WP-Cron pre-warmer.
require_once get_template_directory() . '/apis/home-fragment-cache.php';

add_action( 'rest_api_init', array( 'ADN_Theme_Rest_Routes', 'register' ) );
