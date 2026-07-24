<?php
/**
 * intermediate/page_contact_logical.php
 *
 * Thin wrapper: delegates to \Adn\Theme\Service\ContactContext.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../src/Service/ContactContext.php';

function adn_contact_get_context() {
	return \Adn\Theme\Service\ContactContext::getContext();
}
