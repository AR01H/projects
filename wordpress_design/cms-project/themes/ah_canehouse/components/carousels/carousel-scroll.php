<?php
/**
 * Carousel: Smooth Auto-Scroll
 * Continuous scrolling with play/pause toggle control.
 * Best for: news feeds, testimonials, featured content with auto-rotation
 *
 * Usage:
 *  get_template_part( 'components/carousels/carousel-scroll', null, [
 *      'items'    => [ ... card data ... ],
 *      'type'     => 'feature',
 *      'autoplay' => true,   // auto-rotate on load (default: true)
 *      'speed'    => 4500,   // ms between slides (default: 4500)
 *  ] );
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require_once get_template_directory() . '/components/carousels/_card-renderer.php';

$items    = $args['items']    ?? [];
$type     = $args['type']     ?? 'feature';
$autoplay = ! empty( $args['autoplay'] );
$speed    = (int) ( $args['speed'] ?? 4500 );

if ( empty( $items ) ) return;

static $carousel_id = 0;
$carousel_id++;
$id = 'cc-scroll-' . $carousel_id;
?>

<div class="cc-carousel cc-carousel--scroll" id="<?php echo esc_attr( $id ); ?>"
	 data-autoplay="<?php echo $autoplay ? 1 : 0; ?>"
	 data-speed="<?php echo esc_attr( $speed ); ?>">

	<div class="cc-carousel__viewport">
		<div class="cc-carousel__track">
			<?php foreach ( $items as $item ) : ?>
				<div class="cc-carousel__item">
					<?php echo cc_render_card( (array) $item, $type ); ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>

	<?php if ( count( $items ) > 1 ) : ?>
		<div class="cc-carousel__controls">
			<button class="cc-control-btn cc-control-play" aria-label="Play carousel">
				<svg viewBox="0 0 24 24" fill="currentColor">
					<polygon points="5 3 19 12 5 21"></polygon>
				</svg>
			</button>
			<button class="cc-control-btn cc-control-pause is-hidden" aria-label="Pause carousel">
				<svg viewBox="0 0 24 24" fill="currentColor">
					<rect x="6" y="4" width="4" height="16"></rect>
					<rect x="14" y="4" width="4" height="16"></rect>
				</svg>
			</button>
		</div>
	<?php endif; ?>
</div>

<style>
#<?php echo esc_attr( $id ); ?> {
	position: relative;
	--cc-gap: 20px;
}

.cc-carousel--scroll .cc-carousel__viewport {
	overflow: hidden;
	border-radius: var(--ch-radius, 12px);
}

.cc-carousel--scroll .cc-carousel__track {
	display: grid;
	grid-auto-flow: column;
	grid-auto-columns: 100%;
	gap: var(--cc-gap);
	overflow-x: auto;
	scroll-behavior: smooth;
	scroll-snap-type: x mandatory;
	-webkit-overflow-scrolling: touch;
}

.cc-carousel--scroll .cc-carousel__item {
	scroll-snap-align: start;
	scroll-snap-stop: always;
}

.cc-carousel__controls {
	display: flex;
	justify-content: center;
	gap: 12px;
	margin-top: 16px;
}

.cc-control-btn {
	width: 40px;
	height: 40px;
	border-radius: 50%;
	border: none;
	background: var(--client-color-11);
	color: var(--client-color-1);
	cursor: pointer;
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 0;
	transition: all 0.3s ease;
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.cc-control-btn svg {
	width: 16px;
	height: 16px;
}

.cc-control-btn:hover {
	background: var(--client-color-7);
	color: #fff;
	box-shadow: 0 6px 16px rgba(0,0,0,0.15);
	transform: scale(1.1);
}

.cc-control-btn.is-hidden {
	display: none;
}

@media (max-width: 640px) {
	.cc-carousel__controls { margin-top: 12px; gap: 10px; }
	.cc-control-btn { width: 36px; height: 36px; border-width: 1.5px; }
	.cc-control-btn svg { width: 14px; height: 14px; }
}
</style>

<script>
(function() {
	const carousel = document.getElementById('<?php echo esc_js( $id ); ?>');
	if (!carousel) return;

	const track = carousel.querySelector('.cc-carousel__track');
	const playBtn = carousel.querySelector('.cc-control-play');
	const pauseBtn = carousel.querySelector('.cc-control-pause');

	let currentIndex = 0;
	let autoplayTimer = null;
	let isPlaying = carousel.dataset.autoplay === '1';
	const speed = parseInt(carousel.dataset.speed, 10);
	const itemCount = track.querySelectorAll('.cc-carousel__item').length;

	const goToSlide = (index) => {
		currentIndex = index % itemCount;
		const item = track.querySelectorAll('.cc-carousel__item')[currentIndex];
		item?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
	};

	const nextSlide = () => goToSlide(currentIndex + 1);

	const startAutoplay = () => {
		if (autoplayTimer) clearInterval(autoplayTimer);
		autoplayTimer = setInterval(nextSlide, speed);
		isPlaying = true;
		playBtn?.classList.add('is-hidden');
		pauseBtn?.classList.remove('is-hidden');
	};

	const stopAutoplay = () => {
		if (autoplayTimer) clearInterval(autoplayTimer);
		autoplayTimer = null;
		isPlaying = false;
		playBtn?.classList.remove('is-hidden');
		pauseBtn?.classList.add('is-hidden');
	};

	playBtn?.addEventListener('click', startAutoplay);
	pauseBtn?.addEventListener('click', stopAutoplay);

	carousel.addEventListener('mouseenter', stopAutoplay);
	carousel.addEventListener('mouseleave', () => {
		if (carousel.dataset.autoplay === '1') startAutoplay();
	});

	if (isPlaying) startAutoplay();
})();
</script>
<?php
