<?php
/**
 * intermediate/page_guide_logical.php
 *
 * Thin wrapper: delegates to \Adn\Theme\Service\GuideContext.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../src/Service/GuideContext.php';

function adn_guide_get_context( $slug = '' ) {
	return \Adn\Theme\Service\GuideContext::getContext( $slug );
}
