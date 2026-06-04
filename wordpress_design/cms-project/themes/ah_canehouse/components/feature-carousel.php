<?php
/**
 * Component: Feature Carousel  (reusable)
 * ---------------------------------------------------------------------------
 * A horizontal, swipeable carousel of "feature cards". Each card picks its own
 * VARIANT so a single row can mix designs yet stay cohesive.
 *
 * Reuse it anywhere - pass args via get_template_part()'s 3rd parameter:
 *
 *   get_template_part( 'components/feature-carousel', null, array(
 *       'tag'      => 'Why Choose Us',
 *       'title'    => 'A Few <span class="accent">Good Reasons</span>',
 *       'subtitle' => 'Optional intro line.',
 *       'cards'    => array(
 *           array( 'variant' => 'gradient', 'icon' => '🌍', 'title' => '…', 'text' => '…' ),
 *           array( 'variant' => 'image',    'image' => 'https://…', 'title' => '…', 'text' => '…',
 *                  'link_text' => 'Read more', 'link_url' => '/about' ),
 *           array( 'variant' => 'stat',  'stat' => '100%', 'stat_label' => 'Natural', 'title' => '…', 'text' => '…' ),
 *           array( 'variant' => 'quote', 'text' => 'A short quote…', 'cite' => '- Founder' ),
 *           array( 'variant' => 'icon',  'icon' => '🧪', 'title' => '…', 'text' => '…' ),
 *       ),
 *   ) );
 *
 * Variants:  gradient | image | stat | quote | icon (default)
 * To add a card tomorrow: append ONE array entry - nothing else to touch.
 * ---------------------------------------------------------------------------
 */
defined( 'ABSPATH' ) || exit;

$tag      = $args['tag']      ?? 'The Cane House';
$title    = $args['title']    ?? 'Everything You Need to <span class="accent">Know</span>';
$subtitle = $args['subtitle'] ?? '';

/* Default demo content - the six requested cards, each in its own variant. */
$cards = $args['cards'] ?? array(
	array(
		'variant' => 'gradient',
		'icon'    => '🌍',
		'title'   => 'Why It\'s Loved Worldwide',
		'text'    => 'From India to Brazil, fresh sugarcane juice has been a beloved street-side ritual for over 2,000 years - now pressed live in the UK.',
	),
	array(
		'variant'   => 'image',
		'image'     => 'https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&q=80&w=800',
		'title'     => 'Beyond the Juice',
		'text'      => 'A living tradition rooted in Ayurvedic wellness, spiritual offerings and everyday refreshment.',
		'link_text' => 'Our story',
		'link_url'  => '#story',
	),
	array(
		'variant' => 'quote',
		'text'    => 'Sugarcane - one of nature\'s most generous gifts. Pure energy, pressed fresh.',
		'cite'    => '- The Cane House',
	),
	array(
		'variant'    => 'stat',
		'stat'       => '6+',
		'stat_label' => 'Benefits',
		'title'      => 'Benefits',
		'text'       => 'Natural energy, hydration, antioxidants, digestion support and more - no additives, no crash.',
		'link_text'  => 'See benefits',
		'link_url'   => '#benefits',
	),
	array(
		'variant'   => 'image',
		'image'     => 'https://images.unsplash.com/photo-1599940824399-b87987ceb72a?auto=format&fit=crop&q=80&w=800',
		'title'     => 'The Journey',
		'text'      => 'Fresh stalks sourced from South Asia, pressed live on our machine, served chilled in front of you.',
		'link_text' => 'How it works',
		'link_url'  => '#how-to-order',
	),
	array(
		'variant' => 'icon',
		'icon'    => '🧪',
		'title'   => 'What\'s Inside Every Sip',
		'text'    => 'Calcium, potassium, magnesium, iron and natural electrolytes - 100% plant-based and vegan.',
	),
);

$allowed = array( 'span' => array( 'class' => array(), 'style' => array() ), 'em' => array(), 'strong' => array(), 'br' => array() );

