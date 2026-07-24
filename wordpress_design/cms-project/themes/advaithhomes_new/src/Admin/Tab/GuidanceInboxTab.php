<?php

namespace Adn\Theme\Admin\Tab;

defined( 'ABSPATH' ) || exit;

class GuidanceInboxTab extends AbstractTab {
	public function slug(): string { return 'guidance-inbox'; }
	public function title(): string { return 'Guidance Inbox'; }

	public function render(): void {
		include ADN_THEME_DIR . '/src/Admin/View/tabs/guidance-inbox.php';
	}
}
