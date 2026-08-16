
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;

	function init() {
		var form = dom.qs('[data-vs-contact-form]');
		if (!form) return;

		form.addEventListener('submit', function (event) {
			var invalid = dom.qsa('[required]', form).filter(function (field) {
				return !field.value.trim();
			});

			invalid.forEach(function (field) {
				field.classList.add('is-invalid');
			});

			if (invalid.length) {
				event.preventDefault();
				window.VintageSoul.alert.create({
					message: 'Please fill in all required fields.',
					variant: 'warning',
					target: form
				});
			}
		});

		form.addEventListener('input', function (event) {
			event.target.classList.remove('is-invalid');
		});
	}

	window.VintageSoul.app.register('page-contact', init);
})(window, document);
