<?php

namespace Ah\Cms\Feature\Pages\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class BuilderPagesRepository extends AbstractRepository {
	protected function table(): string { return 'builder_pages'; }
	protected function primaryKey(): string { return 'id'; }

	public function findBySlug( string $slug ): ?array {
		return $this->findOneBy( [ 'slug' => $slug ] );
	}
}
