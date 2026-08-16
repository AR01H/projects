
(function (window) {
	'use strict';

	var listeners = {};

	function on(name, handler) {
		(listeners[name] = listeners[name] || []).push(handler);
	}

	function off(name, handler) {
		if (!listeners[name]) return;
		listeners[name] = listeners[name].filter(function (h) {
			return h !== handler;
		});
	}

	function emit(name, detail) {
		(listeners[name] || []).forEach(function (handler) {
			handler(detail);
		});
	}

	window.VintageSoul = window.VintageSoul || {};
	window.VintageSoul.events = { on: on, off: off, emit: emit };
})(window);
