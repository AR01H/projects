<?php
namespace VintageSoul\Services\Plugins;

use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

/**
 * FeaturedInBridgeService — Bridges CMS "Featured In" logo strips
 * (admin.php?page=ah-featured-in, wp_options key ah_featured_in_sections)
 * into the theme's own logo-strip-section markup, with fallback to
 * logo-strip.json when no matching section exists yet.
 */
class FeaturedInBridgeService {

	/**
	 * Get a named section's logos mapped to the shape logo-strip-section.php
	 * expects: { name, image, subtitle, url }. Falls back to an empty array
	 * so the caller can fall back to its own JSON.
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function get_items( string $section_id = '' ): array {
		$section = self::get_section( $section_id );
		if ( null === $section ) {
			return array();
		}

		$items = array();
		foreach ( (array) ( $section['logos'] ?? array() ) as $logo ) {
			$logo  = (array) $logo;
			$image = (string) ( $logo['image_url'] ?? '' );
			if ( '' === $image ) {
				continue;
			}
			$items[] = array(
				'name'    => (string) ( $logo['label'] ?? '' ),
				'image'   => UrlHelper::resolve( $image ),
				'subtitle' => '',
				'url'     => (string) ( $logo['link'] ?? '' ),
			);
		}

		return $items;
	}

	/**
	 * Get a section's heading text, or '' when the section doesn't exist.
	 */
	public static function get_heading( string $section_id = '' ): string {
		$section = self::get_section( $section_id );
		return $section ? (string) ( $section['heading'] ?? '' ) : '';
	}

	/**
	 * Get the raw section array by id, or the first section when $section_id
	 * is empty (matches the plugin's own "omit section to show the first
	 * one" convention). Returns null when the plugin data isn't available.
	 *
	 * @return array<string, mixed>|null
	 */
	public static function get_section( string $section_id = '' ): ?array {
		if ( ! class_exists( 'AH_Featured_In_Helper' ) ) {
			return null;
		}

		try {
			if ( '' !== $section_id ) {
				return \AH_Featured_In_Helper::find( $section_id );
			}

			$all = \AH_Featured_In_Helper::get_all();
			return ! empty( $all ) ? (array) $all[0] : null;
		} catch ( \Throwable $e ) {
			return null;
		}
	}
}
