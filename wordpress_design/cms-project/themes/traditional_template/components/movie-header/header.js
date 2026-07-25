/**
 * Movie-title header - headline fitting.
 *
 * The board is a fixed shape but the headline is editable content, so a long
 * page title will run into the frame at some viewport width. CSS alone cannot
 * see that collision: clamp() scales with the VIEWPORT, not with how much room
 * this particular string needs inside this particular board.
 *
 * So this measures the rendered headline against its container and shrinks the
 * font a step at a time until it fits. It is the only thing here that needs
 * JavaScript - the board, arch, medallion, gold lettering, curved footer text
 * and ornaments are all CSS and SVG, and header.php already picks a sensible
 * starting size from the string length. With JS off the headline is still
 * correct, just less tightly fitted.
 */
(function () {
	'use strict';

	var MIN_SCALE = 0.52;   // never shrink past this fraction of the CSS size
	var STEP      = 0.04;

	function fit(title) {
		var shell = title.querySelector('.nt-mh__title-shell');
		if (!shell) { return; }

		// Start from a clean slate so a resize can grow the text back up.
		title.style.removeProperty('--nt-mh-fit');
		var scale = 1;

		// Available width is the padded inner box the headline sits in.
		var host = title.parentElement;
		if (!host) { return; }

		function overflows() {
			// Two checks: horizontal overrun, and wrapping to more lines than
			// the string can justify (a very long word forces both).
			return shell.scrollWidth > host.clientWidth + 1;
		}

		while (overflows() && scale > MIN_SCALE) {
			scale -= STEP;
			title.style.setProperty('--nt-mh-fit', scale.toFixed(3));
			title.style.fontSize = 'calc(var(--title-size) * ' + scale.toFixed(3) + ')';
		}
	}

	function fitAll() {
		var titles = document.querySelectorAll('[data-nt-mh-fit]');
		Array.prototype.forEach.call(titles, fit);
	}

	function ready() {
		fitAll();

		// Webfonts land after first paint and change every measurement.
		if (document.fonts && document.fonts.ready && typeof document.fonts.ready.then === 'function') {
			document.fonts.ready.then(fitAll).catch(function () {});
		}

		var t;
		window.addEventListener('resize', function () {
			clearTimeout(t);
			t = setTimeout(fitAll, 150);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', ready);
	} else {
		ready();
	}
}());
