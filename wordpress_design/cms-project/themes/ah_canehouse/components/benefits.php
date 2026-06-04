<?php
defined( 'ABSPATH' ) || exit;
$benefits = ch_get_benefits();
?>

<section id="benefits" class="ch-benefits-section">
	<div class="ch-benefits-inner">
		<div class="fade-left">
			<div class="ch-section-tag">Good for You</div>
			<h2 class="ch-section-title">Why Sugarcane Juice is <span class="accent" style="color:var(--client-color-7);">Loved Worldwide</span></h2>
			<p class="ch-section-body">Fresh sugarcane juice is not just delicious - it's packed with natural benefits rooted in 2,000 years of Ayurvedic and South Asian wellness tradition.</p>
			<div class="ch-benefits-list" data-benefits-track>
				<?php foreach ( $benefits as $b ) :
					$b = (array) $b;
				?>
					<div class="ch-benefit-item">
						<div class="ch-benefit-icon" aria-hidden="true"><?php echo esc_html( $b['icon'] ?? '🌿' ); ?></div>
						<div>
							<div class="ch-b-title"><?php echo esc_html( $b['title'] ?? '' ); ?></div>
							<div class="ch-b-desc"><?php echo esc_html( $b['desc'] ?? '' ); ?></div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Mobile carousel controls (hidden on desktop) -->
			<?php if ( count( $benefits ) > 1 ) : ?>
				<div class="ch-benefits-nav" aria-hidden="true">
					<button type="button" class="ch-benefits-arrow" data-benefits-prev aria-label="Previous benefit">‹</button>
					<div class="ch-benefits-dots" data-benefits-dots>
						<?php foreach ( $benefits as $bi => $b ) : ?>
							<button type="button" class="ch-benefits-dot<?php echo $bi === 0 ? ' active' : ''; ?>" data-go="<?php echo (int) $bi; ?>"></button>
						<?php endforeach; ?>
					</div>
					<button type="button" class="ch-benefits-arrow" data-benefits-next aria-label="Next benefit">›</button>
				</div>
			<?php endif; ?>
		</div>

		<div class="ch-benefits-visual fade-right" aria-hidden="true">
			<div class="ch-promise-card">
				<span class="ch-promise-icon">🌱</span>
				<div class="ch-promise-title">Our Promise</div>
				<div class="ch-promise-sub">Pressed Fresh. Served Cool.</div>
				<div class="ch-promise-tags">
					<div class="ch-promise-tag">No added sugar</div>
					<div class="ch-promise-tag">No preservatives</div>
					<div class="ch-promise-tag">Pure, natural refreshment</div>
					<div class="ch-promise-tag">Pressed live at every order</div>
					<div class="ch-promise-tag">Served chilled, always fresh</div>
					<div class="ch-promise-tag">Rooted in Ayurvedic tradition</div>
				</div>
				<p class="ch-promise-foot">Sugarcane has been cherished for over 2,000 years across the Indian subcontinent. Even the leftover fibre (bagasse) is biodegradable - a truly sustainable crop.</p>
			</div>
		</div>
	</div>
</section>

<script>
(function () {
	'use strict';
	var section = document.getElementById('benefits');
	if (!section || section.dataset.bcInit) return;
	var track = section.querySelector('[data-benefits-track]');
	if (!track) return;
	section.dataset.bcInit = '1';

	var items = Array.prototype.slice.call(track.querySelectorAll('.ch-benefit-item'));
	var dots  = Array.prototype.slice.call(section.querySelectorAll('[data-benefits-dots] [data-go]'));
	var prev  = section.querySelector('[data-benefits-prev]');
	var next  = section.querySelector('[data-benefits-next]');
	var mq    = window.matchMedia('(max-width: 960px)');
	if (items.length < 2) return;

	var cur = 0, timer = null, AUTO = 4000;

	function setActive(i) {
		cur = (i + items.length) % items.length;
		dots.forEach(function (d, k) { d.classList.toggle('active', k === cur); });
	}
	function goTo(i, smooth) {
		setActive(i);
		var left = items[cur].offsetLeft - items[0].offsetLeft;
		track.scrollTo({ left: left, behavior: smooth === false ? 'auto' : 'smooth' });
	}
	function start() { stop(); if (mq.matches) timer = setInterval(function () { goTo(cur + 1); }, AUTO); }
	function stop()  { if (timer) { clearInterval(timer); timer = null; } }

	// Controls
	if (prev) prev.addEventListener('click', function () { goTo(cur - 1); start(); });
	if (next) next.addEventListener('click', function () { goTo(cur + 1); start(); });
	dots.forEach(function (d, i) { d.addEventListener('click', function () { goTo(i); start(); }); });

	// Keep the active dot in sync when the user swipes manually
	var raf;
	track.addEventListener('scroll', function () {
		if (raf) cancelAnimationFrame(raf);
		raf = requestAnimationFrame(function () {
			var step = items[1].offsetLeft - items[0].offsetLeft || track.clientWidth;
			setActive(Math.round(track.scrollLeft / step));
		});
	}, { passive: true });

	// Pause autoplay while interacting, resume after
	['pointerdown', 'touchstart', 'mouseenter'].forEach(function (ev) {
		track.addEventListener(ev, stop, { passive: true });
	});
	['pointerup', 'touchend', 'mouseleave'].forEach(function (ev) {
		track.addEventListener(ev, start, { passive: true });
	});

	// Only autoplay while the mobile carousel is active; reset on breakpoint change
	function onMQ() { goTo(0, false); start(); }
	if (mq.addEventListener) mq.addEventListener('change', onMQ); else mq.addListener(onMQ);

	start();
})();
</script>
