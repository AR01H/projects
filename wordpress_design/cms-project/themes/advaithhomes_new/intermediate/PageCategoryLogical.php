<?php
/**
 * intermediate/page_category_logical.php
 *
 * Thin wrapper: delegates to \Adn\Theme\Service\CategoryContext.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../src/Service/CategoryContext.php';

function adn_category_repository(): \Adn\Theme\Repository\CategoryRepository {
	return \Adn\Theme\Service\CategoryContext::repository();
}

function adn_category_cms_guides( $slug ) {
	return \Adn\Theme\Service\CategoryContext::cmsGuides( $slug );
}

function adn_category_cms_news( $limit = 3 ) {
	return \Adn\Theme\Service\CategoryContext::cmsNews( $limit );
}

function adn_category_latest_updates( $slug, $limit = 4 ) {
	return \Adn\Theme\Service\CategoryContext::latestUpdates( $slug, $limit );
}

function adn_category_parent_term( $slug ) {
	return \Adn\Theme\Service\CategoryContext::parentTerm( $slug );
}

function adn_category_get_context( $slug = '' ) {
	return \Adn\Theme\Service\CategoryContext::getContext( $slug );
}
