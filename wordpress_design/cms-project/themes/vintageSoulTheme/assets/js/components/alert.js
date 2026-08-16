
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;
	var events = window.VintageSoul.events;

	function dismiss(alert) {
		events.emit('alert:dismiss', { id: alert.id || null });
		alert.remove();
	}

	function init() {
		dom.on(document, 'click', '[data-vs-alert-dismiss]', function () {
			var alert = this.closest('[data-vs-alert]');
			if (alert) dismiss(alert);
		});

		dom.qsa('[data-vs-alert-autodismiss]').forEach(function (alert) {
			var delay = parseInt(alert.getAttribute('data-vs-alert-autodismiss'), 10);
			if (delay > 0) {
				setTimeout(function () {
					dismiss(alert);
				}, delay);
			}
		});
	}

	function create(options) {
		options = options || {};
		var variant = options.variant || 'info';
		var dismissible = options.dismissible !== false;

		var alert = document.createElement('div');
		alert.className = 'alert alert--' + variant;
		alert.setAttribute('role', 'alert');
		alert.setAttribute('data-vs-alert', '');
		if (options.autodismiss) {
			alert.setAttribute('data-vs-alert-autodismiss', String(options.autodismiss));
		}

		if (options.icon) {
			var icon = document.createElement('span');
			icon.className = 'alert__icon';
			icon.setAttribute('aria-hidden', 'true');
			icon.textContent = options.icon;
			alert.appendChild(icon);
		}

		var content = document.createElement('div');
		content.className = 'alert__content';
		content.textContent = options.message || '';
		alert.appendChild(content);

		if (dismissible) {
			var dismissBtn = document.createElement('button');
			dismissBtn.type = 'button';
			dismissBtn.className = 'alert__dismiss';
			dismissBtn.setAttribute('aria-label', 'Dismiss');
			dismissBtn.innerHTML = '&times;';
			alert.appendChild(dismissBtn);
		}

		if (options.target) {
			var target = typeof options.target === 'string' ? document.querySelector(options.target) : options.target;
			if (target) target.appendChild(alert);
		}

		if (options.autodismiss > 0) {
			setTimeout(function () {
				dismiss(alert);
			}, options.autodismiss);
		}

		return alert;
	}

	window.VintageSoul.alert = { dismiss: dismiss, create: create };
	window.VintageSoul.app.register('alert', init);
})(window, document);
