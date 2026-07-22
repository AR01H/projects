<?php
namespace AHEcommerce\API;

use WP_REST_Controller;

/**
 * Abstract Base REST Controller.
 * Extends the WP_REST_Controller to enforce versioning and standard permissions.
 */
abstract class Abstract_REST_Controller extends WP_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'ah-ecommerce/v1';

	/**
	 * Register the routes for the objects of the controller.
	 * Child classes should override this method.
	 */
	public function register_routes() {
		// Override in child classes.
	}

	/**
	 * Check if a given request has access to read items.
	 */
	public function get_items_permissions_check( $request ) {
		return current_user_can( 'read' ); // Adjust capability based on module
	}

	/**
	 * Standardize error responses.
	 */
	protected function error_response( $code, $message, $status = 400 ) {
		return new \WP_Error(
			$code,
			$message,
			array( 'status' => $status )
		);
	}
}
