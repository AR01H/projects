<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;

$slides = isset( $slides ) && is_array( $slides ) ? $slides : array();

$slides = array_values(
	array_filter(
		array_map(
			static function ( $slide ) {
				$slide = (array) $slide;
				return array(
					'title'       => trim( (string) ( $slide['title'] ?? '' ) ),
					'subtitle'    => (string) ( $slide['subtitle'] ?? '' ),
					'description' => (string) ( $slide['description'] ?? '' ),
					'image'       => (string) ( $slide['image'] ?? '' ),
					'video'       => (string) ( $slide['video'] ?? '' ),
					'buttons'     => is_array( $slide['buttons'] ?? null ) ? $slide['buttons'] : array(),
				);
			},
			$slides
		),
		static function ( $slide ) {
			return '' !== $slide['title'];
		}
	)
);

if ( empty( $slides ) ) {
	return;
}

$id       = ( isset( $id ) && '' !== trim( (string) $id ) ) ? sanitize_html_class( (string) $id ) : 'hero';
$count    = count( $slides );
$autoplay = ! empty( $autoplay );
$class    = 'hero-carousel' . ( $autoplay ? ' hero-carousel--autoplay' : '' );
?>
<div class="<?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $id ); ?>"<?php echo ( $count > 1 ) ? ' data-vs-hero-carousel' : ''; ?><?php echo ( $count > 1 && $autoplay ) ? ' data-hero-autoplay="1"' : ''; ?>>
	<div class="hero-carousel__track">
		<?php foreach ( $slides as $i => $slide ) : ?>
			<div class="hero-carousel__slide<?php echo ( 0 === $i ) ? ' is-active' : ''; ?>"<?php echo ( 0 !== $i ) ? ' inert' : ''; ?>>
				<?php if ( '' !== $slide['video'] ) : ?>
					<div class="hero-carousel__media">
						<video
							src="<?php echo esc_url( $slide['video'] ); ?>"
							<?php echo ( '' !== $slide['image'] ) ? 'poster="' . esc_url( $slide['image'] ) . '"' : ''; ?>
							muted
							playsinline
							preload="<?php echo ( 0 === $i ) ? 'auto' : 'metadata'; ?>"
						></video>
					</div>
				<?php elseif ( '' !== $slide['image'] ) : ?>
					<div class="hero-carousel__media">
						<img src="<?php echo esc_url( $slide['image'] ); ?>" alt="" loading="<?php echo ( 0 === $i ) ? 'eager' : 'lazy'; ?>">
					</div>
				<?php endif; ?>
				<div class="hero-carousel__content">
					<?php if ( '' !== $slide['subtitle'] ) : ?>
						<p class="hero-carousel__subtitle"><?php echo esc_html( $slide['subtitle'] ); ?></p>
					<?php endif; ?>
					<h2 class="hero-carousel__title"><?php echo esc_html( $slide['title'] ); ?></h2>
					<?php if ( '' !== $slide['description'] ) : ?>
						<p class="hero-carousel__desc"><?php echo esc_html( $slide['description'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $slide['buttons'] ) ) : ?>
						<div class="hero-carousel__actions">
							<?php foreach ( $slide['buttons'] as $btn ) :
								$btn      = (array) $btn;
								$label    = trim( (string) ( $btn['label'] ?? '' ) );
								$route    = (string) ( $btn['route'] ?? '' );
								if ( '' === $label || '' === $route ) {
									continue;
								}
								$is_ghost = 'ghost' === ( $btn['style'] ?? '' );
							?>
								<a class="btn<?php echo $is_ghost ? ' btn--outline' : ''; ?>" href="<?php echo esc_url( RouteService::url( $route ) ); ?>"><?php echo esc_html( $label ); ?></a>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( $count > 1 ) : ?>
		<button type="button" class="carousel-arrow hero-carousel__arrow hero-carousel__arrow--prev" aria-label="<?php esc_attr_e( 'Previous slide', 'vintagesoul' ); ?>">
			<span aria-hidden="true">&larr;</span>
		</button>
		<button type="button" class="carousel-arrow hero-carousel__arrow hero-carousel__arrow--next" aria-label="<?php esc_attr_e( 'Next slide', 'vintagesoul' ); ?>">
			<span aria-hidden="true">&rarr;</span>
		</button>
		<?php if ( $autoplay ) : ?>
			<?php

			?>
			<button
				type="button"
				class="carousel-toggle hero-carousel__toggle"
				data-hero-toggle
				data-label-pause="<?php echo esc_attr__( 'Pause slideshow', 'vintagesoul' ); ?>"
				data-label-play="<?php echo esc_attr__( 'Play slideshow', 'vintagesoul' ); ?>"
				aria-label="<?php esc_attr_e( 'Pause slideshow', 'vintagesoul' ); ?>"
			>
				<span class="carousel-toggle__icon" aria-hidden="true"></span>
			</button>
		<?php endif; ?>
		<div class="carousel-dots hero-carousel__dots" role="tablist">
			<?php for ( $i = 0; $i < $count; $i++ ) : ?>
				<button type="button" class="carousel-dot hero-carousel__dot<?php echo ( 0 === $i ) ? ' is-active' : ''; ?>" data-hero-slide="<?php echo esc_attr( $i ); ?>" role="tab" aria-selected="<?php echo ( 0 === $i ) ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( sprintf(  __( 'Slide %d', 'vintagesoul' ), $i + 1 ) ); ?>"></button>
			<?php endfor; ?>
		</div>
	<?php endif; ?>
</div>
