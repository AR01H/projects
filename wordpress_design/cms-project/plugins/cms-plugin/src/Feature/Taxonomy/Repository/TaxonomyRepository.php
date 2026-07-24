<?php

namespace Ah\Cms\Feature\Taxonomy\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class TaxonomyRepository extends AbstractRepository {
	protected function table(): string { return 'taxonomies'; }
	protected function primaryKey(): string { return 'id'; }

	public function findParentTerms(): array {
		return $this->findBy( [ 'parent_id' => 0 ], [ 'sort_order', 'ASC' ] );
	}

	public function findByType( string $type ): array {
		return $this->findBy( [ 'type' => $type ] );
	}
}
