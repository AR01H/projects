<?php

namespace Ah\Cms\Feature\FileLinks\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class FileLinksRepository extends AbstractRepository {
	protected function table(): string { return 'related_links'; }
	protected function primaryKey(): string { return 'id'; }

	public function findByType( string $type ): array {
		return $this->findBy( [ 'link_type' => $type ] );
	}
}
