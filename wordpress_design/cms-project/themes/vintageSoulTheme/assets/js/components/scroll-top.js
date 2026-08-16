
(function (window, document) {
	'use strict';

	var THRESHOLD = 400;

	function init() {
		var btn = document.getElementById('scroll-top');
		if (!btn) return;

		function update() {
			btn.classList.toggle('is-visible', window.scrollY > THRESHOLD);
		}
		window.addEventListener('scroll', update, { passive: true });
		update();

		btn.addEventListener('click', function () {
			var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
		});
	}

	window.VintageSoul.app.register('scroll-top', init);
})(window, document);
