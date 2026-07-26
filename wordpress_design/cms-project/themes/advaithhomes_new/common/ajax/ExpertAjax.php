<?php
/**
 * Expert AJAX Handlers — Thin wrappers delegating to OOP class.
 *
 * @package Adn\Theme\Common\Ajax
 */
defined( 'ABSPATH' ) || exit;

function adn_expert_full_page_render() {
	\Adn\Theme\Feature\Ajax\ExpertAjaxHandler::fullPageRender();
}

function adn_expert_contact_ajax() {
	\Adn\Theme\Feature\Ajax\ExpertAjaxHandler::contactAjax();
}

function adn_expert_unlock_ajax() {
	\Adn\Theme\Feature\Ajax\ExpertAjaxHandler::unlockAjax();
}
