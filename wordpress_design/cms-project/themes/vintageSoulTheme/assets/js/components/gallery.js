
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;

	function applyFilter(gallery, filter, showAll) {
		dom.qsa('.gallery__item', gallery).forEach(function (item) {
			item.hidden = ! ( showAll || item.dataset.galleryCategory === filter );
		});
	}

	function init() {
		dom.qsa('[data-vs-gallery]').forEach(function (gallery) {
			var tabs = dom.qsa('.gallery__tab', gallery);
			if (!tabs.length) return;
			var firstFilter = tabs[0].dataset.galleryFilter;

			dom.on(gallery, 'click', '.gallery__tab', function () {
				var target = this;
				var filter = target.dataset.galleryFilter;
				tabs.forEach(function (tab) {
					var isActive = tab === target;
					tab.classList.toggle('is-active', isActive);
					tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
				});
				applyFilter(gallery, filter, filter === firstFilter);
			});
		});
	}

	window.VintageSoul.app.register('gallery', init);
})(window, document);
