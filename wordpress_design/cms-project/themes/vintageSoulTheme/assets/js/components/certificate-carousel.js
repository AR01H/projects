
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;

	function init() {
		dom.qsa('[data-vs-certificate-carousel]').forEach(function (carousel) {
			var track = dom.qs('.certificate-carousel__track', carousel);
			var prevBtn = dom.qs('.certificate-carousel__arrow--prev', carousel);
			var nextBtn = dom.qs('.certificate-carousel__arrow--next', carousel);
			if (!track) return;

			function scrollByCard(direction) {
				var card = dom.qs('.certificate-card', track);
				var amount = card ? card.getBoundingClientRect().width + 16 : 280;
				var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
				track.scrollBy({ left: direction * amount, behavior: reduceMotion ? 'auto' : 'smooth' });
			}

			if (prevBtn) prevBtn.addEventListener('click', function () { scrollByCard(-1); });
			if (nextBtn) nextBtn.addEventListener('click', function () { scrollByCard(1); });
		});
	}

	window.VintageSoul.app.register('certificate-carousel', init);
})(window, document);
