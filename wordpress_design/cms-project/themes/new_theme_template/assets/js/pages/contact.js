/**
 * assets/js/pages/contact.js - Contact form -> NT.ajax('contact_submit').
 * Loaded only on the contact page (declared in config/pages.php).
 */
(function () {
	'use strict';

	var form = document.querySelector('[data-nt-contact-form]');
	if (!form || !window.NT) { return; }
	var status = form.querySelector('[data-nt-form-status]');
	var button = form.querySelector('button[type="submit"]');

	function say(message, ok) {
		if (status) {
			status.textContent = message;
			status.classList.toggle('is-error', !ok);
		}
	}

	form.addEventListener('submit', function (e) {
		e.preventDefault();
		var data = {
			name: (form.name && form.name.value) || '',
			email: (form.email && form.email.value) || '',
			phone: (form.phone && form.phone.value) || '',
			message: (form.message && form.message.value) || ''
		};
		if (button) { button.disabled = true; }
		say('Sending...', true);

		NT.ajax('contact_submit', data).then(function (res) {
			var msg = (res && res.data && res.data.message) || 'Done.';
			say(msg, !!(res && res.success));
			if (res && res.success) { form.reset(); }
		}).catch(function () {
			say('Network error. Please try again.', false);
		}).finally(function () {
			if (button) { button.disabled = false; }
		});
	});
}());
