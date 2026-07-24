<?php
/**
 * intermediate/page_topic_category_logical.php
 *
 * Thin wrapper: delegates to \Adn\Theme\Service\TopicCategoryContext.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../src/Service/TopicCategoryContext.php';

function adn_topic_category_repository(): \Adn\Theme\Repository\TopicCategoryRepository {
	return \Adn\Theme\Service\TopicCategoryContext::repository();
}

function adn_topic_category_get_context() {
	return \Adn\Theme\Service\TopicCategoryContext::getContext();
}
