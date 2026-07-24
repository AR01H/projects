<?php
/**
 * intermediate/page_guides_logical.php
 *
 * Thin wrapper: delegates to \Adn\Theme\Service\GuidesContext.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../src/Service/GuidesContext.php';

function adn_guides_news_items( $limit = 3 ) {
	return \Adn\Theme\Service\GuidesContext::newsItems( $limit );
}

function adn_guides_get_context() {
	return \Adn\Theme\Service\GuidesContext::getContext();
}
