<?php
namespace VintageSoul\Services\Plugins;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

/**
 * PageBridgeService — Bridges CMS Pages Manager (admin.php?page=ah-pages)
 * with subpage hero headers, titles, excerpts, featured images/videos, and content.
 */
class PageBridgeService {

	private static array $cache = array();

	/**
	 * Get WP_Post object for a page slug or ID with request-level caching.
	 *
	 * @param string|int $slug_or_id
	 * @return \WP_Post|null
	 */
	public static function get_page( $slug_or_id ): ?\WP_Post {
		$cache_key = (string) $slug_or_id;
		if ( array_key_exists( $cache_key, self::$cache ) ) {
			return self::$cache[ $cache_key ];
		}

		$post = null;

		if ( is_numeric( $slug_or_id ) && (int) $slug_or_id > 0 ) {
			if ( function_exists( 'get_post' ) ) {
				$post = get_post( (int) $slug_or_id );
			}
		} else {
			// Slug + its alternates from config/routes.php (the same list
			// RouteService::key_for_slug() matches against) - not a second,
			// separately-hardcoded list here.
			$slug           = sanitize_title( (string) $slug_or_id );
			$slugs_to_check = array_values( array_unique( array_merge( array( $slug ), RouteService::alternates( $slug ) ) ) );

			foreach ( $slugs_to_check as $s ) {
				if ( function_exists( 'get_page_by_path' ) ) {
					$post = get_page_by_path( $s, OBJECT, 'page' );
				}

				if ( ! $post && isset( $GLOBALS['wpdb'] ) ) {
					global $wpdb;
					$id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'page' AND post_status IN ('publish','draft') LIMIT 1", $s ) );
					if ( $id && function_exists( 'get_post' ) ) {
						$post = get_post( (int) $id );
					}
				}

				if ( $post instanceof \WP_Post ) {
					break;
				}
			}
		}

		self::$cache[ $cache_key ] = ( $post instanceof \WP_Post ) ? $post : null;
		return self::$cache[ $cache_key ];
	}

	/**
	 * Resolve dynamic hero data for a subpage (title, tag, sub, image/video).
	 *
	 * @param string|int $slug_or_id Slug or ID of the page (e.g. 'about', 'history', 'events', 'franchise', 'contact', 'blog')
	 * @param array<string, mixed> $fallback_hero Fallback hero data from JSON
	 * @return array<string, mixed>
	 */
	public static function resolve_hero( $slug_or_id, array $fallback_hero = array() ): array {
		$page = self::get_page( $slug_or_id );

		$tag   = (string) ( $fallback_hero['tag'] ?? ( $fallback_hero['eyebrow'] ?? '' ) );
		$title = (string) ( $fallback_hero['title'] ?? '' );
		$sub   = (string) ( $fallback_hero['sub'] ?? ( $fallback_hero['subtitle'] ?? ( $fallback_hero['description'] ?? '' ) ) );
		$image = (string) ( $fallback_hero['image'] ?? ( $fallback_hero['src'] ?? '' ) );
		$video = (string) ( $fallback_hero['video'] ?? '' );

		if ( $page instanceof \WP_Post && 'trash' !== $page->post_status ) {
			// 1. Dynamic Title from CMS Pages Manager
			if ( ! empty( trim( (string) $page->post_title ) ) && 'Auto Draft' !== $page->post_title ) {
				$title = (string) $page->post_title;
			}

			// 2. Dynamic Subtitle / Excerpt / Description from CMS Pages Manager
			if ( ! empty( trim( (string) $page->post_excerpt ) ) ) {
				$sub = (string) $page->post_excerpt;
			}

			// 3. Dynamic Featured Image or Video from CMS Pages Manager (_thumbnail_id)
			$thumb_id = (int) ( function_exists( 'get_post_thumbnail_id' ) ? get_post_thumbnail_id( $page->ID ) : 0 );
			if ( ! $thumb_id && isset( $GLOBALS['wpdb'] ) ) {
				$thumb_id = (int) get_post_meta( $page->ID, '_thumbnail_id', true );
			}

			if ( $thumb_id > 0 && function_exists( 'wp_get_attachment_url' ) ) {
				$att_url = wp_get_attachment_url( $thumb_id );
				if ( $att_url ) {
					$ext = strtolower( (string) pathinfo( (string) wp_parse_url( $att_url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
					$is_video = in_array( $ext, array( 'mp4', 'webm', 'ogv', 'ogg', 'mov', 'm4v' ), true );
					if ( ! $is_video && function_exists( 'get_post_mime_type' ) ) {
						$mime = (string) get_post_mime_type( $thumb_id );
						$is_video = 0 === strpos( $mime, 'video/' );
					}

					if ( $is_video ) {
						$video = $att_url;
					} else {
						$image = $att_url;
						$video = '';
					}
				}
			}
		}

		if ( '' !== $image && 0 !== strpos( $image, 'http' ) ) {
			$image = UrlHelper::resolve( $image );
		}

		$result = array_merge( $fallback_hero, array(
			'tag'   => $tag,
			'title' => $title,
			'sub'   => $sub,
			'image' => $image,
			'video' => $video,
		) );

		return $result;
	}

	/**
	 * Get page body content if customized in CMS Pages Manager.
	 *
	 * @param string|int $slug_or_id
	 * @param string $fallback
	 * @return string
	 */
	public static function get_page_content( $slug_or_id, string $fallback = '' ): string {
		$page = self::get_page( $slug_or_id );
		if ( $page instanceof \WP_Post && ! empty( trim( (string) $page->post_content ) ) ) {
			return (string) $page->post_content;
		}
		return $fallback;
	}
}
