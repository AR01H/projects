<?php
namespace VintageSoul\Support;

defined( 'ABSPATH' ) || exit;

final class UrlHelper {

	public static function resolve( string $url ): string {
		$url = trim( $url );
		if ( '' === $url ) {
			return '';
		}

		if ( '#' === $url[0] || preg_match( '#^(https?:)?//#i', $url ) || str_starts_with( $url, 'mailto:' ) || str_starts_with( $url, 'tel:' ) ) {
			return $url;
		}

		if ( str_starts_with( $url, 'assets/' ) || str_starts_with( $url, 'static/' ) || preg_match( '/\.(jpe?g|png|gif|webp|svg|ico|mp4|webm)$/i', $url ) ) {
			return VINTAGESOUL_URI . '/' . ltrim( $url, '/' );
		}

		return home_url( '/' . ltrim( $url, '/' ) );
	}

	/**
	 * Converts standard YouTube/Vimeo/Instagram/Facebook/TikTok links into playable embed URLs.
	 */
	public static function resolve_video_embed( string $url ): string {
		$url = trim( $url );
		if ( '' === $url ) {
			return '';
		}

		// 1. YouTube watch, shorts, or youtu.be links
		if ( preg_match( '/(?:youtube\.com\/(?:watch\?v=|shorts\/|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i', $url, $matches ) ) {
			return 'https://www.youtube-nocookie.com/embed/' . $matches[1] . '?autoplay=0&rel=0&modestbranding=1';
		}

		// 2. Instagram Reel or Post links
		if ( preg_match( '/instagram\.com\/(reel|p)\/([a-zA-Z0-9_-]+)/i', $url, $matches ) ) {
			return 'https://www.instagram.com/' . strtolower( $matches[1] ) . '/' . $matches[2] . '/embed/';
		}

		// 3. Vimeo links
		if ( preg_match( '/vimeo\.com\/(?:video\/)?([0-9]+)/i', $url, $matches ) ) {
			return 'https://player.vimeo.com/video/' . $matches[1] . '?autoplay=0&title=0&byline=0';
		}

		// 4. Facebook Posts, Photos, Reels & Video links
		if ( str_contains( $url, 'facebook.com' ) ) {
			if ( str_contains( $url, '/videos/' ) || str_contains( $url, '/watch/' ) || str_contains( $url, '/reel/' ) ) {
				return 'https://www.facebook.com/plugins/video.php?href=' . rawurlencode( $url ) . '&show_text=0&autoplay=0';
			}
			return 'https://www.facebook.com/plugins/post.php?href=' . rawurlencode( $url ) . '&show_text=true';
		}

		// 5. TikTok links
		if ( preg_match( '/tiktok\.com\/@[^\/]+\/video\/([0-9]+)/i', $url, $matches ) ) {
			return 'https://www.tiktok.com/embed/v2/' . $matches[1];
		}

		return self::resolve( $url );
	}

	/**
	 * Extracts YouTube video thumbnail or resolves image URL.
	 */
	public static function resolve_thumbnail( string $image_url, string $video_url = '' ): string {
		$image_url = trim( $image_url );
		if ( '' !== $image_url ) {
			return self::resolve( $image_url );
		}

		// Extract YouTube Thumbnail from video link if image is omitted
		if ( '' !== $video_url && preg_match( '/(?:youtube\.com\/(?:watch\?v=|shorts\/|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i', $video_url, $matches ) ) {
			return 'https://img.youtube.com/vi/' . $matches[1] . '/hqdefault.jpg';
		}

		return self::resolve( 'assets/images/sugarcane/hero_juice.jpg' );
	}
}
