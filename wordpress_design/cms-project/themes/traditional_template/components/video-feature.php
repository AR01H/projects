<?php
/**
 * Video feature - a poster image with a play badge beside supporting copy.
 *
 * GENERIC: any "watch how it works / see it in action" block. Switch data per
 * page with `source`.
 *
 * PLAYS IN PLACE. The badge is still a real link to the video, so with JS off
 * (or if the URL is a format we do not recognise) it opens the way it always
 * did. When we can identify the video, the link carries the embed URL and
 * initVideoFacade() in common.js swaps the poster for a player on click.
 *
 * Nothing from the video host is loaded until that click - no iframe, no
 * script, no cookie. That is deliberate: an embed present on page load sets
 * third-party cookies on every visitor whether or not they ever press play,
 * which is exactly what a consent banner is supposed to prevent. It is also
 * why YouTube goes through youtube-nocookie.com.
 *
 * Data: { tag, title (em allowed), body, image, alt, video_url, button, points[] }
 */
defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'app_video_embed_url' ) ) {
	/**
	 * Turn a watch URL into an autoplaying embed URL.
	 *
	 * @param string $url Any YouTube or Vimeo link.
	 * @return string Embed URL, or '' when the host is not recognised.
	 */
	function app_video_embed_url( $url ) {
		$url = trim( (string) $url );
		if ( '' === $url ) {
			return '';
		}

		$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
		$path = (string) wp_parse_url( $url, PHP_URL_PATH );
		$id   = '';

		if ( false !== strpos( $host, 'youtu.be' ) ) {
			$id = trim( $path, '/' );
		} elseif ( false !== strpos( $host, 'youtube' ) ) {
			if ( 0 === strpos( $path, '/embed/' ) ) {
				$id = substr( $path, 7 );
			} else {
				$query = (string) wp_parse_url( $url, PHP_URL_QUERY );
				parse_str( $query, $args );
				$id = isset( $args['v'] ) ? (string) $args['v'] : '';
			}
			if ( $id ) {
				return 'https://www.youtube-nocookie.com/embed/' . rawurlencode( $id )
					. '?autoplay=1&rel=0&modestbranding=1&playsinline=1';
			}
			return '';
		} elseif ( false !== strpos( $host, 'vimeo' ) ) {
			$id = trim( $path, '/' );
			if ( preg_match( '/^\d+$/', $id ) ) {
				return 'https://player.vimeo.com/video/' . rawurlencode( $id ) . '?autoplay=1';
			}
			return '';
		}

		if ( $id && preg_match( '/^[A-Za-z0-9_-]{6,20}$/', $id ) ) {
			return 'https://www.youtube-nocookie.com/embed/' . rawurlencode( $id )
				. '?autoplay=1&rel=0&modestbranding=1&playsinline=1';
		}
		return '';
	}
}

$vf_source = ( isset( $source ) && $source ) ? (string) $source : 'video_feature';
$data      = App_Helpers::data( $vf_source );
$title     = ( is_array( $data ) && ! empty( $data['title'] ) ) ? $data['title'] : '';
if ( '' === $title ) {
	return;
}
$tag    = $data['tag']       ?? '';
$body   = $data['body']      ?? '';
$image  = $data['image']     ?? '';
$alt    = $data['alt']       ?? '';
$video  = $data['video_url'] ?? '';
$embed  = app_video_embed_url( $video );
$button = $data['button']    ?? '';
$points = ( ! empty( $data['points'] ) ) ? (array) $data['points'] : array();
?>
<section class="app-videofeat" id="video-feature">
	<div class="container app-videofeat__inner">

		<div class="app-videofeat__media"<?php echo $embed ? ' data-nt-video-host' : ''; ?>>
			<?php if ( $image ) : ?>
				<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy">
			<?php endif; ?>
			<?php if ( $video ) : ?>
				<a class="app-videofeat__play" href="<?php echo esc_url( $video ); ?>"
				   target="_blank" rel="noopener noreferrer"
				   <?php echo $embed ? 'data-nt-video="' . esc_url( $embed ) . '"' : ''; ?>
				   aria-label="<?php esc_attr_e( 'Play video', NT_TEXT_DOMAIN ); ?>">
					<span aria-hidden="true">&#9654;</span>
				</a>
			<?php endif; ?>
		</div>

		<div class="app-videofeat__copy">
			<?php if ( $tag ) : ?><div class="app-section-tag"><?php echo esc_html( $tag ); ?></div><?php endif; ?>
			<h2 class="app-videofeat__title"><?php echo wp_kses( $title, array( 'em' => array() ) ); ?></h2>
			<?php if ( $body ) : ?><p class="app-videofeat__body"><?php echo esc_html( $body ); ?></p><?php endif; ?>

			<?php if ( ! empty( $points ) ) : ?>
				<ul class="app-videofeat__points">
					<?php foreach ( $points as $point ) : ?>
						<li><?php echo esc_html( (string) $point ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $button && $video ) : ?>
				<a class="btn app-videofeat__btn" href="<?php echo esc_url( $video ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html( $button ); ?>
				</a>
			<?php endif; ?>
		</div>

	</div>
</section>