/* Unique id so several carousels can live on one page. */
$uid = 'ch-fc-' . wp_unique_id();

/**
 * Render a single card by variant. Kept local + guarded so the component can be
 * included multiple times without redeclaring the function.
 */
if ( ! function_exists( 'ch_fc_render_card' ) ) {
	function ch_fc_render_card( array $c ): void {
		$variant = $c['variant'] ?? 'icon';
		$title   = $c['title']   ?? '';
		$text    = $c['text']    ?? '';
		$icon    = $c['icon']    ?? '🌿';
		$link_t  = $c['link_text'] ?? '';
		$link_u  = $c['link_url']  ?? '';

		$link_html = '';
		if ( $link_t && $link_u ) {
			$link_html = '<a class="ch-fc-link" href="' . esc_url( $link_u ) . '">'
				. esc_html( $link_t ) . ' <span aria-hidden="true">→</span></a>';
		}
		?>
		<article class="ch-fc-card ch-fc-card--<?php echo esc_attr( $variant ); ?>">
			<?php if ( $variant === 'image' ) : ?>
				<div class="ch-fc-card__media">
					<?php if ( ! empty( $c['image'] ) ) : ?>
						<img src="<?php echo esc_url( $c['image'] ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy">
					<?php endif; ?>
				</div>
				<div class="ch-fc-card__body">
					<h3 class="ch-fc-card__title"><?php echo esc_html( $title ); ?></h3>
					<p class="ch-fc-card__text"><?php echo esc_html( $text ); ?></p>
					<?php echo $link_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>

			<?php elseif ( $variant === 'stat' ) : ?>
				<div class="ch-fc-card__body">
					<div class="ch-fc-card__stat"><?php echo esc_html( $c['stat'] ?? '' ); ?></div>
					<div class="ch-fc-card__stat-label"><?php echo esc_html( $c['stat_label'] ?? '' ); ?></div>
					<p class="ch-fc-card__text"><?php echo esc_html( $text ); ?></p>
					<?php echo $link_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>

			<?php elseif ( $variant === 'quote' ) : ?>
				<div class="ch-fc-card__body">
					<span class="ch-fc-card__quote-mark" aria-hidden="true">&ldquo;</span>
					<p class="ch-fc-card__quote"><?php echo esc_html( $text ); ?></p>
					<?php if ( ! empty( $c['cite'] ) ) : ?>
						<cite class="ch-fc-card__cite"><?php echo esc_html( $c['cite'] ); ?></cite>
					<?php endif; ?>
				</div>

			<?php elseif ( $variant === 'gradient' ) : ?>
				<div class="ch-fc-card__body">
					<span class="ch-fc-card__icon ch-fc-card__icon--lg"><?php echo esc_html( $icon ); ?></span>
					<h3 class="ch-fc-card__title"><?php echo esc_html( $title ); ?></h3>
					<p class="ch-fc-card__text"><?php echo esc_html( $text ); ?></p>
					<?php echo $link_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>

			<?php else : /* icon (default) */ ?>
				<div class="ch-fc-card__body">
					<span class="ch-fc-card__icon"><?php echo esc_html( $icon ); ?></span>
					<h3 class="ch-fc-card__title"><?php echo esc_html( $title ); ?></h3>
					<p class="ch-fc-card__text"><?php echo esc_html( $text ); ?></p>
					<?php echo $link_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>
		</article>
		<?php
	}
}
?>

