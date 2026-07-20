<?php
/**
 * config/rest.php - REST API route registry.
 *
 * core/rest.php loops this array on rest_api_init. Routes live under the
 * namespace below, e.g.  /wp-json/nt/v1/posts?page=2&search=villa
 *
 * Call from JS with the built-in helper (assets/js/common.js):
 *   NT.rest( 'posts', { page: 2, search: 'villa' } ).then( ... );
 *
 * Entry keys:
 *   methods    (string) 'GET', 'POST', ... Required.
 *   callback   (string) PHP function. Receives WP_REST_Request. Required.
 *   file       (string) Theme-relative file defining the callback (lazy-loaded).
 *   capability (string) '' = public route; otherwise current_user_can() gate.
 *   args       (array)  Standard register_rest_route args (validation/defaults).
 */

defined( 'ABSPATH' ) || exit;

return array(

	'namespace' => 'nt/v1',

	'routes' => array(

		// Paged post listing - powers the News page "Load More".
		'posts' => array(
			'methods'  => 'GET',
			'callback' => 'nt_rest_posts',
			'file'     => 'handlers/rest/posts.php',
			'args'     => array(
				'page' => array(
					'type'              => 'integer',
					'default'           => 1,
					'sanitize_callback' => 'absint',
				),
				'per_page' => array(
					'type'              => 'integer',
					'default'           => 6,
					'sanitize_callback' => 'absint',
				),
				'search' => array(
					'type'              => 'string',
					'default'           => '',
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		),
	),
);
