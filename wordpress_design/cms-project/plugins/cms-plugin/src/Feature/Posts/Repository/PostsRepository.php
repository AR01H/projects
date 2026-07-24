<?php

namespace Ah\Cms\Feature\Posts\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class PostsRepository extends AbstractRepository {
	protected function table(): string { return 'posts'; }
	protected function primaryKey(): string { return 'id'; }

	public function findFeatured(): array {
		return $this->findBy( [ 'is_featured' => 1 ] );
	}

	public function findPopular( int $limit = 10 ): array {
		return $this->findAll( [ 'order_by' => 'view_count', 'order' => 'DESC', 'limit' => $limit ] );
	}
}
