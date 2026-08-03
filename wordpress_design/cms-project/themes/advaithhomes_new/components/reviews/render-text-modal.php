<?php
defined( 'ABSPATH' ) || exit;

/**
 * Shared "full review" text popup - printed at most once per page load no
 * matter how many review cards render. Any card that needs it renders an
 * expand button with class="ah-rv-text-modal-trigger" plus a sibling
 * <template class="ah-rv-text-modal-content"> holding the full review
 * markup (a <template> so the already-wp_kses_post()'d HTML doesn't need to
 * survive an attribute-escaping round trip); a single delegated click
 * listener (registered here) opens the one shared overlay for any of them.
 * Used first by the home page carousel card (render-carousel-card.php) but
 * reusable by any review card type that needs a "read more" popup.
 *
 * Theme-level override of plugins/cms-plugin/models/reviews/render-text-modal.php -
 * see render-big.php in this folder for how the override resolves.
 */
function ah_review_render_text_modal_once(): string {
	static $printed = false;
	if ( $printed ) {
		return '';
	}
	$printed = true;

	ob_start();
	?>
<div id="ah-rv-text-modal" class="ah-rv-text-modal" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr__( 'Full review', 'ah-cms' ); ?>">
	<div class="ah-rv-text-modal-panel">
		<button type="button" class="ah-rv-text-modal-close" aria-label="<?php echo esc_attr__( 'Close', 'ah-cms' ); ?>">&times;</button>
		<div class="ah-rv-text-modal-body"></div>
	</div>
</div>
<script>
(function () {
	var box = document.getElementById('ah-rv-text-modal');
	if (!box) return;
	var body  = box.querySelector('.ah-rv-text-modal-body');
	var close = box.querySelector('.ah-rv-text-modal-close');

	function open(trigger) {
		var scope = trigger.closest('.ah-review-card') || trigger.parentElement;
		var tpl   = scope ? scope.querySelector('.ah-rv-text-modal-content') : null;
		if (!tpl) return;
		body.innerHTML = '';
		body.appendChild(tpl.content.cloneNode(true));
		box.classList.add('is-open');
	}
	function shut() { box.classList.remove('is-open'); body.innerHTML = ''; }

	document.addEventListener('click', function (e) {
		var trigger = e.target.closest ? e.target.closest('.ah-rv-text-modal-trigger') : null;
		if (trigger) {
			e.preventDefault();
			open(trigger);
			return;
		}
		if (e.target === box) { shut(); }
	});
	close.addEventListener('click', shut);
	document.addEventListener('keydown', function (e) {
		if (!box.classList.contains('is-open')) return;
		if (e.key === 'Escape') shut();
	});
})();
</script>
	<?php
	return (string) ob_get_clean();
}
