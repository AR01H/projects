
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;

	function applyFilter(gallery, filter, showAll) {
		var items = dom.qsa('.gallery__item, .look-back-card', gallery);
		items.forEach(function (item) {
			var cat = (item.dataset.galleryCategory || item.dataset.category || '').toLowerCase().trim();
			var isMatch = showAll || 'all' === filter || cat === filter || (filter === 'drinks' && cat.indexOf('drink') !== -1) || (filter === 'sugarcane' && cat.indexOf('sugarcane') !== -1);
			if (isMatch) {
				item.classList.remove('is-hidden');
				item.hidden = false;
				item.style.removeProperty('display');
			} else {
				item.classList.add('is-hidden');
				item.hidden = true;
				item.style.setProperty('display', 'none', 'important');
			}
		});
	}

	function init() {
		dom.qsa('[data-vs-gallery], #look-back-in-time, .look-back-vintage').forEach(function (gallery) {
			var tabs = dom.qsa('.gallery__tab, .gallery-tab', gallery);
			if (!tabs.length) return;
			var firstFilter = (tabs[0].dataset.galleryFilter || tabs[0].dataset.filter || 'all').toLowerCase().trim();

			dom.on(gallery, 'click', '.gallery__tab, .gallery-tab', function (e) {
				e.preventDefault();
				var target = this;
				var filter = (target.dataset.galleryFilter || target.dataset.filter || 'all').toLowerCase().trim();
				tabs.forEach(function (tab) {
					var isActive = tab === target;
					tab.classList.toggle('is-active', isActive);
					tab.classList.toggle('gallery-tab--active', isActive);
					tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
				});
				applyFilter(gallery, filter, filter === firstFilter || filter === 'all');
			});
		});
	}

	window.VintageSoul.app.register('gallery', init);
})(window, document);
