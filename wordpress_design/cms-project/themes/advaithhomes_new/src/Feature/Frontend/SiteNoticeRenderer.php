<?php
namespace Adn\Theme\Feature\Frontend;

defined( 'ABSPATH' ) || exit;

class SiteNoticeRenderer {

	public static function render(): void {
		if ( class_exists( 'AH_Notice_Helper' ) ) {
			\AH_Notice_Helper::render_frontend_popup();
		}
	}
}
