<?php

namespace Adn\Theme\Admin\Tab;

defined( 'ABSPATH' ) || exit;

class HomeTab extends AbstractTab {
	public function slug(): string { return 'home'; }
	public function title(): string { return 'Home Page'; }

	public function render(): void {
		$subtab = $_GET['subtab'] ?? 'sections';
		// Load subtab view
		$viewPath = ADN_THEME_DIR . '/src/Admin/View/tabs/home/' . sanitize_file_name( $subtab ) . '.php';
		if ( file_exists( $viewPath ) ) {
			include $viewPath;
		}
	}
}
