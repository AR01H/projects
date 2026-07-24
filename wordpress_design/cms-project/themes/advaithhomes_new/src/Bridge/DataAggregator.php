<?php
namespace Adn\Theme\Bridge;

defined( 'ABSPATH' ) || exit;

/**
 * Data Aggregator — Collects data from all sources and assembles
 * a unified context array for each page.
 */
class DataAggregator {

	private PluginDataSource $plugin;
	private JsonDataSource $json;

	public function __construct( PluginDataSource $plugin, JsonDataSource $json ) {
		$this->plugin = $plugin;
		$this->json = $json;
	}

	/**
	 * Aggregate all data needed for a page.
	 */
	public function aggregate( string $pageSlug, array $routeParams = [] ): array {
		$cacheKey = "bridge:{$pageSlug}:" . \md5( \serialize( $routeParams ) );

		// Try to get from transient cache
		$cached = \get_transient( $cacheKey );
		if ( $cached !== false ) {
			return $cached;
		}

		$context = [];

		// Plugin data (CMS content)
		$context['navigation'] = $this->plugin->getNavigation();
		$context['settings'] = $this->plugin->getSettings();
		$context['siteNotices'] = $this->plugin->getSiteNotices();
		$context['banners'] = $this->plugin->getBanners();
		$context['spotlights'] = $this->plugin->getSpotlights();
		$context['newsBar'] = $this->plugin->getNewsBar();
		$context['featuredIn'] = $this->plugin->getFeaturedIn();
		$context['reviews'] = $this->plugin->getReviews();
		$context['faqs'] = $this->plugin->getFaqs();
		$context['resources'] = $this->plugin->getResources();

		// Theme-specific data (from JSON files)
		$context['siteChrome'] = $this->json->load( 'site_chrome' );
		$context['homePage'] = $this->json->load( 'home_page' );
		$context['sidebarCards'] = $this->json->load( 'sidebar_cards' );
		$context['postSidebar'] = $this->json->load( 'post_sidebar' );
		$context['terms'] = $this->json->load( 'terms' );

		// Page-specific data
		$context['page'] = $routeParams;

		// Cache for 30 minutes
		\set_transient( $cacheKey, $context, 30 * \MINUTE_IN_SECONDS );

		return $context;
	}

	/**
	 * Get page-specific context.
	 */
	public function getContext( string $pageSlug, array $routeParams = [] ): array {
		return $this->aggregate( $pageSlug, $routeParams );
	}
}
