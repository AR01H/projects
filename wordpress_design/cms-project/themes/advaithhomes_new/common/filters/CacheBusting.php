<?php
/**
 * Cache Busting & Lazy Loading Filters
 *
 * @package Adn\Theme\Common\Filters
 */
defined( 'ABSPATH' ) || exit;

/**
 * Add loading="lazy" and decoding="async" to <img> tags in post content.
 */
function adn_add_img_lazy_attr( string $content ): string {
	if ( false === strpos( $content, '<img' ) ) {
		return $content;
	}
	return \preg_replace_callback(
		'/<img(?![^>]*\bloading=)[^>]*>/i',
		function ( $m ) {
			$tag = $m[0];
			if ( false !== strpos( $tag, 'loading=' ) ) {
				return $tag;
			}
			$tag = \rtrim( $tag, '/' . '>' );
			$tag = \rtrim( $tag );
			return $tag . ' loading="lazy" decoding="async">';
		},
		$content
	);
}

/**
 * Append version to attachment URLs.
 */
function adn_cache_bust_attachment_url( $url ) {
	if ( ! \defined( 'LOCAL_CACHE_VERSION' ) ) {
		return $url;
	}
	if ( false !== strpos( $url, 'v=' ) ) {
		return $url;
	}
	$sep = ( false === strpos( $url, '?' ) ) ? '?v=' : '&v=';
	return $url . $sep . LOCAL_CACHE_VERSION;
}

/**
 * Append version to attachment image src arrays.
 */
function adn_cache_bust_attachment_image_src( $src ) {
	if ( ! \defined( 'LOCAL_CACHE_VERSION' ) ) {
		return $src;
	}
	if ( empty( $src ) || ! \is_array( $src ) ) {
		return $src;
	}
	$url = $src[0];
	if ( false !== strpos( $url, 'v=' ) ) {
		return $src;
	}
	$sep = ( false === strpos( $url, '?' ) ) ? '?v=' : '&v=';
	$src[0] = $url . $sep . LOCAL_CACHE_VERSION;
	return $src;
}

/**
 * Append version to images in post content.
 */
function adn_cache_bust_content_images( $content ) {
	if ( ! \defined( 'LOCAL_CACHE_VERSION' ) ) {
		return $content;
	}
	$pattern = '#(<img[^>]+src=["\"])([^"\"]+)(["\"])#i';
	return \preg_replace_callback( $pattern, function ( $m ) {
		$url = $m[2];
		if ( \preg_match( '#^https?://#i', $url ) && false === strpos( $url, \home_url() ) ) {
			return $m[0];
		}
		if ( false !== strpos( $url, 'v=' ) ) {
			return $m[0];
		}
		$sep = ( false === strpos( $url, '?' ) ) ? '?v=' : '&v=';
		$url = $url . $sep . LOCAL_CACHE_VERSION;
		return $m[1] . $url . $m[3];
	}, $content );
}

/**
 * Append version to background-image URLs in inline styles.
 */
function adn_cache_bust_content_bg_images( $content ) {
	if ( ! \defined( 'LOCAL_CACHE_VERSION' ) ) {
		return $content;
	}
	$pattern = '#(background(?:-image)?\s*:\s*url\(["\']?)([^"\')]+)(["\']?\))#i';
	return \preg_replace_callback( $pattern, function ( $m ) {
		$url = $m[2];
		if ( \preg_match( '#^https?://#i', $url ) && false === strpos( $url, \home_url() ) ) {
			return $m[0];
		}
		if ( false !== strpos( $url, 'v=' ) ) {
			return $m[0];
		}
		$sep = ( false === strpos( $url, '?' ) ) ? '?v=' : '&v=';
		$url = $url . $sep . LOCAL_CACHE_VERSION;
		return $m[1] . $url . $m[3];
	}, $content );
}
