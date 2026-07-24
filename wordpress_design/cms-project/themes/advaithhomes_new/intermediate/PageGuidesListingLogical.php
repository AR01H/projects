<?php
/**
 * intermediate/page_guides_listing_logical.php
 *
 * Thin wrapper: delegates to \Adn\Theme\Service\GuidesListingContext.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../src/Service/GuidesListingContext.php';

function adn_guides_listing_get_context( $slug = '' ) {
	return \Adn\Theme\Service\GuidesListingContext::getContext( $slug );
}

function adn_guides_listing_cms_items( $articles ) {
	return \Adn\Theme\Service\GuidesListingContext::cmsItems( $articles );
}
