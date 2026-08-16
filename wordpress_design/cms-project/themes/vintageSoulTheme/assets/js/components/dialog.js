
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;
	var events = window.VintageSoul.events;
	var FOCUSABLE = 'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])';

	var openDialogs = [];

	function getDialog(id) {
		return document.getElementById(id);
	}

	function trapFocus(panel, event) {
		var focusable = dom.qsa(FOCUSABLE, panel);
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

	function open(id) {
		var dialog = getDialog(id);
		if (!dialog) return;

		dialog.hidden = false;
		dialog.dataset.vsLastFocus = 'true';
		dialog._lastFocused = document.activeElement;
		openDialogs.push(dialog);

		document.body.style.overflow = 'hidden';

		var panel = dom.qs('.dialog__panel', dialog);
		var focusable = dom.qsa(FOCUSABLE, panel);
		if (focusable.length) focusable[0].focus();

		events.emit('dialog:open', { id: id });
	}

	function close(id) {
		var dialog = getDialog(id);
		if (!dialog) return;

		dialog.hidden = true;
		openDialogs = openDialogs.filter(function (d) {
			return d !== dialog;
		});
		if (!openDialogs.length) {
			document.body.style.overflow = '';
		}
		if (dialog._lastFocused && typeof dialog._lastFocused.focus === 'function') {
			dialog._lastFocused.focus();
		}

		events.emit('dialog:close', { id: id });
	}

	function init() {
		dom.on(document, 'click', '[data-vs-dialog-open]', function (event) {
			event.preventDefault();
			open(this.getAttribute('data-vs-dialog-open'));
		});

		dom.on(document, 'click', '[data-vs-dialog-close]', function () {
			var dialog = this.closest('[data-vs-dialog]');
			if (dialog) close(dialog.id);
		});

		document.addEventListener('keydown', function (event) {
			if (!openDialogs.length) return;
			var current = openDialogs[openDialogs.length - 1];

			if (event.key === 'Escape') {
				close(current.id);
			} else if (event.key === 'Tab') {
				trapFocus(dom.qs('.dialog__panel', current), event);
			}
		});
	}

	var dynamicCount = 0;

	function create(options) {
		options = options || {};
		var id = 'dialog-dynamic-' + (++dynamicCount);

		var dialog = document.createElement('div');
		dialog.className = 'dialog' + (options.fullscreen ? ' dialog--fullscreen' : '');
		dialog.id = id;
		dialog.setAttribute('data-vs-dialog', '');
		dialog.hidden = true;

		var backdrop = document.createElement('div');
		backdrop.className = 'dialog__backdrop';
		backdrop.setAttribute('data-vs-dialog-close', '');
		dialog.appendChild(backdrop);

		var panel = document.createElement('div');
		panel.className = 'dialog__panel';
		panel.setAttribute('role', 'dialog');
		panel.setAttribute('aria-modal', 'true');
		panel.setAttribute('aria-labelledby', id + '-title');
		dialog.appendChild(panel);

		var header = document.createElement('header');
		header.className = 'dialog__header';
		var title = document.createElement('h2');
		title.className = 'dialog__title';
		title.id = id + '-title';
		title.textContent = options.title || '';
		header.appendChild(title);
		var closeBtn = document.createElement('button');
		closeBtn.type = 'button';
		closeBtn.className = 'dialog__close';
		closeBtn.setAttribute('aria-label', 'Close');
		closeBtn.innerHTML = '&times;';
		closeBtn.setAttribute('data-vs-dialog-close', '');
		header.appendChild(closeBtn);
		panel.appendChild(header);

		if (options.body) {
			var body = document.createElement('div');
			body.className = 'dialog__body';
			body.textContent = options.body;
			panel.appendChild(body);
		}

		if (options.buttons && options.buttons.length) {
			var footer = document.createElement('footer');
			footer.className = 'dialog__footer';
			options.buttons.forEach(function (btnOptions) {
				var btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'btn' + (btnOptions.variant ? ' btn--' + btnOptions.variant : '');
				btn.textContent = btnOptions.label;
				btn.addEventListener('click', function (event) {
					if (typeof btnOptions.onClick === 'function') {
						btnOptions.onClick(event);
					}
					if (btnOptions.close !== false) {
						close(id);
					}
				});
				footer.appendChild(btn);
			});
			panel.appendChild(footer);
		}

		document.body.appendChild(dialog);

		var handleClose = function (detail) {
			if (detail.id !== id) return;
			window.VintageSoul.events.off('dialog:close', handleClose);
			dialog.remove();
		};
		window.VintageSoul.events.on('dialog:close', handleClose);

		open(id);
		return id;
	}

	window.VintageSoul.dialog = { open: open, close: close, create: create };
	window.VintageSoul.app.register('dialog', init);
})(window, document);
