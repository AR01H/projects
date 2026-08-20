
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;
	var events = window.VintageSoul.events;
	var FOCUSABLE = 'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])';

	var toggle = null;
	var scrim = null;
	var drawer = null;
	var lastFocused = null;

	function setSubmenuOpen(item, isOpen) {
		var btn = dom.qs('.mobile-nav__toggle', item);
		var sub = dom.qs('.mobile-nav__submenu', item);
		item.classList.toggle('is-open', isOpen);
		if (btn) btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		if (sub) sub.inert = !isOpen;
	}

	function closeAllSubmenus() {
		dom.qsa('.mobile-nav__item.is-open', drawer).forEach(function (item) {
			setSubmenuOpen(item, false);
		});
	}

	function trapFocus(event) {
		var focusable = dom.qsa(FOCUSABLE, drawer);
		if (!focusable.length) return;
		var first = focusable[0];
		var last = focusable[focusable.length - 1];

		if (event.shiftKey && document.activeElement === first) {
			event.preventDefault();
			last.focus();
		} else if (!event.shiftKey && document.activeElement === last) {
			event.preventDefault();
			first.focus();
		}
	}

	function isOpen() {
		return drawer.classList.contains('is-open');
	}

	function open() {
		lastFocused = document.activeElement;
		drawer.inert = false;
		drawer.classList.add('is-open');
		scrim.classList.add('is-open');
		toggle.setAttribute('aria-expanded', 'true');
		toggle.setAttribute('aria-label', toggle.dataset.labelClose || 'Close menu');
		document.body.style.overflow = 'hidden';
		document.body.classList.add('has-open-drawer');

		var focusable = dom.qsa(FOCUSABLE, drawer);
		if (focusable.length) focusable[0].focus();

		events.emit('mobile-nav:open', {});
	}

	function close() {
		drawer.classList.remove('is-open');
		scrim.classList.remove('is-open');
		toggle.setAttribute('aria-expanded', 'false');
		toggle.setAttribute('aria-label', toggle.dataset.labelOpen || 'Open menu');
		drawer.inert = true;
		document.body.style.overflow = '';
		document.body.classList.remove('has-open-drawer');
		closeAllSubmenus();

		if (lastFocused && typeof lastFocused.focus === 'function') {
			lastFocused.focus();
		}
		events.emit('mobile-nav:close', {});
	}

	function init() {
		toggle = document.getElementById('mobile-nav-toggle');
		scrim = document.getElementById('mobile-nav-scrim');
		drawer = document.getElementById('mobile-nav');
		if (!toggle || !scrim || !drawer) return;

		dom.qsa('.mobile-nav__submenu', drawer).forEach(function (sub) {
			sub.inert = true;
		});

		toggle.addEventListener('click', function () {
			if (isOpen()) close(); else open();
		});
		scrim.addEventListener('click', close);

		dom.on(drawer, 'click', 'a[href]', function () {
			close();
		});

		dom.on(drawer, 'click', '.mobile-nav__toggle', function () {
			var item = this.closest('.mobile-nav__item');
			if (!item) return;
			var wasOpen = item.classList.contains('is-open');
			closeAllSubmenus();
			if (!wasOpen) setSubmenuOpen(item, true);
		});

		document.addEventListener('keydown', function (event) {
			if (!isOpen()) return;
			if (event.key === 'Escape') {
				close();
			} else if (event.key === 'Tab') {
				trapFocus(event);
			}
		});
	}

	window.VintageSoul.mobileNav = { open: open, close: close };
	window.VintageSoul.app.register('mobile-nav', init);
})(window, document);
