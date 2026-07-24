<?php

namespace Ah\Cms\Feature\Spotlights\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class SpotlightsRepository extends AbstractRepository {
	protected function table(): string { return 'spotlights'; }
	protected function primaryKey(): string { return 'id'; }

	public function findByTerm( int $termId, int $limit = 999 ): array {
		return $this->findBy( [ 'term_id' => $termId, 'is_active' => 1 ], [ 'sort_order', 'ASC' ], $limit );
	}
}
