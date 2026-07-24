<?php

namespace Ah\Cms\Feature\Audit\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class AuditRepository extends AbstractRepository {
	protected function table(): string { return 'audit_logs'; }
	protected function primaryKey(): string { return 'id'; }

	public function getRecentLogs( int $limit = 50 ): array {
		return $this->findAll( [ 'order_by' => 'created_at', 'order' => 'DESC', 'limit' => $limit ] );
	}
}
