<?php
defined( 'ABSPATH' ) || exit;

/**
 * Shared flip-card click behavior for Big Box / Mini Card / With Photos -
 * printed at most once per page load no matter how many flip cards render,
 * same static-guard pattern as ah_review_render_lightbox_once().
 *
 * Each flip card is a .ah-rv-flip element; CSS already flips it on :hover
 * (desktop mouse) via review-cards-base.css. This delegated listener adds a
 * persistent .is-flipped toggle on click/tap, since touch devices have no
 * real hover state - tapping a card flips it and it stays flipped until
 * tapped again. Clicks on a photo (With Photos' lightbox trigger) are
 * ignored here so opening the lightbox doesn't also flip the card back.
 *
 * Theme-level override of plugins/cms-plugin/models/reviews/render-flip-behavior.php -
 * see render-big.php in this folder for how the override resolves.
 */
function ah_review_render_flip_script_once(): string {
	static $printed = false;
	if ( $printed ) {
		return '';
	}
	$printed = true;

	ob_start();
	?>
<script>
(function () {
	document.addEventListener('click', function (e) {
		if (e.target.closest && e.target.closest('.ah-rv-lightbox-trigger')) return;
		var card = e.target.closest ? e.target.closest('.ah-rv-flip') : null;
		if (card) card.classList.toggle('is-flipped');
	});
})();
</script>
	<?php
	return (string) ob_get_clean();
}
