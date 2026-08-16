
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;
	var events = window.VintageSoul.events;

	function init() {
		dom.qsa('[data-vs-booking-wizard]').forEach(function (form) {
			var wizard = form.closest('.booking-wizard');
			var panels = dom.qsa('[data-wizard-panel]', form);
			var indicatorItems = dom.qsa('.booking-wizard__step', wizard);
			var summaryList = dom.qs('[data-wizard-summary]', form);
			if (!panels.length) return;

			var index = 0;

			function showStep(next) {
				index = Math.max(0, Math.min(panels.length - 1, next));

				panels.forEach(function (panel, i) {
					var active = i === index;
					panel.classList.toggle('is-active', active);
					panel.inert = !active;
				});

				indicatorItems.forEach(function (item, i) {
					item.classList.toggle('is-active', i === index);
					item.classList.toggle('is-done', i < index);
				});

				if (index === panels.length - 1) buildSummary();

				var panel = form.closest('.dialog__panel');
				if (panel) panel.scrollTop = 0;
			}

			function buildSummary() {
				if (!summaryList) return;
				var caneInput = dom.qs('input[name="cane_type"]:checked', form);
				var flavourInputs = dom.qsa('input[name="flavours[]"]:checked', form);
				var flavours = flavourInputs.map(function (input) {
					var nameEl = dom.qs('.booking-wizard__option-name', input.nextElementSibling);
					return nameEl ? nameEl.textContent : input.value;
				});
				var caneNameEl = caneInput ? dom.qs('.booking-wizard__option-name', caneInput.nextElementSibling) : null;

				var rows = [
					['Cane Type', caneNameEl ? caneNameEl.textContent : ''],
					['Flavours', flavours.length ? flavours.join(', ') : '-'],
					['Event Type', fieldValue('event_type')],
					['Date', fieldValue('event_date')],
					['Location', fieldValue('location')],
					['Guests', fieldValue('guest_count')]
				];

				summaryList.innerHTML = '';
				rows.forEach(function (row) {
					if (!row[1]) return;
					var dt = document.createElement('dt');
					dt.textContent = row[0];
					var dd = document.createElement('dd');
					dd.textContent = row[1];
					summaryList.appendChild(dt);
					summaryList.appendChild(dd);
				});
			}

			function fieldValue(name) {
				var field = form.elements[name];
				return field ? field.value : '';
			}

			dom.on(form, 'click', '[data-wizard-next]', function () {
				showStep(index + 1);
			});
			dom.on(form, 'click', '[data-wizard-back]', function () {
				showStep(index - 1);
			});

			form.addEventListener('submit', function (event) {
				event.preventDefault();
				var data = {};
				new window.FormData(form).forEach(function (value, key) {
					if (key.slice(-2) === '[]') {
						var cleanKey = key.slice(0, -2);
						data[cleanKey] = data[cleanKey] || [];
						data[cleanKey].push(value);
					} else {
						data[key] = value;
					}
				});
				events.emit('booking-wizard:submit', { id: wizard ? wizard.id : '', data: data });
			});

			events.on('dialog:open', function (detail) {
				if (wizard && detail.id === wizard.id) showStep(0);
			});

			showStep(0);
		});
	}

	window.VintageSoul.app.register('booking-wizard', init);
})(window, document);
