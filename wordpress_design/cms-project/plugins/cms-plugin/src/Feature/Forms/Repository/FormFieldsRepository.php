<?php

namespace Ah\Cms\Feature\Forms\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class FormFieldsRepository extends AbstractRepository {
	protected function table(): string { return 'form_fields'; }
	protected function primaryKey(): string { return 'id'; }

	public function findByForm( int $formId ): array {
		return $this->findBy( [ 'form_id' => $formId ], [ 'sort_order', 'ASC' ] );
	}
}
