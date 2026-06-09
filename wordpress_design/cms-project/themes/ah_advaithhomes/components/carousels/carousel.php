<?php
/**
 * Unified Carousel
 *
 * @param array  $args['items']
 * @param string $args['type']               'feature'|'image'|'step'|'selector'
 * @param bool   $args['showDots']           default true
 * @param bool   $args['showArrows']         default true
 * @param bool   $args['autoplay']           default false
 * @param int    $args['autoplaySpeed']      ms  (default 4000)
 * @param bool   $args['infiniteLoop']       default true
 * @param bool   $args['pauseOnHover']       default true
 * @param int    $args['cardsPerView']       desktop (default 3)
 * @param int    $args['tabletCardsPerView'] (default 2)
 * @param int    $args['mobileCardsPerView'] (default 1)
 * @param int    $args['scrollStep']         0 = cardsPerView
 * @param int    $args['gap']               px  (default 24)
 * @param int    $args['animationDuration'] ms  (default 400)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

require_once get_template_directory() . '/components/carousels/_card-renderer.php';
require_once get_template_directory() . '/components/carousels/_card-variants.php';

$items = (array) ( $args['items'] ?? [] );
$type  = $args['type'] ?? 'feature';
if ( empty( $items ) ) return;

$show_dots   = ! isset( $args['showDots'] )     || (bool) $args['showDots'];
$show_arrows = ! isset( $args['showArrows'] )   || (bool) $args['showArrows'];
$autoplay    = isset( $args['autoplay'] )        && (bool) $args['autoplay'];
$infinite    = ! isset( $args['infiniteLoop'] )  || (bool) $args['infiniteLoop'];
$pause_hover = ! isset( $args['pauseOnHover'] )  || (bool) $args['pauseOnHover'];

$speed    = max( 500,  (int) ( $args['autoplaySpeed']      ?? 4000 ) );
$cpv_d    = max( 1,    (int) ( $args['cardsPerView']        ?? 3    ) );
$cpv_t    = max( 1,    (int) ( $args['tabletCardsPerView']  ?? min( 2, $cpv_d ) ) );
$cpv_m    = max( 1,    (int) ( $args['mobileCardsPerView']  ?? 1    ) );
$gap      = max( 0,    (int) ( $args['gap']                 ?? 24   ) );
$step     = max( 0,    (int) ( $args['scrollStep']          ?? 0    ) );
$dur      = max( 100,  (int) ( $args['animationDuration']   ?? 400  ) );

static $uid = 0;
$uid++;
$id = 'ccr-' . $uid;
?>

<div class="cc-carousel"
	id="<?php echo esc_attr( $id ); ?>"
	data-d="<?php echo esc_attr( $cpv_d ); ?>"
	data-t="<?php echo esc_attr( $cpv_t ); ?>"
	data-m="<?php echo esc_attr( $cpv_m ); ?>"
	data-g="<?php echo esc_attr( $gap ); ?>"
	data-s="<?php echo esc_attr( $step ); ?>"
	data-ap="<?php echo $autoplay ? '1' : '0'; ?>"
	data-sp="<?php echo esc_attr( $speed ); ?>"
	data-inf="<?php echo $infinite ? '1' : '0'; ?>"
	data-ph="<?php echo $pause_hover ? '1' : '0'; ?>"
	data-dur="<?php echo esc_attr( $dur ); ?>">

	<div class="cc-carousel__row">

		<?php if ( $show_arrows ) : ?>
		<button class="cc-carousel__btn cc-carousel__btn--prev" type="button" aria-label="Previous">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
				stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<polyline points="15 18 9 12 15 6"></polyline>
			</svg>
		</button>
		<?php endif; ?>

		<div class="cc-carousel__win">
			<div class="cc-carousel__track">
				<?php foreach ( $items as $item ) :
					$item    = (array) $item;
					$variant = $item['variant'] ?? '';
				?>
				<div class="cc-carousel__cell">
					<?php echo $variant
						? cc_render_card_variant( $item, $variant )
						: cc_render_card( $item, $type ); ?>
				</div>
				<?php endforeach; ?>
			</div>
		</div>

		<?php if ( $show_arrows ) : ?>
		<button class="cc-carousel__btn cc-carousel__btn--next" type="button" aria-label="Next">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
				stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
				<polyline points="9 18 15 12 9 6"></polyline>
			</svg>
		</button>
		<?php endif; ?>

	</div>

	<?php if ( $show_dots ) : ?>
	<div class="cc-carousel__dots"></div>
	<?php endif; ?>

</div>

<?php if ( ! defined( 'CC_CAROUSEL_CSS_V2' ) ) : define( 'CC_CAROUSEL_CSS_V2', true ); ?>
<style>
/* ── Shell ─────────────────────────────────────────────────────── */
.cc-carousel { position: relative; width: 100%; }

