<?php
/**
 * intermediate/page_ask_expert_logical.php
 *
 * Thin wrapper: delegates to \Adn\Theme\Service\AskExpertContext.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../src/Service/AskExpertContext.php';

function adn_ask_expert_sidebar_data() {
	return \Adn\Theme\Service\AskExpertContext::sidebarData();
}

function adn_ask_expert_get_context() {
	return \Adn\Theme\Service\AskExpertContext::getContext();
}
