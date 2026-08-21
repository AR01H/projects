
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;

	function init() {
		dom.qsa('[data-vs-showcase]').forEach(function (showcase) {
			var track = dom.qs('.showcase__track', showcase);
			var prevBtn = dom.qs('.showcase__arrow--prev', showcase);
			var nextBtn = dom.qs('.showcase__arrow--next', showcase);
			if (!track) return;

			function step(direction) {
				var card = dom.qs('.showcase__card', track);
				var gap = parseFloat(window.getComputedStyle(track).columnGap) || 16;
				var amount = card ? card.getBoundingClientRect().width + gap : 300;
				var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
				track.scrollBy({ left: direction * amount, behavior: reduceMotion ? 'auto' : 'smooth' });
			}

			function syncArrows() {
				var max = track.scrollWidth - track.clientWidth - 1;
				if (prevBtn) prevBtn.disabled = track.scrollLeft <= 0;
				if (nextBtn) nextBtn.disabled = track.scrollLeft >= max;
			}

			if (prevBtn) prevBtn.addEventListener('click', function () { step(-1); });
			if (nextBtn) nextBtn.addEventListener('click', function () { step(1); });
			track.addEventListener('scroll', syncArrows, { passive: true });
			window.addEventListener('resize', syncArrows);
			syncArrows();
		});
	}

	window.VintageSoul.app.register('showcase', init);
})(window, document);
