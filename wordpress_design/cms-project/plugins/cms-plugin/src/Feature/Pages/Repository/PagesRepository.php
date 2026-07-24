<?php

namespace Ah\Cms\Feature\Pages\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class PagesRepository extends AbstractRepository {
	protected function table(): string { return 'pages'; }
	protected function primaryKey(): string { return 'id'; }

	public function findByType( string $type ): array {
		return $this->findBy( [ 'type' => $type ] );
	}

	public function findActive(): array {
		return $this->findBy( [ 'status' => 'published' ] );
	}

	public function findBySlug( string $slug ): ?array {
		return $this->findOneBy( [ 'slug' => $slug ] );
	}
}
