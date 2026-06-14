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
	        aria-label="<?php esc_attr_e( 'Previous guides', ADN_TEXT_DOMAIN ); ?>"
	        hidden>
		&#8249;
	</button>

	<div class="guides-carousel" role="region" aria-label="<?php esc_attr_e( 'Guides & Insights', ADN_TEXT_DOMAIN ); ?>">
		<?php foreach ( $items as $card ) : ?>
			<?php adn_component( 'cards/guide_card', array( 'card' => $card ) ); ?>
		<?php endforeach; ?>
	</div>

	<button class="guides-carousel-btn guides-carousel-btn--next"
	        type="button"
	        aria-label="<?php esc_attr_e( 'Next guides', ADN_TEXT_DOMAIN ); ?>">
		&#8250;
	</button>

</div>
<script>
(function(){
	'use strict';
	var wrap  = document.getElementById('<?php echo esc_js( $_wrap_id ); ?>');
	if (!wrap) return;
	var track = wrap.querySelector('.guides-carousel');
	var prev  = wrap.querySelector('.guides-carousel-btn--prev');
	var next  = wrap.querySelector('.guides-carousel-btn--next');

	function cardWidth() {
		var c = track.querySelector('.guide-card');
		return c ? c.offsetWidth + 16 : 280;
	}

	function updateBtns() {
		var sl = track.scrollLeft;
		var maxScroll = track.scrollWidth - track.offsetWidth;
		if (prev) { sl > 4 ? prev.removeAttribute('hidden') : prev.setAttribute('hidden', ''); }
		if (next) { sl < maxScroll - 4 ? next.removeAttribute('hidden') : next.setAttribute('hidden', ''); }
	}

	if (prev) prev.addEventListener('click', function(){ track.scrollBy({ left: -cardWidth(), behavior: 'smooth' }); });
	if (next) next.addEventListener('click', function(){ track.scrollBy({ left:  cardWidth(), behavior: 'smooth' }); });
	track.addEventListener('scroll', updateBtns, { passive: true });
	window.addEventListener('resize', updateBtns);
	updateBtns();
}());
</script>
