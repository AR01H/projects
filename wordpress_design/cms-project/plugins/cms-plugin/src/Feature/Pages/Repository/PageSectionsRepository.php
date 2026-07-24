<?php

namespace Ah\Cms\Feature\Pages\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class PageSectionsRepository extends AbstractRepository {
	protected function table(): string { return 'page_sections'; }
	protected function primaryKey(): string { return 'id'; }

	public function findByPage( int $pageId ): array {
		return $this->findBy( [ 'page_id' => $pageId ], [ 'sort_order', 'ASC' ] );
	}
}
