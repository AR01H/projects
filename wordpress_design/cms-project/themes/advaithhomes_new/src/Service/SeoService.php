<?php

namespace Adn\Theme\Service;

defined( 'ABSPATH' ) || exit;

/**
 * SEO Service — outputs meta tags, Open Graph, Twitter Card, JSON-LD.
 * Wraps the procedural functions from includes/seo.php.
 */
class SeoService {

	/**
	 * Output all SEO meta tags in wp_head.
	 */
	public static function outputMeta(): void {
		adn_seo_head_output();
	}

	/**
	 * Get meta title for the current page.
	 */
	public static function getTitle(): string {
		return adn_seo_get_title();
	}

	/**
	 * Get meta description for the current page.
	 */
	public static function getDescription(): string {
		return adn_seo_get_description();
	}

	/**
	 * Get OG image for the current page.
	 */
	public static function getOgImage(): string {
		return adn_seo_get_og_image();
	}
}
