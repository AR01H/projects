<?php
/**
 * Carousel: Dots Navigation
 * Smooth horizontal scroll with dot pagination indicators.
 * Best for: product cards, features, testimonials
 *
 * Usage:
 *  get_template_part( 'components/carousels/carousel-dots', null, [
 *      'items'  => [ ... card data ... ],
 *      'type'   => 'image',  // 'image' | 'feature' | 'step'
 *  ] );
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require_once get_template_directory() . '/components/carousels/_card-renderer.php';

$items = $args['items'] ?? [];
$type  = $args['type']  ?? 'feature';
$class = isset( $args['class'] ) ? ' ' . esc_attr( $args['class'] ) : '';

if ( empty( $items ) ) return;

static $carousel_id = 0;
$carousel_id++;
$id = 'cc-dots-' . $carousel_id;
?>

<div class="cc-carousel cc-carousel--dots" id="<?php echo esc_attr( $id ); ?>">
	<div class="cc-carousel__track" role="region" aria-label="Carousel">
		<?php foreach ( $items as $item ) : ?>
			<div class="cc-carousel__item">
				<?php echo cc_render_card( (array) $item, $type ); ?>
			</div>
		<?php endforeach; ?>
	</div>

	<?php if ( count( $items ) > 1 ) : ?>
		<div class="cc-carousel__nav">
			<div class="cc-carousel__dots">
				<?php foreach ( $items as $i => $_ ) : ?>
					<button class="cc-dot<?php echo $i === 0 ? ' is-active' : ''; ?>"
						aria-label="Slide <?php echo $i + 1; ?>"
						data-index="<?php echo $i; ?>"></button>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endif; ?>
</div>

<style>
#<?php echo esc_attr( $id ); ?> {
	--cc-gap: 24px;
	--cc-visible: 3;
}

.cc-carousel--dots .cc-carousel__track {
	display: grid;
	grid-auto-flow: column;
	grid-auto-columns: calc((100% - (var(--cc-visible) - 1) * var(--cc-gap)) / var(--cc-visible));
	gap: var(--cc-gap);
	overflow-x: auto;
	scroll-behavior: smooth;
	scroll-snap-type: x mandatory;
	-webkit-overflow-scrolling: touch;
}

.cc-carousel__item {
	scroll-snap-align: start;
}

.cc-carousel__nav {
	display: flex;
	justify-content: center;
	margin-top: 24px;
	gap: 10px;
	padding: 0 16px;
}

.cc-dot {
	width: 8px;
	height: 8px;
	border-radius: 50%;
	border: none;
	background: var(--client-color-4);
	cursor: pointer;
	padding: 0;
	transition: all 0.3s ease;
	opacity: 0.6;
}

.cc-dot:hover {
	opacity: 0.9;
	transform: scale(1.2);
}

.cc-dot.is-active {
	background: var(--client-color-7);
	width: 24px;
	border-radius: 4px;
	opacity: 1;
}

@media (max-width: 900px) {
	#<?php echo esc_attr( $id ); ?> { --cc-visible: 2; }
}

@media (max-width: 640px) {
	#<?php echo esc_attr( $id ); ?> { --cc-visible: 1; --cc-gap: 12px; }
	.cc-carousel__nav { margin-top: 16px; }
}
</style>

<script>
(function() {
	const carousel = document.getElementById('<?php echo esc_js( $id ); ?>');
	if (!carousel) return;

	const track = carousel.querySelector('.cc-carousel__track');
	const dots = carousel.querySelectorAll('.cc-dot');

	const updateDots = () => {
		const scrollPos = track.scrollLeft;
		const itemWidth = track.querySelector('.cc-carousel__item')?.offsetWidth || 0;
		const index = Math.round(scrollPos / (itemWidth + 16));
		dots.forEach((d, i) => d.classList.toggle('is-active', i === index));
	};

	dots.forEach((dot, i) => {
		dot.addEventListener('click', () => {
			const item = track.querySelectorAll('.cc-carousel__item')[i];
			item?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
		});
	});

	track.addEventListener('scroll', updateDots, { passive: true });
	updateDots();
})();
</script>
<?php
