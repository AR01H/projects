<?php
namespace VintageSoul\Services\Plugins;

defined( 'ABSPATH' ) || exit;

/**
 * ClientStoriesBridgeService — Bridges the CMS "Showcase Gallery"
 * (admin.php?page=ah-client-stories, tables ah_client_stories_header &
 * ah_client_gallery) into the theme's "Look Back In Time" gallery section.
 *
 * That admin only stores a header (heading/description) and plain gallery
 * images (no per-image title/caption/category) - the video links tab has no
 * matching slot in the theme's gallery section, so it isn't surfaced here.
 */
class ClientStoriesBridgeService {

	/**
	 * Gallery images mapped to the shape look-back-in-time-section.php
	 * expects: { image }. Empty when the header's "Visible" toggle is off,
	 * the page/header doesn't exist yet, or the plugin class isn't loaded.
	 *
	 * @return array<int, array<string, string>>
	 */
	public static function get_gallery_items(): array {
		$data = self::get_data();

		$items = array();
		foreach ( (array) ( $data['images'] ?? array() ) as $row ) {
			$row      = (array) $row;
			$image_id = (int) ( $row['image_id'] ?? 0 );
			if ( ! $image_id ) {
				continue;
			}
			$url = wp_get_attachment_image_url( $image_id, 'large' );
			if ( ! $url ) {
				continue;
			}
			$items[] = array( 'image' => $url );
		}

		return $items;
	}

	/**
	 * The section's heading/description, or '' when unavailable.
	 *
	 * @return array{heading: string, description: string}
	 */
	public static function get_header(): array {
		$data = self::get_data();
		return array(
			'heading'     => (string) ( $data['heading'] ?? '' ),
			'description' => (string) ( $data['description'] ?? '' ),
		);
	}

	/**
	 * @return array{heading: string, description: string, images: array, videos: array}
	 */
	private static function get_data(): array {
		$empty = array( 'heading' => '', 'description' => '', 'images' => array(), 'videos' => array() );

		if ( ! class_exists( 'AH_Client_Stories_Model' ) ) {
			return $empty;
		}

		try {
			return ( new \AH_Client_Stories_Model() )->get_reviews_page_gallery();
		} catch ( \Throwable $e ) {
			return $empty;
		}
	}
}
