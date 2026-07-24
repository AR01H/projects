<?php

namespace Ah\Cms\Feature\Newsletter\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class NewsletterRepository extends AbstractRepository {
	protected function table(): string { return 'newsletters'; }
	protected function primaryKey(): string { return 'id'; }

	public function findActive(): array {
		return $this->findBy( [ 'is_active' => 1 ] );
	}
}
