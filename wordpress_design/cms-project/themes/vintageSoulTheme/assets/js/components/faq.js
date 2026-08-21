
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;

	function close(item) {
		item.classList.remove('is-open');
		var trigger = dom.qs('.faq__trigger', item);
		if (trigger) trigger.setAttribute('aria-expanded', 'false');
	}

	function init() {
		dom.on(document, 'click', '.faq__trigger', function () {
			var item = this.closest('.faq__item');
			if (!item) return;

			var willOpen = !item.classList.contains('is-open');

			// Only one answer open at a time, scoped to this list so two FAQ
			// components on the same page don't close each other's items.
			var list = item.closest('.faq__list');
			if (list) {
				dom.qsa('.faq__item.is-open', list).forEach(function (other) {
					if (other !== item) close(other);
				});
			}

			item.classList.toggle('is-open', willOpen);
			this.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
		});
	}

	window.VintageSoul.app.register('faq', init);
})(window, document);
