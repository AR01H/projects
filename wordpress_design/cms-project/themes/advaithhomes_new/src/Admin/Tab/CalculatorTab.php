<?php

namespace Adn\Theme\Admin\Tab;

defined( 'ABSPATH' ) || exit;

class CalculatorTab extends AbstractTab {
	public function slug(): string { return 'calculators'; }
	public function title(): string { return 'Manage Calculators'; }

	public function render(): void {
		$subtab = $_GET['subtab'] ?? 'list';
		$viewPath = ADN_THEME_DIR . '/src/Admin/View/tabs/calculators/' . sanitize_file_name( $subtab ) . '.php';
		if ( file_exists( $viewPath ) ) {
			include $viewPath;
		}
	}
}
