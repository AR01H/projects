<?php
/**
 * intermediate/page_news_logical.php
 *
 * Thin wrapper: delegates to \Adn\Theme\Service\NewsContext.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../src/Service/NewsContext.php';

function adn_news_get_context() {
	return \Adn\Theme\Service\NewsContext::getContext();
}

function adn_newsbar_item_thumb( $image_id, $size = 'medium' ) {
	return \Adn\Theme\Service\NewsContext::newsbarItemThumb( $image_id, $size );
}

function adn_news_newsbar_featured( $item ) {
	return \Adn\Theme\Service\NewsContext::newsbarFeatured( $item );
}

function adn_news_newsbar_grid_items( $rows ) {
	return \Adn\Theme\Service\NewsContext::newsbarGridItems( $rows );
}
