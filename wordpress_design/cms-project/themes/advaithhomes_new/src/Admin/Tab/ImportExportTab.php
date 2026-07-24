<?php

namespace Adn\Theme\Admin\Tab;

defined( 'ABSPATH' ) || exit;

class ImportExportTab extends AbstractTab {
	public function slug(): string { return 'import-export'; }
	public function title(): string { return 'Import / Export'; }

	public function render(): void {
		include ADN_THEME_DIR . '/src/Admin/View/tabs/import-export.php';
	}
}
