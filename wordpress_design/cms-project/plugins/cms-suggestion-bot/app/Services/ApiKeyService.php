<?php

declare( strict_types = 1 );

namespace CmsSuggestionBot\Services;

use CmsSuggestionBot\Repositories\ApiKeyRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Issuing/revoking keys for the future public API (app/API) - Admin\Pages\ApiPage
 * goes through this rather than ApiKeyRepository directly.
 */
final class ApiKeyService {

	public function __construct( private readonly ApiKeyRepository $repository ) {}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function all(): array {
		return $this->repository->all();
	}

	public function issue( string $label ): string {
		$key = wp_generate_password( 40, false, false );

		$this->repository->insert( array(
			'label'           => sanitize_text_field( $label ),
			'api_key'         => $key,
			'allowed_origins' => '',
			'rate_limit'      => 60,
			'is_active'       => 1,
			'created_at'      => current_time( 'mysql' ),
		) );

		return $key;
	}

	public function revoke( int $id ): bool {
		return $this->repository->update( $id, array( 'is_active' => 0 ) );
	}

	public function delete( int $id ): bool {
		return $this->repository->delete( $id );
	}
}
