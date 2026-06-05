<?php
/**
 * Component: Origins Showcase - Split Hero
 * Permanent split screen:
 *   LEFT  → UK hero, always visible & highlighted (the star)
 *   RIGHT → origin countries (India / Pakistan / Brazil) with a small tab
 *           selector; clicking a tab switches only the right side.
 * Args: tag, title, subtitle, uk_entry, origins[]
 */
defined( 'ABSPATH' ) || exit;

$_d       = CH_About_Data::origins_showcase_settings();
$tag      = $args['tag']      ?? $_d['section_heading']  ?? '';
$title    = $args['title']    ?? $_d['section_title']    ?? '';
$subtitle = $args['subtitle'] ?? $_d['section_subtitle'] ?? '';

/* ── UK (the hero, left - always highlighted) ───────────────────────── */
$uk = $args['uk_entry'] ?? $_d['uk'] ?? [];

/* ── Origin countries (right - small, switchable) ──────────────────── */
$origins = $args['origins'] ?? $_d['origins'] ?? [];

$allowed = [ 'span' => [ 'class' => [], 'style' => [] ], 'em' => [], 'strong' => [], 'br' => [] ];

if ( ! function_exists( 'ch_orig_embed_url' ) ) {
	function ch_orig_embed_url( $url ) {
		if ( empty( $url ) ) return '';
		if ( preg_match( '/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $m ) ) {
			return 'https://www.youtube.com/embed/' . $m[1] . '?autoplay=1&rel=0&modestbranding=1';
		}
		if ( preg_match( '/vimeo\.com\/(\d+)/', $url, $m ) ) {
			return 'https://player.vimeo.com/video/' . $m[1] . '?autoplay=1';
		}
		return $url;
	}
}

/* helper to render a media block (image slideshow + optional video) */
if ( ! function_exists( 'ch_orig_media_block' ) ) {
	function ch_orig_media_block( $images, $video_url, $flag ) {
		$images = array_values( array_filter( (array) $images ) );
		$videmb = ch_orig_embed_url( $video_url );
		?>
		<div class="ch-orig-media">
			<div class="ch-orig-slides" data-orig-slides>
				<?php if ( ! empty( $images ) ) : ?>
					<?php foreach ( $images as $gi => $src ) : ?>
						<img class="ch-orig-slide<?php echo $gi === 0 ? ' active' : ''; ?>"
							src="<?php echo esc_url( $src ); ?>"
							alt="sugarcane <?php echo $gi + 1; ?>" loading="lazy">
					<?php endforeach; ?>
					<?php if ( count( $images ) > 1 ) : ?>
						<div class="ch-orig-slide-dots">
							<?php foreach ( $images as $gi => $src ) : ?>
								<button type="button"
									class="ch-orig-slide-dot<?php echo $gi === 0 ? ' active' : ''; ?>"
									data-go="<?php echo $gi; ?>" aria-label="Image <?php echo $gi + 1; ?>"></button>
							<?php endforeach; ?>
						</div>
						<button type="button" class="ch-orig-slide-arrow ch-orig-slide-prev" aria-label="Previous">‹</button>
						<button type="button" class="ch-orig-slide-arrow ch-orig-slide-next" aria-label="Next">›</button>
					<?php endif; ?>
				<?php else : ?>
					<div class="ch-orig-placeholder">
						<span class="ch-orig-placeholder-flag"><?php echo esc_html( $flag ); ?></span>
					</div>
				<?php endif; ?>
				<?php if ( $videmb ) : ?>
					<button type="button" class="ch-orig-video-btn"
						data-embed="<?php echo esc_attr( $videmb ); ?>" aria-label="Watch video">
						<span class="ch-orig-video-play">▶</span>
						<span>Watch Video</span>
					</button>
				<?php endif; ?>
			</div>
			<?php if ( $videmb ) : ?>
			<div class="ch-orig-video-wrap" hidden>
				<button type="button" class="ch-orig-video-close">✕ Close</button>
				<iframe class="ch-orig-iframe" src="" data-src="<?php echo esc_attr( $videmb ); ?>"
					frameborder="0" allow="autoplay; encrypted-media; fullscreen" allowfullscreen></iframe>
			</div>
			<?php endif; ?>
		</div>
		<?php
	}
}

$uk = (array) $uk;
?>

<section class="ch-orig-section">
	<div class="container">
	<?php get_template_part( 'components/section-header', null, [
			'tag'   => $tag,
			'title' => $title,
			'body'  => $subtitle,
		] ); ?>
	</div>

	<div class="ch-orig-split">

		<!-- ══ LEFT: UK hero - always visible, highlighted ════════════════ -->
		<div class="ch-orig-uk">
			<span class="ch-orig-uk-ribbon">★ The Cane House</span>
			<?php ch_orig_media_block( $uk['images'] ?? [], $uk['video_url'] ?? '', $uk['flag'] ?? '🇬🇧' ); ?>
			<div class="ch-orig-uk-body">
				<?php if ( ! empty( $uk['badge'] ) ) : ?>
					<div class="ch-orig-uk-badge"><?php echo esc_html( $uk['badge'] ); ?></div>
				<?php endif; ?>
				<div class="ch-orig-country-tag">
					<span class="ch-orig-content-flag"><?php echo esc_html( $uk['flag'] ?? '' ); ?></span>
					<span class="ch-orig-content-name"><?php echo esc_html( $uk['name'] ?? '' ); ?></span>
				</div>
				<h3 class="ch-orig-headline"><?php echo esc_html( $uk['headline'] ?? '' ); ?></h3>
				<p class="ch-orig-desc"><?php echo esc_html( $uk['desc'] ?? '' ); ?></p>
				<?php if ( ! empty( $uk['points'] ) ) : ?>
					<ul class="ch-orig-points">
						<?php foreach ( (array) $uk['points'] as $pt ) : ?>
							<li><?php echo esc_html( $pt ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>

		<!-- ══ RIGHT: origin countries - small, switchable ═══════════════ -->
		<div class="ch-orig-side">
			<div class="ch-orig-side-head">
				<span class="ch-orig-side-label">Where It Comes From</span>
				<div class="ch-orig-tabs" role="tablist">
					<?php foreach ( $origins as $i => $origin ) :
						$origin = (array) $origin;
						$oid    = esc_attr( $origin['id'] ?? 'orig-' . $i );
					?>
						<button class="ch-orig-tab<?php echo $i === 0 ? ' active' : ''; ?>"
							data-orig-target="<?php echo $oid; ?>"
							role="tab" aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>">
							<span class="ch-orig-tab-flag"><?php echo esc_html( $origin['flag'] ?? '🌍' ); ?></span>
							<span class="ch-orig-tab-name"><?php echo esc_html( $origin['name'] ?? '' ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="ch-orig-side-panels">
				<?php foreach ( $origins as $i => $origin ) :
					$origin = (array) $origin;
					$oid    = esc_attr( $origin['id'] ?? 'orig-' . $i );
				?>
					<div class="ch-orig-opanel<?php echo $i === 0 ? ' active' : ''; ?>" id="ch-orig-panel-<?php echo $oid; ?>">
						<?php ch_orig_media_block( $origin['images'] ?? [], $origin['video_url'] ?? '', $origin['flag'] ?? '🌍' ); ?>
						<div class="ch-orig-obody">
							<div class="ch-orig-country-tag">
								<span class="ch-orig-content-flag"><?php echo esc_html( $origin['flag'] ?? '' ); ?></span>
								<span class="ch-orig-content-name"><?php echo esc_html( $origin['name'] ?? '' ); ?></span>
								<?php if ( ! empty( $origin['tagline'] ) ) : ?>
									<span class="ch-orig-otagline">· <?php echo esc_html( $origin['tagline'] ); ?></span>
								<?php endif; ?>
							</div>
							<h3 class="ch-orig-headline ch-orig-headline--sm"><?php echo esc_html( $origin['headline'] ?? '' ); ?></h3>
							<p class="ch-orig-desc ch-orig-desc--sm"><?php echo esc_html( $origin['desc'] ?? '' ); ?></p>
							<?php if ( ! empty( $origin['points'] ) ) : ?>
								<ul class="ch-orig-points ch-orig-points--sm">
									<?php foreach ( (array) $origin['points'] as $pt ) : ?>
										<li><?php echo esc_html( $pt ); ?></li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

	</div><!-- /ch-orig-split -->
</section>

<script>
(function () {
	'use strict';

	// ── Tabs switch the RIGHT side only (UK stays put) ───────────────────
	var tabs   = document.querySelectorAll('.ch-orig-tab');
	var opanels = document.querySelectorAll('.ch-orig-opanel');

	tabs.forEach(function (tab) {
		tab.addEventListener('click', function () {
			var target = tab.getAttribute('data-orig-target');
			tabs.forEach(function (t) { t.classList.remove('active'); t.setAttribute('aria-selected', 'false'); });
			opanels.forEach(function (p) { p.classList.remove('active'); });
			tab.classList.add('active');
			tab.setAttribute('aria-selected', 'true');
			var panel = document.getElementById('ch-orig-panel-' + target);
			if (panel) panel.classList.add('active');
		});
	});

	// ── Autoplay slideshow (every media block runs its own) ──────────────
	function initSlideshow(wrap) {
		var slides = wrap.querySelectorAll('.ch-orig-slide');
		var dots   = wrap.querySelectorAll('.ch-orig-slide-dot');
		if (slides.length <= 1) return;
		var cur = 0, timer = null;

		function goTo(idx) {
			slides[cur].classList.remove('active');
			if (dots[cur]) dots[cur].classList.remove('active');
			cur = ((idx % slides.length) + slides.length) % slides.length;
			slides[cur].classList.add('active');
			if (dots[cur]) dots[cur].classList.add('active');
		}
		function startAuto() { timer = setInterval(function () { goTo(cur + 1); }, 4000); }
		function stopAuto()  { clearInterval(timer); }

		startAuto();
		dots.forEach(function (d, i) {
			d.addEventListener('click', function () { stopAuto(); goTo(i); startAuto(); });
		});
		var prev = wrap.querySelector('.ch-orig-slide-prev');
		var next = wrap.querySelector('.ch-orig-slide-next');
		if (prev) prev.addEventListener('click', function () { stopAuto(); goTo(cur - 1); startAuto(); });
		if (next) next.addEventListener('click', function () { stopAuto(); goTo(cur + 1); startAuto(); });
	}
	document.querySelectorAll('[data-orig-slides]').forEach(initSlideshow);

	// ── Video play / close ───────────────────────────────────────────────
	document.querySelectorAll('.ch-orig-video-btn').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var media   = btn.closest('.ch-orig-media');
			var slides  = media && media.querySelector('[data-orig-slides]');
			var wrap    = media && media.querySelector('.ch-orig-video-wrap');
			var iframe  = wrap  && wrap.querySelector('.ch-orig-iframe');
			if (!wrap || !iframe) return;
			iframe.src = iframe.getAttribute('data-src');
			if (slides) slides.style.display = 'none';
			wrap.hidden = false;
		});
	});
	document.querySelectorAll('.ch-orig-video-close').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var wrap   = btn.closest('.ch-orig-video-wrap');
			var media  = btn.closest('.ch-orig-media');
			var slides = media && media.querySelector('[data-orig-slides]');
			var iframe = wrap  && wrap.querySelector('.ch-orig-iframe');
			if (iframe) iframe.src = '';
			if (slides) slides.style.display = '';
			if (wrap)   wrap.hidden = true;
		});
	});
})();
</script>
