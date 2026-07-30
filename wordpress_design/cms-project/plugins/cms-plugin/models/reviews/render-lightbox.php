<?php
defined( 'ABSPATH' ) || exit;

/**
 * Shared photo lightbox for review cards (With Photos grid, Full Story
 * gallery). Printed at most once per page load no matter how many review
 * cards render - every card just marks its <img> with
 * class="ah-rv-lightbox-trigger" and a data-full="<large image url>"
 * attribute; a single delegated click listener (registered here) opens the
 * one shared overlay for any of them.
 *
 * Prev/next steps through the OTHER photos in that same review's gallery -
 * on open, it collects every .ah-rv-lightbox-trigger inside the clicked
 * one's nearest gallery wrapper (.ah-rv-wp-grid / .ah-rv-gallery / .ah-rv-strip),
 * so navigating never mixes photos from a different review. The arrows/
 * counter are hidden automatically when that review only has one photo.
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
	<button type="button" class="ah-rv-lightbox-nav ah-rv-lightbox-nav--prev" aria-label="<?php echo esc_attr__( 'Previous photo', 'ah-cms' ); ?>">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
	</button>
	<img id="ah-rv-lightbox-img" src="" alt="">
	<button type="button" class="ah-rv-lightbox-nav ah-rv-lightbox-nav--next" aria-label="<?php echo esc_attr__( 'Next photo', 'ah-cms' ); ?>">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
	</button>
	<div class="ah-rv-lightbox-count"></div>
</div>
<script>
(function () {
	var box   = document.getElementById('ah-rv-lightbox');
	var img   = document.getElementById('ah-rv-lightbox-img');
	if (!box || !img) return;
	var close = box.querySelector('.ah-rv-lightbox-close');
	var prev  = box.querySelector('.ah-rv-lightbox-nav--prev');
	var next  = box.querySelector('.ah-rv-lightbox-nav--next');
	var count = box.querySelector('.ah-rv-lightbox-count');

	var gallery = [];
	var index   = 0;

	function galleryFor(trigger) {
		var scope = trigger.closest('.ah-rv-wp-grid, .ah-rv-gallery, .ah-rv-strip');
		var nodes = scope ? scope.querySelectorAll('.ah-rv-lightbox-trigger') : [trigger];
		return Array.prototype.slice.call(nodes);
	}
	function show() {
		var t = gallery[index];
		img.src = t.getAttribute('data-full') || t.src;
		var multi = gallery.length > 1;
		prev.style.display = multi ? '' : 'none';
		next.style.display = multi ? '' : 'none';
		count.style.display = multi ? '' : 'none';
		count.textContent = multi ? (index + 1) + ' / ' + gallery.length : '';
	}
	function open(trigger) {
		gallery = galleryFor(trigger);
		index = gallery.indexOf(trigger);
		if (index < 0) index = 0;
		show();
		box.classList.add('is-open');
	}
	function shut() { box.classList.remove('is-open'); img.src = ''; }
	function goPrev() { index = (index - 1 + gallery.length) % gallery.length; show(); }
	function goNext() { index = (index + 1) % gallery.length; show(); }

	document.addEventListener('click', function (e) {
		var trigger = e.target.closest ? e.target.closest('.ah-rv-lightbox-trigger') : null;
		if (trigger) {
			e.preventDefault();
			open(trigger);
			return;
		}
		if (e.target === box) { shut(); }
	});
	close.addEventListener('click', shut);
	prev.addEventListener('click', goPrev);
	next.addEventListener('click', goNext);
	document.addEventListener('keydown', function (e) {
		if (!box.classList.contains('is-open')) return;
		if (e.key === 'Escape') shut();
		else if (e.key === 'ArrowLeft') goPrev();
		else if (e.key === 'ArrowRight') goNext();
	});
})();
</script>
	<?php
	return (string) ob_get_clean();
}
