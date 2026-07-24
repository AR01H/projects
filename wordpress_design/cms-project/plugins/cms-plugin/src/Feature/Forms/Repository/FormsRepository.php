<?php

namespace Ah\Cms\Feature\Forms\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class FormsRepository extends AbstractRepository {
	protected function table(): string { return 'forms'; }
	protected function primaryKey(): string { return 'id'; }

	public function findActive(): array {
		return $this->findBy( [ 'is_active' => 1 ] );
	}
}
