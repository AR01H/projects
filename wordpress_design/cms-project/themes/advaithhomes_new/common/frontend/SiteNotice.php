<?php
/**
 * Site Notice Popup — Thin wrapper delegating to OOP class.
 *
 * @package Adn\Theme\Common\Frontend
 */
defined( 'ABSPATH' ) || exit;

function adn_render_site_notice_popup(): void {
	\Adn\Theme\Feature\Frontend\SiteNoticeRenderer::render();
}
