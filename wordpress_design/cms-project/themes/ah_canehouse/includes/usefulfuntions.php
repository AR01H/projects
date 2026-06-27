<?php
defined( 'ABSPATH' ) || exit;

// ── Background execution after HTTP response ─────────────────────────────────
//
// Usage:
//   ch_run_after_response( function() use ( $data ) {
//       AH_Workflow_Manager::evaluate( CH_Rules::BOOKING_REQUEST, $data, true );
//   } );
//   wp_send_json_success( [...] );   ← response goes to browser first
//
// How it works:
//   wp_send_json_success() → wp_die() → PHP shutdown → fastcgi_finish_request()
//   closes the HTTP connection while PHP continues running the callback.
//   On non-FPM servers (mod_php) the callback still runs but the browser waits.

/**
 * Queue a callable to run after the HTTP response has been sent to the browser.
 * Call this BEFORE wp_send_json_success() / wp_send_json_error().
 */
function ch_run_after_response( callable $fn ): void {
	add_action( 'shutdown', static function () use ( $fn ) {
		// PHP-FPM: flush + close the connection, keep PHP alive
		if ( function_exists( 'fastcgi_finish_request' ) ) {
			fastcgi_finish_request();
		} else {
			// Apache mod_php fallback: set headers and flush buffers
			ignore_user_abort( true );
			if ( ob_get_level() ) ob_end_flush();
			flush();
		}

		set_time_limit( 60 ); // give background work up to 60 s
		$fn();
	} );
}

// ── Asset URL resolution & type detection ────────────────────────────────────
//
// Usage examples:
//   ch_resolve_asset_url('/wp-content/uploads/photo.jpg')  → https://site.com/wp-content/uploads/photo.jpg
//   ch_resolve_asset_url('images/logo.svg')                → https://site.com/wp-content/themes/ah_canehouse/images/logo.svg
//   ch_resolve_asset_url('https://cdn.example.com/x.jpg') → https://cdn.example.com/x.jpg
//
//   ch_asset_type('https://youtu.be/abc123')   → 'youtube'
//   ch_asset_type('/uploads/promo.mp4')         → 'video'
//   ch_asset_type('/uploads/photo.webp')        → 'image'
//
//   ch_render_media('/uploads/hero.jpg', ['alt' => 'Hero', 'class' => 'hero-img'])
//   ch_render_media('https://youtu.be/abc123',  ['title' => 'Promo video'])
//   ch_render_media('/uploads/reel.mp4',        ['autoplay' => true, 'loop' => true])

/**
 * Detect whether $path is an external URL or internal.
 * External = starts with http/https/protocol-relative (//).
 */
function ch_is_external_url( string $path ): bool {
	return preg_match( '#^(https?:)?//#i', $path ) === 1;
}

/**
 * Resolve any path/URL to a full absolute URL.
 *
 * Rules:
 *   - Already absolute (http/https/protocol-relative) → return as-is
 *   - Starts with /  → site-relative  → home_url() + path
 *   - No leading /   → theme-relative → get_template_directory_uri() / path
 */
function ch_resolve_asset_url( string $path ): string {
	$path = trim( $path );
	if ( $path === '' ) return '';

	if ( ch_is_external_url( $path ) ) {
		return esc_url( $path );
	}

	if ( str_starts_with( $path, '/' ) ) {
		return esc_url( home_url( $path ) );
	}

	return esc_url( get_template_directory_uri() . '/' . ltrim( $path, '/' ) );
}

/**
 * Detect the media type of a path/URL.
 *
 * Returns one of: 'youtube' | 'vimeo' | 'image' | 'video' | 'audio' | 'unknown'
 *
 * @param string $fallback  Returned when the type cannot be inferred from the extension.
 *                          Pass 'image' in contexts where extensionless CDN URLs (e.g. Unsplash)
 *                          should be treated as images.
 */
