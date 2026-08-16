<?php

defined( 'ABSPATH' ) || exit;

$name      = isset( $name ) ? trim( (string) $name ) : '';
$role      = isset( $role ) ? (string) $role : '';
$title     = isset( $title ) ? (string) $title : '';
$desc      = isset( $desc ) ? (string) $desc : '';
$thumbnail = isset( $thumbnail ) ? (string) $thumbnail : '';
$avatar    = isset( $avatar ) ? (string) $avatar : '';
$video_url = isset( $video_url ) ? (string) $video_url : '';

if ( '' === $name || '' === $thumbnail ) {
	return;
}
?>
<figure class="video-testimonial-card">
	<button type="button" class="video-testimonial-card__media hover-zoom" aria-label="<?php echo esc_attr( sprintf(  __( 'Play video from %s', 'vintagesoul' ), $name ) ); ?>"<?php echo '' !== $video_url ? ' data-video-url="' . esc_attr( $video_url ) . '"' : ''; ?>>
		<img class="video-testimonial-card__thumb" src="<?php echo esc_url( $thumbnail ); ?>" alt="" loading="lazy">
		<span class="video-testimonial-card__overlay">
			<?php if ( '' !== $avatar ) : ?>
				<img class="video-testimonial-card__avatar" src="<?php echo esc_url( $avatar ); ?>" alt="" loading="lazy">
			<?php else : ?>
				<span class="video-testimonial-card__avatar video-testimonial-card__avatar--fallback" aria-hidden="true"><?php echo esc_html( mb_substr( $name, 0, 1 ) ); ?></span>
			<?php endif; ?>
			<span class="video-testimonial-card__info">
				<span class="video-testimonial-card__name"><?php echo esc_html( $name ); ?></span>
				<?php if ( '' !== $role ) : ?>
					<span class="video-testimonial-card__role"><?php echo esc_html( $role ); ?></span>
				<?php endif; ?>
			</span>
		</span>
		<span class="video-testimonial-card__play-icon" aria-hidden="true"></span>
	</button>
	<?php if ( '' !== $title || '' !== $desc ) : ?>
		<figcaption class="video-testimonial-card__body">
			<?php if ( '' !== $title ) : ?>
				<p class="video-testimonial-card__title"><?php echo esc_html( $title ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== $desc ) : ?>
				<p class="video-testimonial-card__desc"><?php echo esc_html( $desc ); ?></p>
			<?php endif; ?>
		</figcaption>
	<?php endif; ?>
</figure>
