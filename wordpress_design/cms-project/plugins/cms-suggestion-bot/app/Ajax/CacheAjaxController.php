<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Ajax;

use CmsSuggestionBot\Services\CacheService;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Tools -> Generate/Destroy/Rebuild Cache buttons. Registered in
 * Core\Plugin::boot() against the three CSB_AJAX_* action names.
 */
final class CacheAjaxController {

	public function __construct( private readonly CacheService $cacheService ) {}

	public function hooks(): void {
		add_action( 'wp_ajax_' . CSB_AJAX_GENERATE_CACHE, array( $this, 'generate' ) );
		add_action( 'wp_ajax_' . CSB_AJAX_DESTROY_CACHE, array( $this, 'destroy' ) );
		add_action( 'wp_ajax_' . CSB_AJAX_REBUILD_CACHE, array( $this, 'rebuild' ) );
	}

	public function generate(): void {
		$this->authorize();

		$types = isset( $_POST['types'] ) ? array_map( 'sanitize_key', (array) wp_unslash( $_POST['types'] ) ) : array();

		$result = empty( $types ) || in_array( 'everything', $types, true )
			? $this->cacheService->generateAll()
			: $this->cacheService->generate( $types );

		wp_send_json_success( array(
			'message' => __( 'Cache generated.', 'cms-suggestion-bot' ),
			'result'  => $result,
		) );
	}

	public function destroy(): void {
		$this->authorize();

		$type  = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '';
		$count = $this->cacheService->destroy( '' !== $type ? $type : null );

		wp_send_json_success( array(
			/* translators: %d: number of entries removed */
			'message' => sprintf( __( 'Destroyed %d cache entries.', 'cms-suggestion-bot' ), $count ),
		) );
	}

	public function rebuild(): void {
		$this->authorize();

		$result = $this->cacheService->rebuildAll();

		wp_send_json_success( array(
			'message' => __( 'Cache rebuilt.', 'cms-suggestion-bot' ),
			'result'  => $result,
		) );
	}

	private function authorize(): void {
		check_ajax_referer( 'csb_admin', 'nonce' );

		if ( ! current_user_can( CSB_CAPABILITY ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized.', 'cms-suggestion-bot' ) ), 403 );
		}
	}
}
