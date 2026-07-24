<?php

namespace Ah\Cms\Feature\Redirect\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class RedirectRulesRepository extends AbstractRepository {
	protected function table(): string { return 'redirect_rules'; }
	protected function primaryKey(): string { return 'id'; }

	public function findActive(): array {
		return $this->findBy( [ 'is_active' => 1 ] );
	}
}
