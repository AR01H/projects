<?php

namespace Adn\Theme\Admin\Tab;

defined( 'ABSPATH' ) || exit;

class ContactInboxTab extends AbstractTab {
	public function slug(): string { return 'contact-inbox'; }
	public function title(): string { return 'Contact Inbox'; }

	public function render(): void {
		include ADN_THEME_DIR . '/src/Admin/View/tabs/contact-inbox.php';
	}
}
