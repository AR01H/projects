<?php

namespace Adn\Theme\Admin\Tab;

defined( 'ABSPATH' ) || exit;

class DashboardTab extends AbstractTab {
	public function slug(): string { return 'dashboard'; }
	public function title(): string { return 'Dashboard'; }

	public function render(): void {
		// Render dashboard tab view
		include ADN_THEME_DIR . '/src/Admin/View/tabs/dashboard.php';
	}
}
