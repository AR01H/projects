<?php
/**
 * intermediate/page_tools_logical.php
 *
 * Thin wrapper: delegates to \Adn\Theme\Service\ToolsContext.
 */

defined( 'ABSPATH' ) || exit;

require_once ADN_THEME_DIR . '/intermediate/PageHomeLogical.php';
require_once __DIR__ . '/../src/Service/ToolsContext.php';

function adn_calculators_get_context() {
	return \Adn\Theme\Service\ToolsContext::getContext();
}
