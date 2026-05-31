<?php
defined( 'ABSPATH' ) || exit;

$title  = $args['title']  ?? '';
$images = $args['images'] ?? [];
$id     = $args['id']     ?? 'post-gal-' . uniqid();

if ( empty( $images ) ) return;
?>

<figure class="ch-post-gallery" id="<?php echo esc_attr( $id ); ?>">
	<?php if ( $title ) : ?>
		<figcaption class="ch-post-gallery__title"><?php echo esc_html( $title ); ?></figcaption>
	<?php endif; ?>

	<div class="ch-post-gallery__track">
		<?php foreach ( $images as $i => $img ) :
			$src   = is_array( $img ) ? $img['src'] : $img;
			$label = is_array( $img ) ? $img['label'] ?? '' : '';
			$desc  = is_array( $img ) ? $img['desc'] ?? '' : '';
		?>
			<div class="ch-post-gallery__slide<?php echo $i === 0 ? ' active' : ''; ?>">
				<img src="<?php echo esc_url( $src ); ?>"
					alt="<?php echo esc_attr( $label ?: 'Gallery image' ); ?>"
					loading="lazy"
					class="ch-post-gallery__img">
				<?php if ( $label || $desc ) : ?>
					<div class="ch-post-gallery__caption">
						<?php if ( $label ) : ?>
							<div class="ch-post-gallery__label"><?php echo esc_html( $label ); ?></div>
						<?php endif; ?>
						<?php if ( $desc ) : ?>
							<div class="ch-post-gallery__desc"><?php echo esc_html( $desc ); ?></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( count( $images ) > 1 ) : ?>
		<div class="ch-post-gallery__nav">
			<div class="ch-post-gallery__dots" data-target="<?php echo esc_attr( $id ); ?>">
				<?php foreach ( $images as $i => $_ ) : ?>
					<button class="ch-post-gallery__dot<?php echo $i === 0 ? ' active' : ''; ?>"
						data-index="<?php echo $i; ?>"
						aria-label="Image <?php echo $i + 1; ?>"
						type="button"></button>
				<?php endforeach; ?>
			</div>
			<div class="ch-post-gallery__arrows">
				<button class="ch-post-gallery__arrow ch-post-gallery__arrow--prev"
					data-target="<?php echo esc_attr( $id ); ?>"
					aria-label="Previous image" type="button">←</button>
				<button class="ch-post-gallery__arrow ch-post-gallery__arrow--next"
					data-target="<?php echo esc_attr( $id ); ?>"
					aria-label="Next image" type="button">→</button>
			</div>
		</div>
	<?php endif; ?>
</figure>
