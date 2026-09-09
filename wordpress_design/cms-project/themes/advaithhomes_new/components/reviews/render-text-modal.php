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
	<div class="ah-rv-text-modal-backdrop"></div>
	<div class="ah-rv-text-modal-panel">
		<button type="button" class="ah-rv-text-modal-close" aria-label="<?php echo esc_attr__( 'Close', 'ah-cms' ); ?>">
			<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
		</button>
		<div class="ah-rv-text-modal-body"></div>
	</div>
</div>
<script>
(function () {
	var box = document.getElementById('ah-rv-text-modal');
	if (!box) return;
	var body     = box.querySelector('.ah-rv-text-modal-body');
	var close    = box.querySelector('.ah-rv-text-modal-close');
	var backdrop = box.querySelector('.ah-rv-text-modal-backdrop');

	function ensureMounted() {
		if (box.parentNode && box.parentNode !== document.body) {
			document.body.appendChild(box);
		}
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', ensureMounted);
	} else {
		ensureMounted();
	}

	function open(trigger) {
		ensureMounted();
		var scope = trigger.closest('.ah-review-card') || trigger.parentElement;
		var tpl   = scope ? scope.querySelector('.ah-rv-text-modal-content') : null;
		if (!tpl) return;
		body.innerHTML = '';
		body.appendChild(tpl.content.cloneNode(true));
		box.classList.add('is-open');
		document.body.style.overflow = 'hidden';
	}
	function shut() {
		box.classList.remove('is-open');
		document.body.style.overflow = '';
		setTimeout(function(){ if (!box.classList.contains('is-open')) body.innerHTML = ''; }, 300);
	}

	document.addEventListener('click', function (e) {
		var trigger = e.target.closest ? e.target.closest('.ah-rv-text-modal-trigger') : null;
		if (trigger) {
			e.preventDefault();
			open(trigger);
			return;
		}
		if (e.target === box || e.target === backdrop) { shut(); }
	});
	if (close) close.addEventListener('click', shut);
	if (backdrop) backdrop.addEventListener('click', shut);
	document.addEventListener('keydown', function (e) {
		if (!box.classList.contains('is-open')) return;
		if (e.key === 'Escape') shut();
	});
})();
</script>
	<?php
	return (string) ob_get_clean();
}
