<?php
/**
 * common/frontend/PageModal.php
 *
 * Clean iframe modal for Terms & Conditions / Privacy Policy links.
 * Rendered via wp_footer hook. No header, sidebar, or footer — just content.
 *
 * Usage: adnOpenPageModal('https://example.com/page', 'Page Title')
 */

defined( 'ABSPATH' ) || exit;

function adn_render_page_modal(): void {
	?>
	<div id="adn-page-modal" class="adn-page-modal" style="display:none;" role="dialog" aria-modal="true" aria-label="">
		<div class="adn-page-modal__overlay" onclick="adnClosePageModal()"></div>
		<div class="adn-page-modal__dialog">
			<div class="adn-page-modal__header">
				<h3 class="adn-page-modal__title"></h3>
				<button type="button" class="adn-page-modal__close" onclick="adnClosePageModal()" aria-label="Close">
					<i class="fa-solid fa-xmark" aria-hidden="true"></i>
				</button>
			</div>
			<div class="adn-page-modal__body">
				<div class="adn-page-modal__spinner" id="adn-page-modal-spinner">
					<div class="adn-page-modal__spinner-icon"></div>
					<span>Loading...</span>
				</div>
				<iframe class="adn-page-modal__iframe" id="adn-page-modal-iframe" src="" style="display:none;"></iframe>
			</div>
		</div>
	</div>
	<script>
	function adnOpenPageModal(url, title) {
		var modal = document.getElementById('adn-page-modal');
		if (!modal) return;
		var iframe = document.getElementById('adn-page-modal-iframe');
		var spinner = document.getElementById('adn-page-modal-spinner');
		modal.querySelector('.adn-page-modal__title').textContent = title || '';
		if (spinner) spinner.style.display = 'flex';
		if (iframe) {
			iframe.style.display = 'none';
			iframe.onload = function() {
				if (spinner) spinner.style.display = 'none';
				iframe.style.display = 'block';
			};
			var sep = url.indexOf('?') === -1 ? '?' : '&';
			iframe.src = url + sep + 'dialog=true';
		}
		modal.style.display = 'flex';
		document.body.style.overflow = 'hidden';
	}
	function adnClosePageModal() {
		var modal = document.getElementById('adn-page-modal');
		if (!modal) return;
		var iframe = document.getElementById('adn-page-modal-iframe');
		var spinner = document.getElementById('adn-page-modal-spinner');
		modal.style.display = 'none';
		if (iframe) iframe.src = '';
		if (spinner) spinner.style.display = 'none';
		document.body.style.overflow = '';
	}
	document.addEventListener('keydown', function(e) {
		if (e.key === 'Escape') adnClosePageModal();
	});
	</script>
	<?php
}
