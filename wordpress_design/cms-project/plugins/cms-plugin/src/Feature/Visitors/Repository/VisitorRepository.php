<?php

namespace Ah\Cms\Feature\Visitors\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class VisitorRepository extends AbstractRepository {
	protected function table(): string { return 'visitor_logs'; }
	protected function primaryKey(): string { return 'id'; }

	public function getRecentVisitors( int $limit = 50 ): array {
		return $this->findAll( [ 'order_by' => 'created_at', 'order' => 'DESC', 'limit' => $limit ] );
	}
}
