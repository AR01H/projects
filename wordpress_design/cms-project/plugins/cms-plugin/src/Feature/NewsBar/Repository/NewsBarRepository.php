<?php

namespace Ah\Cms\Feature\NewsBar\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class NewsBarRepository extends AbstractRepository {
	protected function table(): string { return 'news_bar_items'; }
	protected function primaryKey(): string { return 'id'; }

	public function findActive(): array {
		return $this->findBy( [ 'is_active' => 1 ], [ 'sort_order', 'ASC' ] );
	}
}
