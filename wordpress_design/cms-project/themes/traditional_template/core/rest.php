<?php
/**
 * core/rest.php - REST route registration driven by config/rest.php.
 *
 * Public routes get permission_callback __return_true; routes with a
 * 'capability' get a current_user_can() permission callback. Handler files
 * are lazy-loaded only when their route actually fires.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Registration loop - hooked on rest_api_init by the bootstrap.
 */
function nt_register_rest_routes() {
	$cfg = nt_config( 'rest' );
	$ns  = (string) ( $cfg['namespace'] ?? 'nt/v1' );

	foreach ( (array) ( $cfg['routes'] ?? array() ) as $route => $def ) {

		$permission = '__return_true';
		if ( ! empty( $def['capability'] ) ) {
			$cap        = (string) $def['capability'];
			$permission = static function () use ( $cap ) {
				return current_user_can( $cap );
			};
		}

		register_rest_route( $ns, '/' . ltrim( (string) $route, '/' ), array(
			'methods'             => $def['methods'] ?? 'GET',
			'permission_callback' => $permission,
			'args'                => (array) ( $def['args'] ?? array() ),
			'callback'            => static function ( $request ) use ( $def, $route ) {
				if ( ! empty( $def['file'] ) ) {
					nt_require_theme_file( $def['file'] );
				}
				$callback = $def['callback'] ?? '';
				if ( ! is_callable( $callback ) ) {
					return new WP_Error( 'nt_no_handler', 'Handler not found for route: ' . $route, array( 'status' => 500 ) );
				}
				return call_user_func( $callback, $request );
			},
		) );
	}
}
