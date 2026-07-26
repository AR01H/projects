<?php
/**
 * Post AJAX Handlers — Thin wrappers delegating to OOP class.
 *
 * @package Adn\Theme\Common\Ajax
 */
defined( 'ABSPATH' ) || exit;

function adn_post_related_articles_ajax() {
	\Adn\Theme\Feature\Ajax\PostAjaxHandler::relatedArticles();
}

function adn_post_helpful_ajax() {
	\Adn\Theme\Feature\Ajax\PostAjaxHandler::helpfulAjax();
}
