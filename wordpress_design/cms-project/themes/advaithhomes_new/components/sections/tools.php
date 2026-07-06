<?php
/**
 * components/sections/tools.php - Section: Popular Tools carousel
 *
 * Renders the calculator cards as a horizontal carousel (same .cgt-* pattern
 * used on the category-guide page) so the calculators slide like a carousel
 * on the home page and anywhere this section is used.
 *
 * Props: $items [ { icon, name, url } ]
 * Usage: adn_component( 'sections/tools', array( 'items' => $ctx['calculators']['items'] ) );
 */

defined( 'ABSPATH' ) || exit;

$items = isset( $items ) ? (array) $items : array();
if ( empty( $items ) ) {
	return;
}
?>
<div class="cgt-carousel-wrap">
	<div class="cgt-track">
		<?php foreach ( $items as $card ) : ?>
			<?php adn_component( 'cards/tool_card', array( 'card' => $card ) ); ?>
		<?php endforeach; ?>
	</div>
	<button class="cgt-arrow cgt-arrow--prev" aria-label="<?php esc_attr_e( 'Previous', ADN_TEXT_DOMAIN ); ?>">
		<i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
	</button>
	<button class="cgt-arrow cgt-arrow--next" aria-label="<?php esc_attr_e( 'Next', ADN_TEXT_DOMAIN ); ?>">
		<i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
	</button>
</div>
<script>
/* Injection-safe: no document.currentScript, so this also works when the
   section arrives via the AJAX fragment loader. Re-runnable + idempotent. */
(function(){
	function initOne(wrap){
		if(wrap.dataset.cgtInit){ return; }
		wrap.dataset.cgtInit = '1';
		var track = wrap.querySelector('.cgt-track');
		var prev  = wrap.querySelector('.cgt-arrow--prev');
		var next  = wrap.querySelector('.cgt-arrow--next');
		if(!track||!prev||!next){ return; }
		function cardW(){ var c=track.children[0]; return c ? c.offsetWidth + parseInt(getComputedStyle(track).gap||0) : 320; }
		function upd(){
			prev.classList.toggle('cgt-arrow--hidden', track.scrollLeft <= 2);
			next.classList.toggle('cgt-arrow--hidden', track.scrollLeft >= track.scrollWidth - track.clientWidth - 2);
		}
		prev.addEventListener('click', function(){ track.scrollBy({left:-cardW()*2, behavior:'smooth'}); });
		next.addEventListener('click', function(){ track.scrollBy({left: cardW()*2, behavior:'smooth'}); });
		track.addEventListener('scroll', upd, {passive:true});
		window.addEventListener('resize', upd, {passive:true});
		upd();
	}
	window.adnCgtInitAll = function(root){
		[].forEach.call((root||document).querySelectorAll('.cgt-carousel-wrap'), initOne);
	};
	window.adnCgtInitAll();
}());
</script>
