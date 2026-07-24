<?php
/**
 * Site Notice Popup
 *
 * Renders the site-wide notice popup (once per day, resets if content changes).
 *
 * @package Adn\Theme\Common\Frontend
 */
defined( 'ABSPATH' ) || exit;

function adn_render_site_notice_popup(): void {
	if ( class_exists( 'AH_Notice_Helper' ) ) {
		AH_Notice_Helper::render_frontend_popup();
	}
}
