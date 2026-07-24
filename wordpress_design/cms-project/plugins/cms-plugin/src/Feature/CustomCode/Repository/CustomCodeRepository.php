<?php

namespace Ah\Cms\Feature\CustomCode\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class CustomCodeRepository extends AbstractRepository {
	protected function table(): string { return 'custom_code'; }
	protected function primaryKey(): string { return 'id'; }

	public function findBySlug( string $slug ): ?array {
		return $this->findOneBy( [ 'slug' => $slug ] );
	}

	public function findGlobal(): ?array {
		return $this->findOneBy( [ 'slug' => '__global__' ] );
	}
}
