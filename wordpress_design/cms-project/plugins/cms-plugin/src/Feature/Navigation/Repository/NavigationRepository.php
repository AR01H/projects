<?php

namespace Ah\Cms\Feature\Navigation\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class NavigationRepository extends AbstractRepository {
	protected function table(): string { return 'navigation'; }
	protected function primaryKey(): string { return 'id'; }

	public function getMenuItems( string $location = 'main' ): array {
		return $this->findBy( [ 'location' => $location ], [ 'sort_order', 'ASC' ] );
	}
}
