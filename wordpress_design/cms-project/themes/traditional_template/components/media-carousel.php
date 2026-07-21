<?php
/**
 * Home media banner - a fading carousel that supports BOTH images and videos.
 *
 * Every slide comes from admin/data/home_media.json. A slide with
 * "type":"video" renders an autoplaying muted looped <video> (with a poster
 * fallback); "type":"image" renders an <img>. Auto-rotation, dots and arrows
 * are driven by the vanilla controller in assets/js/common.js (initMediaCarousels).
 * Nothing here is hardcoded content.
 */
defined( 'ABSPATH' ) || exit;

$data   = nt_data( 'home_media' );
$slides = $data['slides'] ?? array();
if ( empty( $slides ) ) {
	return;
}

$autoplay = ! empty( $data['autoplay'] );
$interval = (int) ( $data['interval'] ?? 6000 );
$tag      = $data['tag'] ?? '';
?>
<section class="nt-media-carousel"
         id="nt-home-media"
         data-autoplay="<?php echo $autoplay ? '1' : '0'; ?>"
         data-interval="<?php echo esc_attr( $interval ); ?>"
         aria-label="<?php esc_attr_e( 'Featured highlights', NT_TEXT_DOMAIN ); ?>">

	<div class="nt-media-carousel__track">
		<?php foreach ( $slides as $i => $slide ) :
			$slide   = (array) $slide;
			$type    = $slide['type'] ?? 'image';
			$src     = $slide['src'] ?? '';
			$heading = $slide['heading'] ?? '';
			$text    = $slide['text'] ?? '';
			$cta     = (array) ( $slide['cta'] ?? array() );
			if ( '' === $src ) {
				continue;
			}
		?>
			<div class="nt-media-slide<?php echo 0 === $i ? ' is-active' : ''; ?>" data-index="<?php echo esc_attr( $i ); ?>">
				<?php if ( 'video' === $type ) : ?>
					<video class="nt-media-slide__media" playsinline muted loop preload="metadata"
						<?php if ( ! empty( $slide['poster'] ) ) : ?>poster="<?php echo esc_url( $slide['poster'] ); ?>"<?php endif; ?>>
						<source src="<?php echo esc_url( $src ); ?>" type="video/mp4">
					</video>
				<?php else : ?>
					<img class="nt-media-slide__media" src="<?php echo esc_url( $src ); ?>"
					     alt="<?php echo esc_attr( $slide['alt'] ?? $heading ); ?>"
					     loading="<?php echo 0 === $i ? 'eager' : 'lazy'; ?>">
				<?php endif; ?>

				<div class="nt-media-slide__overlay"></div>

				<?php if ( $heading || $text || ! empty( $cta['label'] ) ) : ?>
					<div class="nt-media-slide__content container">
						<?php if ( $tag && 0 === $i ) : ?><span class="nt-media-slide__tag"><?php echo esc_html( $tag ); ?></span><?php endif; ?>
						<?php if ( $heading ) : ?><h2 class="nt-media-slide__heading"><?php echo esc_html( $heading ); ?></h2><?php endif; ?>
						<?php if ( $text ) : ?><p class="nt-media-slide__text"><?php echo esc_html( $text ); ?></p><?php endif; ?>
						<?php if ( ! empty( $cta['label'] ) ) : ?>
							<a class="btn" href="<?php echo esc_url( nt_link( $cta['url'] ?? '#' ) ); ?>"><?php echo esc_html( $cta['label'] ); ?> &rarr;</a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( count( $slides ) > 1 ) : ?>
		<button type="button" class="nt-media-carousel__arrow nt-media-carousel__arrow--prev" data-nt-media-prev aria-label="<?php esc_attr_e( 'Previous slide', NT_TEXT_DOMAIN ); ?>">&#8592;</button>
		<button type="button" class="nt-media-carousel__arrow nt-media-carousel__arrow--next" data-nt-media-next aria-label="<?php esc_attr_e( 'Next slide', NT_TEXT_DOMAIN ); ?>">&#8594;</button>
		<div class="nt-media-carousel__dots" role="tablist">
			<?php foreach ( $slides as $i => $_ ) : ?>
				<button type="button" class="nt-media-carousel__dot<?php echo 0 === $i ? ' is-active' : ''; ?>" data-nt-media-dot="<?php echo esc_attr( $i ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Slide %d', NT_TEXT_DOMAIN ), $i + 1 ) ); ?>"></button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
