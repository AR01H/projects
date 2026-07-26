<?php
/**
 * Sidebar Helpers — Thin wrappers delegating to OOP class.
 *
 * @package Adn\Theme\Includes
 */
defined( 'ABSPATH' ) || exit;

if ( ! defined( 'ADN_FAQS_CACHE_TTL' ) ) {
	define( 'ADN_FAQS_CACHE_TTL', HOUR_IN_SECONDS );
}

function adn_get_page_sidebar_data( int $page_id = 0, int $faq_limit = 3 ): array {
	return \Adn\Theme\Helper\SidebarHelper::getPageSidebarData( $page_id, $faq_limit );
}

function adn_get_cms_page_id( string $type ): int {
	return \Adn\Theme\Helper\SidebarHelper::getCmsPageId( $type );
}

function adn_faqs_cache_key( int $page_id, bool $fallback_global ): string {
	return \Adn\Theme\Helper\SidebarHelper::faqsCacheKey( $page_id, $fallback_global );
}

function adn_get_page_faqs_grouped( int $page_id = 0, bool $fallback_global = true ): array {
	return \Adn\Theme\Helper\SidebarHelper::getPageFaqsGrouped( $page_id, $fallback_global );
}

function adn_mark_faqs_shown( array $groups ): void {
	\Adn\Theme\Helper\SidebarHelper::markFaqsShown( $groups );
}

function adn_faqs_already_shown( array $merge = array() ): array {
	return \Adn\Theme\Helper\SidebarHelper::faqsAlreadyShown( $merge );
}

function adn_render_slug_attached_faqs(): void {
	\Adn\Theme\Helper\SidebarHelper::renderSlugAttachedFaqs();
}

function adn_purge_faqs_cache(): void {
	\Adn\Theme\Helper\SidebarHelper::purgeFaqsCache();
}
add_action( 'ah_faqs_changed', 'adn_purge_faqs_cache' );
