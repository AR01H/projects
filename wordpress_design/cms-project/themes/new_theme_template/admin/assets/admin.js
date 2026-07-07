/**
 * Theme admin panel JS (loaded only on the Theme page).
 * wp.media is already enqueued by the engine - this wires any
 * .nt-media-picker button to a media-library chooser that fills the
 * input named in data-target with the selected attachment URL.
 */
(function () {
	'use strict';

	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.nt-media-picker');
		if (!btn || typeof wp === 'undefined' || !wp.media) { return; }
		e.preventDefault();

		var targetSel = btn.getAttribute('data-target');
		var input = targetSel ? document.querySelector(targetSel) : null;
		if (!input) { return; }

		var frame = wp.media({ title: 'Select image', multiple: false, library: { type: 'image' } });
		frame.on('select', function () {
			var att = frame.state().get('selection').first().toJSON();
			input.value = att.url;
			input.dispatchEvent(new Event('change', { bubbles: true }));
		});
		frame.open();
	});
}());
