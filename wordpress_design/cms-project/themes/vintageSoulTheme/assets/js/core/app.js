
(function (window, document) {
	'use strict';

	var registry = [];

	function register(name, init) {
		registry.push({ name: name, init: init });
	}

	function boot() {
		registry.forEach(function (entry) {
			try {
				entry.init();
			} catch (error) {
				if (window.console) {
					console.error('[VintageSoul] ' + entry.name + ' failed to init:', error);
				}
			}
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}

	window.VintageSoul = window.VintageSoul || {};
	window.VintageSoul.app = { register: register };
})(window, document);
