<?php
/**
 * Carousel: Side Arrows Navigation
 * Left/right arrow buttons for navigation, minimal and clean.
 * Best for: showcase/hero sections, featured items, media gallery
 *
 * Usage:
 *  get_template_part( 'components/carousels/carousel-arrows', null, [
 *      'items'  => [ ... card data ... ],
 *      'type'   => 'image',
 *      'visible' => 3,  // cards visible per slide (default: 3)
 *  ] );
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require_once get_template_directory() . '/components/carousels/_card-renderer.php';

$items   = $args['items']   ?? [];
$type    = $args['type']    ?? 'feature';
$visible = (int) ( $args['visible'] ?? 3 );

if ( empty( $items ) ) return;

static $carousel_id = 0;
$carousel_id++;
$id = 'cc-arrows-' . $carousel_id;
?>

<div class="cc-carousel cc-carousel--arrows" id="<?php echo esc_attr( $id ); ?>"
	 style="--cc-visible: <?php echo esc_attr( $visible ); ?>; --cc-gap: 20px;">

	<button class="cc-arrow cc-arrow--prev" aria-label="Previous slides">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
			<polyline points="15 18 9 12 15 6"></polyline>
		</svg>
	</button>

	<div class="cc-carousel__viewport">
		<div class="cc-carousel__track">
			<?php foreach ( $items as $item ) : ?>
				<div class="cc-carousel__item">
					<?php echo cc_render_card( (array) $item, $type ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<button class="cc-arrow cc-arrow--next" aria-label="Next slides">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
			<polyline points="9 6 15 12 9 18"></polyline>
		</svg>
	</button>
</div>

<style>
#<?php echo esc_attr( $id ); ?> {
	display: flex;
	align-items: center;
	gap: 16px;
	position: relative;
}

.cc-carousel--arrows .cc-carousel__viewport {
	flex: 1;
	overflow: hidden;
}

.cc-carousel--arrows .cc-carousel__track {
	display: grid;
	grid-auto-flow: column;
	grid-auto-columns: calc((100% - (var(--cc-visible) - 1) * var(--cc-gap)) / var(--cc-visible));
	gap: var(--cc-gap);
	overflow-x: auto;
	scroll-behavior: smooth;
	-webkit-overflow-scrolling: touch;
	scroll-snap-type: x mandatory;
}

.cc-carousel--arrows .cc-carousel__item {
	scroll-snap-align: start;
}

.cc-arrow {
	flex-shrink: 0;
	width: 44px;
	height: 44px;
	border-radius: 50%;
	border: none;
	background: var(--client-color-11);
	color: var(--client-color-1);
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	transition: all 0.3s ease;
	padding: 0;
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.cc-arrow svg {
	width: 18px;
	height: 18px;
}

.cc-arrow:hover {
	background: var(--client-color-7);
	color: #fff;
	box-shadow: 0 6px 16px rgba(0,0,0,0.15);
	transform: scale(1.08);
}

.cc-arrow:disabled {
	opacity: 0.4;
	cursor: not-allowed;
	transform: none;
	pointer-events: none;
}

@media (max-width: 900px) {
	.cc-arrow { width: 38px; height: 38px; }
	.cc-arrow svg { width: 16px; height: 16px; }
}

@media (max-width: 640px) {
	#<?php echo esc_attr( $id ); ?> { gap: 10px; }
	.cc-arrow { width: 36px; height: 36px; border-width: 1.5px; }
	.cc-arrow svg { width: 14px; height: 14px; }
}
</style>

<script>
(function() {
	const carousel = document.getElementById('<?php echo esc_js( $id ); ?>');
	if (!carousel) return;

	const track = carousel.querySelector('.cc-carousel__track');
	const prevBtn = carousel.querySelector('.cc-arrow--prev');
	const nextBtn = carousel.querySelector('.cc-arrow--next');

	const cardStep = () => {
		const item = track.querySelector('.cc-carousel__item');
		return item ? item.offsetWidth + 20 : 0;
	};

	const updateButtons = () => {
		const maxScroll = track.scrollWidth - track.clientWidth;
		prevBtn.disabled = track.scrollLeft <= 1;
		nextBtn.disabled = track.scrollLeft >= maxScroll - 1;
	};

	prevBtn.addEventListener('click', () => {
		track.scrollBy({ left: -cardStep(), behavior: 'smooth' });
	});

	nextBtn.addEventListener('click', () => {
		track.scrollBy({ left: cardStep(), behavior: 'smooth' });
	});

	track.addEventListener('scroll', updateButtons, { passive: true });
	window.addEventListener('resize', updateButtons);

	updateButtons();
})();
</script>
<?php
