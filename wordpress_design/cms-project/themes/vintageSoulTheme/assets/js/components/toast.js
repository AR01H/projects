
(function (window, document) {
	'use strict';

	var events = window.VintageSoul.events;
	var region = null;

	function getRegion() {
		if (region) return region;
		region = document.createElement('div');
		region.className = 'toast-region';
		region.setAttribute('aria-live', 'polite');
		region.setAttribute('aria-atomic', 'true');
		document.body.appendChild(region);
		return region;
	}

	function show(message, variant, duration) {
		variant = variant || 'info';
		duration = duration === undefined ? 5000 : duration;

		var toast = document.createElement('div');
		toast.className = 'alert alert--' + variant + ' toast';
		toast.setAttribute('role', 'status');

		var content = document.createElement('div');
		content.className = 'alert__content';
		content.textContent = message;
		toast.appendChild(content);

		var dismissBtn = document.createElement('button');
		dismissBtn.className = 'alert__dismiss';
		dismissBtn.setAttribute('aria-label', 'Dismiss');
		dismissBtn.textContent = '×';
		dismissBtn.addEventListener('click', function () {
			remove(toast);
		});
		toast.appendChild(dismissBtn);

		getRegion().appendChild(toast);
		events.emit('toast:show', { message: message, variant: variant });

		if (duration > 0) {
			setTimeout(function () {
				remove(toast);
			}, duration);
		}

		return toast;
	}

	function remove(toast) {
		if (!toast.isConnected) return;
		toast.classList.add('toast--leaving');
		setTimeout(function () {
			toast.remove();
		}, 200);
	}

	window.VintageSoul.toast = { show: show };
	window.VintageSoul.app.register('toast', function () {

	});
})(window, document);
