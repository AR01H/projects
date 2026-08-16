
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;

	function init() {
		dom.on(document, 'click', '[data-el-toast]', function () {
			var variant = this.getAttribute('data-el-toast');
			window.VintageSoul.toast.show('This is a ' + variant + ' toast.', variant);
		});

		dom.on(document, 'click', '#el-alert-dynamic', function () {
			window.VintageSoul.alert.create({
				message: 'Created dynamically via VintageSoul.alert.create().',
				variant: 'success',
				target: this.parentElement,
			});
		});

		dom.on(document, 'click', '#el-dialog-dynamic', function () {
			window.VintageSoul.dialog.create({
				title: 'Created dynamically',
				body: 'Built entirely via VintageSoul.dialog.create(), no PHP markup involved.',
				buttons: [
					{ label: 'Cancel', close: true },
					{ label: 'Confirm', variant: 'primary', close: true },
				],
			});
		});
	}

	window.VintageSoul.app.register('page-elements', init);
})(window, document);
