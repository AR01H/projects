
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;

	function init() {
		dom.qsa('[data-vs-video-carousel]').forEach(function (carousel) {
			var track = dom.qs('.video-testimonial-carousel__track', carousel);
			var prevBtn = dom.qs('.video-testimonial-carousel__arrow--prev', carousel);
			var nextBtn = dom.qs('.video-testimonial-carousel__arrow--next', carousel);
			if (!track) return;

			function scrollByCard(direction) {
				var card = dom.qs('.video-testimonial-card', track);
				var amount = card ? card.getBoundingClientRect().width + 16 : 220;
				var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
				track.scrollBy({ left: direction * amount, behavior: reduceMotion ? 'auto' : 'smooth' });
			}

			if (prevBtn) prevBtn.addEventListener('click', function () { scrollByCard(-1); });
			if (nextBtn) nextBtn.addEventListener('click', function () { scrollByCard(1); });
		});
	}

	window.VintageSoul.app.register('video-testimonial-carousel', init);
})(window, document);
