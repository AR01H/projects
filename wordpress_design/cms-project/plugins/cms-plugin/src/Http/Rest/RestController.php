<?php

namespace Ah\Cms\Http\Rest;

defined( 'ABSPATH' ) || exit;

/**
 * Base REST controller — provides common REST API functionality.
 */
abstract class RestController {

	/**
	 * Register REST routes. Subclasses implement this.
	 */
	abstract public function registerRoutes(): void;

	/**
	 * Verify REST request permissions.
	 */
	protected function verifyPermission( \WP_REST_Request $request ): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Verify public REST endpoint permissions.
	 */
	protected function verifyPublicPermission( \WP_REST_Request $request ): bool {
		return true;
	}

	/**
	 * Get a sanitized parameter from the request.
	 */
	protected function param( \WP_REST_Request $request, string $key, $default = '' ) {
		$value = $request->get_param( $key );
		return $value !== null ? $value : $default;
	}
}
