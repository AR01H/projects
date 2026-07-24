<?php

namespace Ah\Cms\Feature\Resources\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class ResourcesRepository extends AbstractRepository {
	protected function table(): string { return 'resources'; }
	protected function primaryKey(): string { return 'id'; }
}
