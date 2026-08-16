
(function (window, document) {
	'use strict';

	function init() {
		var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		if (reduceMotion) return;

		var layers = window.VintageSoul.dom.qsa('.hero__media');
		if (!layers.length) return;

		var ticking = false;

		function clamp( value, min, max ) {
			return Math.max( min, Math.min( max, value ) );
		}

		function update() {
			ticking = false;
			layers.forEach(function (el) {
				var section = el.parentElement;
				if (!section) return;
				var rect = section.getBoundingClientRect();
				if (rect.bottom < 0 || rect.top > window.innerHeight) return;
				var offset = clamp(rect.top * 0.18, -50, 50);
				el.style.transform = 'translateY(' + offset + 'px)';
			});
		}

		function onScroll() {
			if (!ticking) {
				ticking = true;
				requestAnimationFrame(update);
			}
		}

		window.addEventListener('scroll', onScroll, { passive: true });
		window.addEventListener('resize', onScroll, { passive: true });
		update();
	}

	window.VintageSoul.app.register('parallax', init);
})(window, document);
