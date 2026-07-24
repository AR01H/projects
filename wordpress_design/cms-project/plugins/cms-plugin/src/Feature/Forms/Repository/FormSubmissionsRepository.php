<?php

namespace Ah\Cms\Feature\Forms\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class FormSubmissionsRepository extends AbstractRepository {
	protected function table(): string { return 'form_submissions'; }
	protected function primaryKey(): string { return 'id'; }

	public function findByForm( int $formId, int $limit = 50 ): array {
		return $this->findBy( [ 'form_id' => $formId ], [ 'submitted_at', 'DESC' ], $limit );
	}
}
