<?php

namespace Ah\Cms\Feature\Analytics\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class AnalyticsResultsRepository extends AbstractRepository {
	protected function table(): string { return 'analytics_results'; }
	protected function primaryKey(): string { return 'id'; }

	public function latestFor( int $reportId ): ?array {
		return $this->findOneBy( [ 'report_id' => $reportId ] );
	}
}
