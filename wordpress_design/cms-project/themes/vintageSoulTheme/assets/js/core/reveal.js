
(function (window, document) {
	'use strict';

	var SELECTOR = '.section-header, .banner, .card, .feature-row__item, .gallery__item, .step-chain__item, .process-steps__item, .stats__item, .photo-grid__item, .photo-stamp, .product-list__item, .testimonial-card, .video-testimonial-card, .faq__item, .memories__item, .container > h1, .container > h2, .container > h3, .container > p, .container > .btn, .container > .btn--outline, .container > .badge, .site-footer';

	function init() {
		var items = window.VintageSoul.dom.qsa(SELECTOR);
		if (!items.length) return;

		var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (reduceMotion || !('IntersectionObserver' in window)) {
			items.forEach(function (el) {
				el.classList.add('is-revealed');
			});
			return;
		}

		var counts = new Map();
		items.forEach(function (el) {
			var parent = el.parentElement;
			var n = counts.get(parent) || 0;
			counts.set(parent, n + 1);
			el.style.transitionDelay = (Math.min(n, 5) * 90) + 'ms';
		});

		var observer = new IntersectionObserver(function (entries) {
			entries.forEach(function (entry) {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-revealed');
					observer.unobserve(entry.target);
				}
			});
		}, { threshold: 0.15, rootMargin: '0px 0px -60px 0px' });

		items.forEach(function (el) {
			observer.observe(el);
		});
	}

	window.VintageSoul.app.register('reveal', init);
})(window, document);
