<?php
/**
 * Video feature - a poster image with a play badge beside supporting copy.
 *
 * GENERIC: any "watch how it works / see it in action" block. The play badge is
 * a plain link to the video URL (no player JS, nothing to break); swap the URL
 * for YouTube/Vimeo/self-hosted as needed. Switch data per page with `source`.
 * Data: { tag, title (em allowed), body, image, alt, video_url, button, points[] }
 */
defined( 'ABSPATH' ) || exit;

$vf_source = ( isset( $source ) && $source ) ? (string) $source : 'video_feature';
$data      = nt_data( $vf_source );
$title     = ( is_array( $data ) && ! empty( $data['title'] ) ) ? $data['title'] : '';
if ( '' === $title ) {
	return;
}
$tag    = $data['tag']       ?? '';
$body   = $data['body']      ?? '';
$image  = $data['image']     ?? '';
$alt    = $data['alt']       ?? '';
$video  = $data['video_url'] ?? '';
$button = $data['button']    ?? '';
$points = ( ! empty( $data['points'] ) ) ? (array) $data['points'] : array();
?>
<section class="nt-videofeat" id="video-feature">
	<div class="container nt-videofeat__inner">

		<div class="nt-videofeat__media">
			<?php if ( $image ) : ?>
				<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $alt ); ?>" loading="lazy">
			<?php endif; ?>
			<?php if ( $video ) : ?>
				<a class="nt-videofeat__play" href="<?php echo esc_url( $video ); ?>"
				   target="_blank" rel="noopener noreferrer"
				   aria-label="<?php esc_attr_e( 'Play video', NT_TEXT_DOMAIN ); ?>">
					<span aria-hidden="true">&#9654;</span>
				</a>
			<?php endif; ?>
		</div>

		<div class="nt-videofeat__copy">
			<?php if ( $tag ) : ?><div class="nt-section-tag"><?php echo esc_html( $tag ); ?></div><?php endif; ?>
			<h2 class="nt-videofeat__title"><?php echo wp_kses( $title, array( 'em' => array() ) ); ?></h2>
			<?php if ( $body ) : ?><p class="nt-videofeat__body"><?php echo esc_html( $body ); ?></p><?php endif; ?>

			<?php if ( ! empty( $points ) ) : ?>
				<ul class="nt-videofeat__points">
					<?php foreach ( $points as $point ) : ?>
						<li><?php echo esc_html( (string) $point ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( $button && $video ) : ?>
				<a class="btn nt-videofeat__btn" href="<?php echo esc_url( $video ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo esc_html( $button ); ?>
				</a>
			<?php endif; ?>
		</div>

	</div>
</section>
