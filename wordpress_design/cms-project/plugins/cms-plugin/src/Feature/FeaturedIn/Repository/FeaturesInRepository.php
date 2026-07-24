<?php

namespace Ah\Cms\Feature\FeaturedIn\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class FeaturesInRepository extends AbstractRepository {
	protected function table(): string { return 'features_in'; }
	protected function primaryKey(): string { return 'id'; }

	public function findActive(): array {
		return $this->findBy( [ 'is_active' => 1 ], [ 'sort_order', 'ASC' ] );
	}
}
