<?php
/**
 * intermediate/page_expert_single_logical.php
 *
 * Thin wrapper: delegates to \Adn\Theme\Service\ExpertSingleContext.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../src/Service/ExpertSingleContext.php';

function adn_expert_single_get_context( $slug ) {
	return \Adn\Theme\Service\ExpertSingleContext::getContext( $slug );
}
