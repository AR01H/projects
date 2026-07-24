<?php

namespace Ah\Cms\Feature\Banners\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class HomeBannersRepository extends AbstractRepository {
	protected function table(): string { return 'home_banners'; }
	protected function primaryKey(): string { return 'id'; }

	public function findActive(): array {
		return $this->findBy( [ 'is_active' => 1 ], [ 'sort_order', 'ASC' ] );
	}
}
