<?php

namespace Ah\Cms\Feature\Media\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class MediaRepository extends AbstractRepository {
	protected function table(): string { return 'media'; }
	protected function primaryKey(): string { return 'id'; }
}
