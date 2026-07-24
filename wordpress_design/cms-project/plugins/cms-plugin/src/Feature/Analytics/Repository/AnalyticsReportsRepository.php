<?php

namespace Ah\Cms\Feature\Analytics\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class AnalyticsReportsRepository extends AbstractRepository {
	protected function table(): string { return 'analytics_reports'; }
	protected function primaryKey(): string { return 'id'; }
}
