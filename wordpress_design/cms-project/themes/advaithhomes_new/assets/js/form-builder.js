/**
 * Form Builder - AJAX submit for .adn-form components.
 *
 * Any form rendered by components/form_builder/form_builder.php carries a
 * data-endpoint attribute; this script intercepts its submit, POSTs the
 * fields as JSON, and shows the response inline. Without JS the form still
 * performs a normal POST to the same endpoint.
 */
(function () {
	'use strict';

	function showMessage(form, text, isError) {
		var box = form.querySelector('.adn-form__msg');
		if (!box) return;
		box.textContent = text;
		box.hidden = false;
		box.classList.toggle('adn-form__msg--error', !!isError);
		box.classList.toggle('adn-form__msg--success', !isError);
	}

	function onSubmit(event) {
		var form = event.target;
		if (!form.classList.contains('adn-form') || !form.dataset.endpoint) return;
		event.preventDefault();

		// Honeypot filled → quietly do nothing (bot).
		var hp = form.querySelector('[name="adn_hp"]');
		if (hp && hp.value !== '') return;

		var submitBtn = form.querySelector('.adn-form__submit');
		var data = {};
		new FormData(form).forEach(function (value, key) {
			if (key === '_wpnonce' || key === 'adn_hp') return;
			data[key] = value;
		});

		if (submitBtn) {
			submitBtn.disabled = true;
			submitBtn.dataset.label = submitBtn.textContent;
			submitBtn.textContent = '…';
		}

		var nonceField = form.querySelector('[name="_wpnonce"]');
		var headers = { 'Content-Type': 'application/json' };
		if (nonceField && nonceField.value) headers['X-WP-Nonce'] = nonceField.value;

		fetch(form.dataset.endpoint, {
			method: 'POST',
			headers: headers,
			body: JSON.stringify(data)
		})
			.then(function (response) {
				return response.json().then(function (body) {
					return { ok: response.ok, body: body };
				});
			})
			.then(function (result) {
				if (result.ok && result.body && result.body.success !== false) {
					showMessage(form, (result.body && result.body.message) || form.dataset.success || 'Done.', false);
					form.reset();
				} else {
					showMessage(form, (result.body && (result.body.error || result.body.message)) || 'Something went wrong. Please try again.', true);
				}
			})
			.catch(function () {
				showMessage(form, 'Network error. Please try again.', true);
			})
			.finally(function () {
				if (submitBtn) {
					submitBtn.disabled = false;
					submitBtn.textContent = submitBtn.dataset.label || 'Send';
				}
			});
	}

	document.addEventListener('submit', onSubmit);
})();
