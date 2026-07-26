<?php
/**
 * Comment AJAX Handlers — Thin wrappers delegating to OOP class.
 *
 * @package Adn\Theme\Common\Ajax
 */
defined( 'ABSPATH' ) || exit;

function adn_moderate_comment_ajax() {
	\Adn\Theme\Feature\Ajax\CommentAjaxHandler::moderateAjax();
}

function adn_ajax_submit_comment() {
	\Adn\Theme\Feature\Ajax\CommentAjaxHandler::submitComment();
}

function adn_ajax_load_comments() {
	\Adn\Theme\Feature\Ajax\CommentAjaxHandler::loadComments();
}
