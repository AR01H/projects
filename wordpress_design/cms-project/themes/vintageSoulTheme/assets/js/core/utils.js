
(function (window) {
	'use strict';

	function debounce(fn, wait) {
		var timer;
		return function () {
			var args = arguments;
			var ctx = this;
			clearTimeout(timer);
			timer = setTimeout(function () {
				fn.apply(ctx, args);
			}, wait);
		};
	}

	function throttle(fn, limit) {
		var waiting = false;
		return function () {
			if (waiting) return;
			fn.apply(this, arguments);
			waiting = true;
			setTimeout(function () {
				waiting = false;
			}, limit);
		};
	}

	window.VintageSoul = window.VintageSoul || {};
	window.VintageSoul.utils = { debounce: debounce, throttle: throttle };
})(window);
