<?php

namespace Ah\Cms\Feature\Workflow\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class RulesRepository extends AbstractRepository {
	protected function table(): string { return 'rules'; }
	protected function primaryKey(): string { return 'id'; }

	public function findActive(): array {
		return $this->findBy( [ 'is_active' => 1 ] );
	}

	public function findByTrigger( string $trigger ): array {
		return $this->findBy( [ 'trigger_event' => $trigger, 'is_active' => 1 ] );
	}
}
