<?php

namespace Adn\Theme\Admin\Tab;

defined( 'ABSPATH' ) || exit;

class ExpertTab extends AbstractTab {
	public function slug(): string { return 'experts'; }
	public function title(): string { return 'Experts / Team'; }

	public function render(): void {
		$subtab = $_GET['subtab'] ?? 'list';
		$viewPath = ADN_THEME_DIR . '/src/Admin/View/tabs/experts/' . sanitize_file_name( $subtab ) . '.php';
		if ( file_exists( $viewPath ) ) {
			include $viewPath;
		}
	}
}
