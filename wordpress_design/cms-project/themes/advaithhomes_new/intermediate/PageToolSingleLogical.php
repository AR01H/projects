<?php
/**
 * intermediate/page_tool_single_logical.php
 *
 * Thin wrapper: delegates to \Adn\Theme\Service\ToolSingleContext.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../src/Service/ToolSingleContext.php';

function adn_calculator_single_get_context( $key ) {
	return \Adn\Theme\Service\ToolSingleContext::getContext( $key );
}
