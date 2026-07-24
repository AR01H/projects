<?php

namespace Ah\Cms\Feature\FAQs\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class FaqsRepository extends AbstractRepository {
	protected function table(): string { return 'faqs'; }
	protected function primaryKey(): string { return 'id'; }

	public function findBySlug( string $slug ): ?array {
		return $this->findOneBy( [ 'slug' => $slug ] );
	}
}
