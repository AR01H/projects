<?php

namespace Ah\Cms\Feature\Settings\Repository;

defined( 'ABSPATH' ) || exit;

use Ah\Cms\Repository\AbstractRepository;

class SettingsRepository extends AbstractRepository {
	protected function table(): string { return 'site_settings'; }
	protected function primaryKey(): string { return 'id'; }

	public function getByGroup( string $group ): array {
		return $this->findBy( [ 'group' => $group ] );
	}

	public function getValue( string $key ): ?string {
		$row = $this->findOneBy( [ 'setting_key' => $key ] );
		return $row['setting_value'] ?? null;
	}
}
