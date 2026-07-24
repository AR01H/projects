<?php
/**
 * intermediate/page_home_logical.php - Home page container logic.
 *
 * Thin wrapper: delegates to \Adn\Theme\Service\HomeContext.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../src/Service/HomeContext.php';

function adn_home_repository(): \Adn\Theme\Repository\HomeRepository {
	return \Adn\Theme\Service\HomeContext::repository();
}

function adn_home_get_context( $skip = array() ) {
	return \Adn\Theme\Service\HomeContext::getContext( $skip );
}

function adn_home_get_fragment_context( $section ) {
	return \Adn\Theme\Service\HomeContext::getFragmentContext( $section );
}

function adn_home_section_visible( $key ) {
	return \Adn\Theme\Service\HomeContext::sectionVisible( $key );
}

function adn_home_apply_hero_overrides( $hero, $opt ) {
	return \Adn\Theme\Service\HomeContext::applyHeroOverrides( $hero, $opt );
}

function adn_home_cms_journey_cards() {
	return \Adn\Theme\Service\HomeContext::cmsJourneyCards();
}

function adn_home_cms_guide_items() {
	return \Adn\Theme\Service\HomeContext::cmsGuideItems();
}

function adn_home_cms_news_items() {
	return \Adn\Theme\Service\HomeContext::cmsNewsItems();
}

function adn_home_cms_regulations_items() {
	return \Adn\Theme\Service\HomeContext::cmsRegulationsItems();
}

function adn_home_cms_hot_topics_items() {
	return \Adn\Theme\Service\HomeContext::cmsHotTopicsItems();
}
