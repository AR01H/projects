
(function (window, document) {
	'use strict';

	function qs(selector, scope) {
		return (scope || document).querySelector(selector);
	}

	function qsa(selector, scope) {
		return Array.prototype.slice.call((scope || document).querySelectorAll(selector));
	}

	function on(el, type, selectorOrHandler, maybeHandler) {
		if (typeof selectorOrHandler === 'function') {
			el.addEventListener(type, selectorOrHandler);
			return;
		}

		el.addEventListener(type, function (event) {
			var target = event.target.closest(selectorOrHandler);
			if (target && el.contains(target)) {
				maybeHandler.call(target, event);
			}
		});
	}

	window.VintageSoul = window.VintageSoul || {};
	window.VintageSoul.dom = { qs: qs, qsa: qsa, on: on };
})(window, document);