function ch_asset_type( string $path, string $fallback = 'unknown' ): string {
	$path = trim( $path );
	if ( $path === '' ) return $fallback;

	// Video platforms (check before extension so embed URLs match first)
	if ( preg_match( '#(youtube\.com/(watch|embed|shorts)|youtu\.be/)#i', $path ) ) return 'youtube';
	if ( preg_match( '#vimeo\.com/#i', $path ) ) return 'vimeo';

	// Strip query string for extension check
	$clean = strtolower( preg_replace( '/[?#].*$/', '', $path ) );
	$ext   = pathinfo( $clean, PATHINFO_EXTENSION );

	$images = [ 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif', 'bmp', 'ico' ];
	$videos = [ 'mp4', 'webm', 'ogg', 'ogv', 'mov', 'avi', 'm4v' ];
	$audios = [ 'mp3', 'wav', 'flac', 'aac', 'm4a', 'ogg' ];

	if ( in_array( $ext, $images, true ) ) return 'image';
	if ( in_array( $ext, $videos, true ) ) return 'video';
	if ( in_array( $ext, $audios, true ) ) return 'audio';

	return $fallback;
}

/**
 * Render a media asset (image / video / embed) from any path or URL.
 *
 * @param string $path   Any URL or path (external, site-relative, theme-relative).
 * @param array  $args {
 *   @type string $alt       Alt text for images (default: '').
 *   @type string $class     CSS class(es) applied to the element.
 *   @type string $title     Title attribute / iframe title.
 *   @type bool   $autoplay  Video: autoplay (default: false).
 *   @type bool   $loop      Video: loop (default: false).
 *   @type bool   $muted     Video: muted (default: true when autoplay is true).
 *   @type bool   $controls  Video: show controls (default: true).
 *   @type string $poster    Video: poster image URL.
 *   @type bool   $echo      Whether to echo output (default: true). Set false to return string.
 * }
 */
function ch_render_media( string $path, array $args = [] ): string {
	$url  = ch_resolve_asset_url( $path );
	$type = ch_asset_type( $path );

	$class    = esc_attr( $args['class']   ?? '' );
	$alt      = esc_attr( $args['alt']     ?? '' );
	$title    = esc_attr( $args['title']   ?? '' );
	$autoplay = ! empty( $args['autoplay'] );
	$loop     = ! empty( $args['loop'] );
	$controls = $args['controls'] ?? true;
	$muted    = $args['muted']    ?? $autoplay; // auto-mute when autoplay requested
	$poster   = isset( $args['poster'] ) ? ch_resolve_asset_url( $args['poster'] ) : '';

	$html = '';

	switch ( $type ) {

		case 'image':
			$html = sprintf(
				'<img src="%s" alt="%s"%s>',
				$url,
				$alt,
				$class ? ' class="' . $class . '"' : ''
			);
			break;

		case 'video':
			$attrs  = $class   ? ' class="' . $class . '"' : '';
			$attrs .= $poster  ? ' poster="' . esc_attr( $poster ) . '"' : '';
			$attrs .= $controls ? ' controls' : '';
			$attrs .= $autoplay ? ' autoplay'  : '';
			$attrs .= $loop     ? ' loop'      : '';
			$attrs .= $muted    ? ' muted'     : '';
			$attrs .= ' playsinline';

			$ext   = strtolower( pathinfo( preg_replace( '/[?#].*$/', '', $path ), PATHINFO_EXTENSION ) );
			$mime  = match ( $ext ) {
				'webm'       => 'video/webm',
				'ogg','ogv'  => 'video/ogg',
				default      => 'video/mp4',
			};

			$html = sprintf(
				'<video%s><source src="%s" type="%s"></video>',
				$attrs,
				$url,
				esc_attr( $mime )
			);
			break;

		case 'youtube':
			$embed_id = ch_youtube_id( $path );
			$src      = 'https://www.youtube.com/embed/' . $embed_id;
			if ( $autoplay ) $src .= '?autoplay=1&mute=1';
			$html = sprintf(
				'<iframe src="%s" title="%s" class="%s" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>',
				esc_url( $src ),
				$title ?: 'YouTube video',
				$class ?: 'ch-embed ch-embed--youtube'
			);
			break;

		case 'vimeo':
			$embed_id = ch_vimeo_id( $path );
			$src      = 'https://player.vimeo.com/video/' . $embed_id;
			if ( $autoplay ) $src .= '?autoplay=1&muted=1';
			$html = sprintf(
				'<iframe src="%s" title="%s" class="%s" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen loading="lazy"></iframe>',
				esc_url( $src ),
				$title ?: 'Vimeo video',
				$class ?: 'ch-embed ch-embed--vimeo'
			);
			break;

		case 'audio':
			$attrs  = $class    ? ' class="' . $class . '"' : '';
			$attrs .= $controls ? ' controls' : '';
			$attrs .= $autoplay ? ' autoplay'  : '';
			$attrs .= $loop     ? ' loop'      : '';
			$html = sprintf( '<audio%s><source src="%s"></audio>', $attrs, $url );
			break;

		default:
			// Fallback: plain anchor link for unknown types
			$html = sprintf(
				'<a href="%s"%s>%s</a>',
				$url,
				$class ? ' class="' . $class . '"' : '',
				$title ?: basename( $path )
			);
			break;
	}

	$echo = $args['echo'] ?? true;
	if ( $echo ) {
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	return $html;
}

/**
 * Extract YouTube video ID from any YouTube URL format.
 */
function ch_youtube_id( string $url ): string {
	if ( preg_match( '#youtu\.be/([^?&]+)#i', $url, $m ) ) return $m[1];
	if ( preg_match( '#[?&]v=([^&]+)#i',      $url, $m ) ) return $m[1];
	if ( preg_match( '#embed/([^?&/]+)#i',     $url, $m ) ) return $m[1];
	if ( preg_match( '#shorts/([^?&/]+)#i',    $url, $m ) ) return $m[1];
	return '';
}

/**
 * Extract Vimeo video ID from any Vimeo URL format.
 */
function ch_vimeo_id( string $url ): string {
	if ( preg_match( '#vimeo\.com/(?:video/)?(\d+)#i', $url, $m ) ) return $m[1];
	return '';
}
