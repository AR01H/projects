<?php
/**
 * components/sections/guides.php - Section: Guides & Insights carousel
 *
 * Props: $items [ { icon, gradient, category, title, description, read_more, url } ]
 * Usage: adn_component( 'sections/guides', array( 'items' => $ctx['guides']['items'] ) );
 */

defined( 'ABSPATH' ) || exit;

$items = isset( $items ) ? (array) $items : array();
if ( empty( $items ) ) { return; }

static $adn_guides_instance = 0;
$adn_guides_instance++;
$_wrap_id = 'guides-carousel-wrap-' . $adn_guides_instance;
?>
<div class="guides-carousel-wrap" id="<?php echo esc_attr( $_wrap_id ); ?>">

	<button class="guides-carousel-btn guides-carousel-btn--prev"
	        type="button"
	        aria-label="<?php echo esc_attr( sprintf( __( 'Previous %s', ADN_TEXT_DOMAIN ), SITE_CONTENT_PLURAL ) ); ?>"
	        hidden>
		&#8249;
	</button>

	<div class="guides-carousel" role="region" aria-label="<?php echo esc_attr( SITE_CONTENT_PLURAL . ' &amp; Insights' ); ?>">
		<?php foreach ( $items as $card ) : ?>
			<?php adn_component( 'cards/guide_card', array( 'card' => $card ) ); ?>
		<?php endforeach; ?>
	</div>

	<button class="guides-carousel-btn guides-carousel-btn--next"
			type="button"
			aria-label="<?php echo esc_attr( sprintf( __( 'Next %s', ADN_TEXT_DOMAIN ), SITE_CONTENT_PLURAL ) ); ?>">
		&#8250;
	</button>

	<div class="guides-carousel-dots" aria-hidden="true"></div>

</div>
<script>
(function(){
	'use strict';
	var wrap  = document.getElementById('<?php echo esc_js( $_wrap_id ); ?>');
	if (!wrap) return;
	var track = wrap.querySelector('.guides-carousel');
	var prev  = wrap.querySelector('.guides-carousel-btn--prev');
	var next  = wrap.querySelector('.guides-carousel-btn--next');
	var dotsContainer = wrap.querySelector('.guides-carousel-dots');

	var cards = Array.prototype.slice.call(track.querySelectorAll('.guide-card'));
	var current = 0;
	var autoplayInterval = 4000;
	var autoplayTimer = null;

	function scrollToIndex(i){
		if (!cards[i]) return;
		var left = cards[i].offsetLeft - Math.max(0, (track.offsetWidth - cards[i].offsetWidth) / 2);
		track.scrollTo({ left: left, behavior: 'smooth' });
		setActive(i);
	}

	function setActive(i){
		current = i;
		var dots = dotsContainer.querySelectorAll('.guides-carousel-dot');
		dots.forEach(function(d, idx){ d.classList.toggle('active', idx === i); });
		updateBtns();
	}

	function updateBtns(){
		var sl = track.scrollLeft;
		var maxScroll = track.scrollWidth - track.offsetWidth;
		if (prev) { sl > 4 ? prev.removeAttribute('hidden') : prev.setAttribute('hidden', ''); }
		if (next) { sl < maxScroll - 4 ? next.removeAttribute('hidden') : next.setAttribute('hidden', ''); }
	}

	// build dots
	cards.forEach(function(c, idx){
		var dot = document.createElement('button');
		dot.type = 'button';
		dot.className = 'guides-carousel-dot' + (idx===0? ' active':'');
		dot.setAttribute('aria-label', 'Go to slide ' + (idx+1));
		dot.addEventListener('click', function(){ stopAutoplay(); scrollToIndex(idx); });
		dotsContainer.appendChild(dot);
	});

	if (prev) prev.addEventListener('click', function(){ stopAutoplay(); scrollToIndex(Math.max(0, current-1)); });
	if (next) next.addEventListener('click', function(){ stopAutoplay(); scrollToIndex(Math.min(cards.length-1, current+1)); });

	// update active on scroll (debounced)
	var scTimer = null;
	track.addEventListener('scroll', function(){
		updateBtns();
		if (scTimer) clearTimeout(scTimer);
		scTimer = setTimeout(function(){
			var center = track.scrollLeft + track.offsetWidth/2;
			var nearest = 0; var nearestDist = Infinity;
			cards.forEach(function(c, idx){
				var cCenter = c.offsetLeft + c.offsetWidth/2;
				var dist = Math.abs(center - cCenter);
				if (dist < nearestDist){ nearestDist = dist; nearest = idx; }
			});
			setActive(nearest);
		}, 80);
	}, { passive: true });

	// keyboard navigation
	wrap.addEventListener('keydown', function(e){
		if (e.key === 'ArrowRight') { stopAutoplay(); scrollToIndex(Math.min(cards.length-1, current+1)); }
		if (e.key === 'ArrowLeft')  { stopAutoplay(); scrollToIndex(Math.max(0, current-1)); }
	});
	wrap.setAttribute('tabindex','0');

	function startAutoplay(){
		if (autoplayTimer) return;
		autoplayTimer = setInterval(function(){
			var nextIdx = (current + 1) % cards.length;
			scrollToIndex(nextIdx);
		}, autoplayInterval);
	}
	function stopAutoplay(){ if (autoplayTimer) { clearInterval(autoplayTimer); autoplayTimer = null; } }

	// pause on hover/focus
	wrap.addEventListener('mouseenter', stopAutoplay);
	wrap.addEventListener('mouseleave', startAutoplay);
	wrap.addEventListener('focusin', stopAutoplay);
	wrap.addEventListener('focusout', startAutoplay);

	// init
	window.addEventListener('resize', function(){ /* ensure dots align */ });
	updateBtns();
	startAutoplay();
}());
</script>
