<?php

namespace Adn\Theme\Feature\Tools\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Delegates to intermediate/page_tool_single_logical.php functions.
 * Canonical entry point for single tool/calculator page data.
 */
class ToolSingleController {

	public static function getContext( string $key ): ?array {
		return \Adn\Theme\Service\ToolSingleContext::getContext( $key );
	}
}
