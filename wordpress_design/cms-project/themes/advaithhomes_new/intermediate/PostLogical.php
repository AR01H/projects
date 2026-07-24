<?php
/**
 * intermediate/post_logical.php
 *
 * Thin wrapper: delegates to \Adn\Theme\Service\PostContext.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../src/Service/PostContext.php';

function adn_post_get_context() {
	return \Adn\Theme\Service\PostContext::getContext();
}
