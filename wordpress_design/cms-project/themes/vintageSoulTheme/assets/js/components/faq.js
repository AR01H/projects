
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;

	function init() {
		dom.on(document, 'click', '.faq__trigger', function () {
			var item = this.closest('.faq__item');
			if (!item) return;
			var isOpen = item.classList.contains('is-open');
			item.classList.toggle('is-open', !isOpen);
			this.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
		});
	}

	window.VintageSoul.app.register('faq', init);
})(window, document);
