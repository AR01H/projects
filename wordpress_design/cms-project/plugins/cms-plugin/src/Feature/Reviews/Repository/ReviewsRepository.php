<?php

namespace Ah\Cms\Feature\Reviews\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class ReviewsRepository extends AbstractRepository {
	protected function table(): string { return 'reviews'; }
	protected function primaryKey(): string { return 'id'; }

	public function findFeatured(): array {
		return $this->findBy( [ 'is_featured' => 1 ] );
	}
}