.cc-carousel__row {
	display: flex;
	align-items: center;
	gap: 12px;
}

.cc-carousel__win {
	flex: 1 1 0%;
	min-width: 0;
	overflow: hidden;
}

/* Track: no transition by default. JS adds .is-sliding to animate. */
.cc-carousel__track {
	display: flex;
	align-items: stretch;
}

.cc-carousel__track.is-sliding {
	transition: transform var(--cc-dur, 400ms) ease;
}

.cc-carousel__cell {
	flex: 0 0 auto;
	box-sizing: border-box;
}

/* ── Arrows ────────────────────────────────────────────────────── */
.cc-carousel__btn {
	flex: 0 0 44px;
	width: 44px;
	height: 44px;
	border-radius: 50%;
	border: none;
	padding: 0;
	cursor: pointer;
	background: var(--client-color-11, #f0f5ea);
	color: var(--client-color-1, #1e1e1e);
	display: flex;
	align-items: center;
	justify-content: center;
	box-shadow: 0 2px 10px rgba(0,0,0,0.10);
	transition: background .22s, box-shadow .22s, transform .18s;
}

.cc-carousel__btn svg {
	width: 18px;
	height: 18px;
	pointer-events: none;
}

.cc-carousel__btn:hover:not(:disabled) {
	background: var(--client-color-7, #5a9e3a);
	color: #fff;
	box-shadow: 0 4px 16px rgba(0,0,0,0.16);
	transform: scale(1.06);
}

.cc-carousel__btn:disabled { opacity: 0.25; cursor: not-allowed; }
.cc-carousel__btn[hidden]  { visibility: hidden; pointer-events: none; }

/* ── Dots ──────────────────────────────────────────────────────── */
.cc-carousel__dots {
	display: flex;
	justify-content: center;
	align-items: center;
	gap: 8px;
	margin-top: 20px;
	flex-wrap: wrap;
}

.cc-carousel__dot {
	width: 8px;
	height: 8px;
	border-radius: 50%;
	border: none;
	background: var(--client-color-4, #c8d8b0);
	cursor: pointer;
	padding: 0;
	opacity: 0.45;
	transition: all .28s ease;
}

.cc-carousel__dot:hover { opacity: .8; transform: scale(1.25); }

.cc-carousel__dot.is-on {
	width: 24px;
	border-radius: 4px;
	background: var(--client-color-7, #5a9e3a);
	opacity: 1;
}

/* ── Responsive ────────────────────────────────────────────────── */
@media (max-width: 900px) {
	.cc-carousel__btn { flex: 0 0 38px; width: 38px; height: 38px; }
	.cc-carousel__btn svg { width: 16px; height: 16px; }
}

@media (max-width: 640px) {
	.cc-carousel__row { gap: 6px; }
	.cc-carousel__btn { flex: 0 0 32px; width: 32px; height: 32px; }
	.cc-carousel__btn svg { width: 13px; height: 13px; }
	.cc-carousel__dots { margin-top: 14px; gap: 6px; }
}
</style>
<?php endif; ?>

<script>
(function () {
	'use strict';

	/* Run after two animation frames so the browser has computed layout.
	   Inline scripts run mid-parse; offsetWidth can be 0 at that point. */
	requestAnimationFrame(function () {
		requestAnimationFrame(function () {

	var ROOT = document.getElementById('<?php echo esc_js( $id ); ?>');
	if (!ROOT) return;

	/* ── Config ─────────────────────────────────────────────────── */
	var ds  = ROOT.dataset;
	var CPV_D   = Math.max(1, +ds.d  || 3);
	var CPV_T   = Math.max(1, +ds.t  || 2);
	var CPV_M   = Math.max(1, +ds.m  || 1);
	var GAP     = +ds.g   || 0;
	var STEP    = +ds.s   || 0;
	var INFINITE = ds.inf === '1';
	var AUTOPLAY = ds.ap  === '1';
	var SPEED   = +ds.sp  || 4000;
	var PAUSE   = ds.ph   === '1';
	var DUR     = +ds.dur || 400;

	ROOT.style.setProperty('--cc-dur', DUR + 'ms');

	/* ── Elements ────────────────────────────────────────────────── */
	var WIN   = ROOT.querySelector('.cc-carousel__win');
	var TRACK = ROOT.querySelector('.cc-carousel__track');
	var DOTS  = ROOT.querySelector('.cc-carousel__dots');
	var PREV  = ROOT.querySelector('.cc-carousel__btn--prev');
	var NEXT  = ROOT.querySelector('.cc-carousel__btn--next');

	if (!TRACK || !WIN) return;

	/* Snapshot of real (non-clone) cells - captured once before cloning */
	var ORIG  = Array.from(TRACK.children);
	var TOTAL = ORIG.length;
	if (!TOTAL) return;

	/* ── Runtime state ───────────────────────────────────────────── */
	var vis       = CPV_D;
	var stp       = STEP || vis;
	var pages     = 1;
	var page      = 0;       // logical page 0…pages-1 (may briefly be -1 or pages)
	var clonePre  = 0;       // cells prepended as clones
	var itemW     = 0;
	var locked    = false;   // nav debounce
	var aTimer    = null;

	/* ── Helpers ─────────────────────────────────────────────────── */
	function getVis() {
		var w = window.innerWidth;
		return w <= 640 ? CPV_M : w <= 900 ? CPV_T : CPV_D;
	}

	/* Pixel offset for a given cell index in the track */
	function offsetFor(cellIdx) {
		return -(cellIdx * (itemW + GAP));
	}

	/* Cell index in track for a logical page */
	function cellOf(p) {
		return clonePre + p * stp;
	}

	/* ── Move track ──────────────────────────────────────────────── */

	/* Instant snap - no animation.
	   We remove the .is-sliding class, force a reflow so the browser
	   applies 'transition: none' synchronously, then set the transform. */
	function snapTo(cellIdx) {
		TRACK.classList.remove('is-sliding');
		void TRACK.offsetWidth; /* <-- critical: flush style before transform */
		TRACK.style.transform = 'translateX(' + offsetFor(cellIdx) + 'px)';
	}

	/* Animated slide - adds .is-sliding so CSS transition fires */
	function slideTo(cellIdx) {
		TRACK.classList.add('is-sliding');
		TRACK.style.transform = 'translateX(' + offsetFor(cellIdx) + 'px)';
	}

	/* ── transitionend: handle infinite boundary snap ────────────── */
	TRACK.addEventListener('transitionend', function (e) {
		if (e.target !== TRACK || e.propertyName !== 'transform') return;

		if (!INFINITE || TOTAL <= vis) return;

		if (page < 0) {
			page = pages - 1;
			snapTo(cellOf(page));
			syncDots();
		} else if (page >= pages) {
			page = 0;
			snapTo(cellOf(page));
			syncDots();
		}
	});

	/* ── Navigation ──────────────────────────────────────────────── */
	function navigate(dir) {
		if (locked) return;

		var next = page + dir;

		if (!INFINITE) {
			if (next < 0 || next >= pages) return;
		}

		locked = true;
		setTimeout(function () { locked = false; }, DUR + 60);

		page = next;
		slideTo(cellOf(page));
		syncDots();
	}

	if (PREV) PREV.addEventListener('click', function () { navigate(-1); });
	if (NEXT) NEXT.addEventListener('click', function () { navigate(1); });

	/* ── Touch/swipe ─────────────────────────────────────────────── */
	var touchStartX = 0;
	WIN.addEventListener('touchstart', function (e) {
		touchStartX = e.touches[0].clientX;
	}, { passive: true });

	WIN.addEventListener('touchend', function (e) {
		var dx = touchStartX - e.changedTouches[0].clientX;
		if (Math.abs(dx) > 44) navigate(dx > 0 ? 1 : -1);
	}, { passive: true });

	/* ── Autoplay ────────────────────────────────────────────────── */
	function startPlay() {
		if (!AUTOPLAY) return;
		stopPlay();
		aTimer = setInterval(function () { navigate(1); }, SPEED);
	}

	function stopPlay() {
		clearInterval(aTimer);
		aTimer = null;
	}

	if (PAUSE) {
		ROOT.addEventListener('mouseenter', stopPlay);
		ROOT.addEventListener('mouseleave', startPlay);
	}

	/* ── Dot sync ────────────────────────────────────────────────── */
	function syncDots() {
		if (!DOTS) return;
		var active = ((page % pages) + pages) % pages;
		DOTS.querySelectorAll('.cc-carousel__dot').forEach(function (d, i) {
			d.classList.toggle('is-on', i === active);
		});
		if (!INFINITE) {
			if (PREV) PREV.disabled = page <= 0;
			if (NEXT) NEXT.disabled = page >= pages - 1;
		}
	}

	function buildDots() {
		if (!DOTS) return;
		DOTS.innerHTML = '';

		for (var i = 0; i < pages; i++) {
			(function (pg) {
				var btn = document.createElement('button');
				btn.className = 'cc-carousel__dot' + (pg === 0 ? ' is-on' : '');
				btn.setAttribute('aria-label', 'Page ' + (pg + 1));
				btn.addEventListener('click', function () {
					if (!locked) {
						locked = true;
						setTimeout(function () { locked = false; }, DUR + 60);
						page = pg;
						slideTo(cellOf(pg));
						syncDots();
					}
				});
				DOTS.appendChild(btn);
			})(i);
		}
	}

	/* ── Clone items for infinite loop ──────────────────────────── */
	function buildClones() {
		/* Remove clones from any previous call */
		Array.from(TRACK.querySelectorAll('.cc-carousel__cell--clone'))
			.forEach(function (n) { n.remove(); });

		if (!INFINITE || TOTAL <= vis) {
			clonePre = 0;
			return;
		}

		/*
		 * Prepend: clone of the last `vis` real items, in correct order.
		 * Insert them one by one at the front (last-first so order is preserved).
		 */
		var tailItems = ORIG.slice(-vis);
		for (var i = tailItems.length - 1; i >= 0; i--) {
			var cHead = tailItems[i].cloneNode(true);
			cHead.classList.add('cc-carousel__cell--clone');
			TRACK.insertBefore(cHead, TRACK.firstChild);
		}
		clonePre = vis;

		/* Append: clone of the first `vis` real items */
		ORIG.slice(0, vis).forEach(function (el) {
			var cTail = el.cloneNode(true);
			cTail.classList.add('cc-carousel__cell--clone');
			TRACK.appendChild(cTail);
		});
	}

	/* ── Compute widths ──────────────────────────────────────────── */
	function applyWidths() {
		var ww = WIN.offsetWidth;
		if (ww <= 0) return; /* layout not ready yet - skip */

		itemW = (ww - GAP * (vis - 1)) / vis;

		Array.from(TRACK.children).forEach(function (el) {
			el.style.width = itemW + 'px';
			el.style.flexShrink = '0';
		});

		TRACK.style.gap = GAP + 'px';
	}

	/* ── Control visibility ──────────────────────────────────────── */
	function updateControls() {
		var noNav = TOTAL <= vis;

		if (PREV) { if (noNav) PREV.setAttribute('hidden', ''); else PREV.removeAttribute('hidden'); }
		if (NEXT) { if (noNav) NEXT.setAttribute('hidden', ''); else NEXT.removeAttribute('hidden'); }
		if (DOTS) DOTS.style.display = noNav ? 'none' : '';

		/* For non-infinite, disable arrows at edges */
		if (!INFINITE && !noNav) {
			if (PREV) PREV.disabled = (page <= 0);
			if (NEXT) NEXT.disabled = (page >= pages - 1);
		} else {
			if (PREV) PREV.disabled = false;
			if (NEXT) NEXT.disabled = false;
		}
	}

	/* ── Full init ───────────────────────────────────────────────── */
	function init() {
		stopPlay();
		locked = false;
		page   = 0;

		vis   = getVis();
		stp   = STEP || vis;
		pages = Math.ceil(TOTAL / stp);

		buildClones();
		applyWidths();
		buildDots();
		updateControls();
		snapTo(cellOf(0));

		startPlay();
	}

	init();

	/* ── Resize ──────────────────────────────────────────────────── */
	var resizeTimer;
	window.addEventListener('resize', function () {
		clearTimeout(resizeTimer);
		resizeTimer = setTimeout(function () {
			stopPlay();
			init();
		}, 160);
	});

		}); /* end inner rAF */
	});    /* end outer rAF */

})();
</script>
<?php