<section class="ch-fc-section">
	<div class="container">
		<div class="ch-fc-head fade-up">
			<div>
				<?php if ( $tag ) : ?><div class="section-tag"><?php echo esc_html( $tag ); ?></div><?php endif; ?>
				<h2 class="section-title"><?php echo wp_kses( $title, $allowed ); ?></h2>
				<?php if ( $subtitle ) : ?><p class="ch-fc-subtitle"><?php echo wp_kses( $subtitle, $allowed ); ?></p><?php endif; ?>
			</div>
			<div class="ch-fc-arrows">
				<button type="button" class="ch-fc-arrow" data-fc-prev aria-label="Previous">‹</button>
				<button type="button" class="ch-fc-arrow" data-fc-next aria-label="Next">›</button>
			</div>
		</div>
	</div>

	<div class="ch-fc" id="<?php echo esc_attr( $uid ); ?>">
		<div class="ch-fc-track" data-fc-track>
			<?php foreach ( $cards as $card ) : ch_fc_render_card( (array) $card ); endforeach; ?>
		</div>
		<div class="ch-fc-dots" data-fc-dots aria-hidden="true">
			<?php foreach ( $cards as $di => $card ) : ?>
				<button type="button" class="ch-fc-dot<?php echo $di === 0 ? ' active' : ''; ?>" data-fc-go="<?php echo (int) $di; ?>" tabindex="-1"></button>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<script>
(function () {
	'use strict';
	var root = document.getElementById('<?php echo esc_js( $uid ); ?>');
	if (!root || root.dataset.fcInit) return;
	root.dataset.fcInit = '1';

	var track = root.querySelector('[data-fc-track]');
	var dots  = Array.prototype.slice.call(root.querySelectorAll('[data-fc-go]'));
	var prev  = root.parentNode.querySelector('[data-fc-prev]') || document.querySelector('[data-fc-prev]');
	var next  = root.parentNode.querySelector('[data-fc-next]');
	// arrows live in the section header (outside .ch-fc), grab them from the section
	var section = root.closest('.ch-fc-section');
	if (section) {
		prev = section.querySelector('[data-fc-prev]');
		next = section.querySelector('[data-fc-next]');
	}
	if (!track) return;

	function cardStep() {
		var card = track.querySelector('.ch-fc-card');
		if (!card) return 320;
		var gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap || '0') || 0;
		return card.getBoundingClientRect().width + gap;
	}

	function updateUI() {
		var max = track.scrollWidth - track.clientWidth - 1;
		if (prev) prev.disabled = track.scrollLeft <= 1;
		if (next) next.disabled = track.scrollLeft >= max;
		// nearest card → active dot
		var idx = Math.round(track.scrollLeft / cardStep());
		idx = Math.max(0, Math.min(dots.length - 1, idx));
		dots.forEach(function (d, i) { d.classList.toggle('active', i === idx); });
	}

	function scrollByCards(dir) { track.scrollBy({ left: dir * cardStep(), behavior: 'smooth' }); }

	if (prev) prev.addEventListener('click', function () { scrollByCards(-1); });
	if (next) next.addEventListener('click', function () { scrollByCards(1); });
	dots.forEach(function (d, i) {
		d.addEventListener('click', function () { track.scrollTo({ left: i * cardStep(), behavior: 'smooth' }); });
	});

	var raf;
	track.addEventListener('scroll', function () {
		if (raf) cancelAnimationFrame(raf);
		raf = requestAnimationFrame(updateUI);
	}, { passive: true });
	window.addEventListener('resize', updateUI);

	// ── Pointer drag (desktop click-drag; touch uses native scroll) ──────────
	var down = false, startX = 0, startScroll = 0, moved = false;
	track.addEventListener('pointerdown', function (e) {
		if (e.pointerType === 'touch') return;       // let touch scroll natively
		down = true; moved = false;
		startX = e.clientX; startScroll = track.scrollLeft;
		track.classList.add('is-dragging');
	});
	window.addEventListener('pointermove', function (e) {
		if (!down) return;
		var dx = e.clientX - startX;
		if (Math.abs(dx) > 4) moved = true;
		track.scrollLeft = startScroll - dx;
	});
	window.addEventListener('pointerup', function () {
		down = false; track.classList.remove('is-dragging');
	});
	// swallow click after a drag so links don't fire mid-drag
	track.addEventListener('click', function (e) {
		if (moved) { e.preventDefault(); e.stopPropagation(); moved = false; }
	}, true);

	updateUI();
})();
</script>
