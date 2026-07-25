<?php
/**
 * intermediate/page_guidance_logical.php
 *
 * Thin wrapper: delegates to \Adn\Theme\Service\GuidanceContext.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../src/Feature/Guidance/Service/GuidanceContext.php';

function adn_guidance_get_context() {
	return \Adn\Theme\Service\GuidanceContext::getContext();
}
