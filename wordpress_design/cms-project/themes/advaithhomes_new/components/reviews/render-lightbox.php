<?php
defined( 'ABSPATH' ) || exit;

/**
 * Shared photo lightbox for review cards (With Photos strip, Full Story gallery).
 * Printed at most once per page load no matter how many review cards render -
 * every card just marks its <img> with class="ah-rv-lightbox-trigger" and a
 * data-full="<large image url>" attribute; a single delegated click listener
 * (registered here) opens the one shared overlay for any of them.
 *
 * Theme-level override of plugins/cms-plugin/models/reviews/render-lightbox.php -
 * see render-big.php in this folder for how the override resolves.
 */
function ah_review_render_lightbox_once(): string {
	static $printed = false;
	if ( $printed ) {
		return '';
	}
	$printed = true;

	ob_start();
	?>
<div id="ah-rv-lightbox" class="ah-rv-lightbox" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__( 'Photo preview', 'ah-cms' ); ?>">
	<button type="button" class="ah-rv-lightbox-close" aria-label="<?php echo esc_attr__( 'Close', 'ah-cms' ); ?>">&times;</button>
	<img id="ah-rv-lightbox-img" src="" alt="">
</div>
<script>
(function () {
	var box   = document.getElementById('ah-rv-lightbox');
	var img   = document.getElementById('ah-rv-lightbox-img');
	if (!box || !img) return;
	var close = box.querySelector('.ah-rv-lightbox-close');

	function open(src) { img.src = src; box.classList.add('is-open'); }
	function shut()     { box.classList.remove('is-open'); img.src = ''; }

	document.addEventListener('click', function (e) {
		var trigger = e.target.closest ? e.target.closest('.ah-rv-lightbox-trigger') : null;
		if (trigger) {
			e.preventDefault();
			open(trigger.getAttribute('data-full') || trigger.src);
			return;
		}
		if (e.target === box) { shut(); }
	});
	close.addEventListener('click', shut);
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape' && box.classList.contains('is-open')) { shut(); }
	});
})();
</script>
	<?php
	return (string) ob_get_clean();
}
