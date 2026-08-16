
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;
	var events = window.VintageSoul.events;

	function closeAll(except) {
		dom.qsa('[data-vs-dropdown]').forEach(function (dropdown) {
			if (dropdown === except) return;
			setOpen(dropdown, false);
		});
	}

	function setOpen(dropdown, isOpen) {
		var trigger = dom.qs('.dropdown__trigger', dropdown);
		var panel = dom.qs('.dropdown__panel', dropdown);
		if (!trigger || !panel) return;

		panel.hidden = !isOpen;
		trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		dropdown.classList.toggle('is-open', isOpen);
		events.emit(isOpen ? 'dropdown:open' : 'dropdown:close', { dropdown: dropdown });
	}

	function init() {
		dom.on(document, 'click', '.dropdown__trigger', function (event) {
			event.preventDefault();
			var dropdown = this.closest('[data-vs-dropdown]');
			var isOpen = dropdown.classList.contains('is-open');
			closeAll(dropdown);
			setOpen(dropdown, !isOpen);
		});

		document.addEventListener('click', function (event) {
			var dropdown = event.target.closest('[data-vs-dropdown]');
			if (!dropdown) closeAll();
		});

		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape') closeAll();
		});
	}

	window.VintageSoul.dropdown = { closeAll: closeAll };
	window.VintageSoul.app.register('dropdown', init);
})(window, document);
