<?php

namespace Ah\Cms\Feature\SiteNotices\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class SiteNoticesRepository extends AbstractRepository {
	protected function table(): string { return 'site_notices'; }
	protected function primaryKey(): string { return 'id'; }

	public function findActive(): array {
		return $this->findBy( [ 'is_active' => 1 ] );
	}
}
