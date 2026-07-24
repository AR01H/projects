<?php

namespace Adn\Theme\Admin\Tab;

defined( 'ABSPATH' ) || exit;

class CategoryTab extends AbstractTab {
	public function slug(): string { return 'category'; }
	public function title(): string { return 'Category Pages'; }

	public function render(): void {
		include ADN_THEME_DIR . '/src/Admin/View/tabs/category.php';
	}
}
