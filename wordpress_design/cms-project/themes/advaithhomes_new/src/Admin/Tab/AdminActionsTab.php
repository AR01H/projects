<?php

namespace Adn\Theme\Admin\Tab;

defined( 'ABSPATH' ) || exit;

class AdminActionsTab extends AbstractTab {
	public function slug(): string { return 'admin-actions'; }
	public function title(): string { return 'Admin Actions'; }

	public function render(): void {
		$subtab = $_GET['subtab'] ?? 'cache';
		$viewPath = ADN_THEME_DIR . '/src/Admin/View/tabs/admin-actions/' . sanitize_file_name( $subtab ) . '.php';
		if ( file_exists( $viewPath ) ) {
			include $viewPath;
		}
	}
}
