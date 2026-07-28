<?php
defined( 'ABSPATH' ) || exit;

/**
 * Shared photo lightbox for review cards (With Photos strip, Full Story gallery).
 * Printed at most once per page load no matter how many review cards render -
 * every card just marks its <img> with class="ah-rv-lightbox-trigger" and a
 * data-full="<large image url>" attribute; a single delegated click listener
 * (registered here) opens the one shared overlay for any of them.
 */
function ah_review_render_lightbox_once(): string {
	static $printed = false;
	if ( $printed ) {
		return '';
	}
	$printed = true;

	ob_start();
	?>
<style>
.ah-rv-lightbox-trigger{cursor:zoom-in}
.ah-rv-lightbox{display:none;position:fixed;inset:0;background:rgba(15,17,16,.88);z-index:99999;align-items:center;justify-content:center;padding:24px;box-sizing:border-box}
.ah-rv-lightbox.is-open{display:flex}
.ah-rv-lightbox img{max-width:92vw;max-height:88vh;border-radius:10px;box-shadow:0 20px 60px rgba(0,0,0,.4)}
.ah-rv-lightbox-close{position:absolute;top:18px;right:22px;background:rgba(255,255,255,.12);border:none;color:#fff;width:38px;height:38px;border-radius:50%;font-size:20px;line-height:1;cursor:pointer;display:flex;align-items:center;justify-content:center}
.ah-rv-lightbox-close:hover{background:rgba(255,255,255,.22)}
</style>
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
