/**
 * Movie-title header - headline fitting.
 *
 * The board is a fixed shape but the headline is editable content, so a long
 * page title will run into the frame. CSS alone cannot see that collision:
 * clamp() scales with the VIEWPORT, not with how much room this particular
 * string needs on this particular board. header.php picks a sensible starting
 * size from the string length; this corrects it against what actually
 * rendered.
 *
 * WHAT THIS USED TO DO, AND WHY IT STOPPED WORKING
 * It measured an element called `.nt-mh__title-shell` - the HTML span the
 * headline lived in before the title became SVG <textPath>. That element has
 * not existed since, so the function returned on its first line and no
 * headline has been fitted since the rewrite. On a phone that showed as
 * "HELP & ANSWERS" rendering as "ELP & ANSWER", with the first and last
 * letters outside the board.
 *
 * It now measures the SVG text itself. The headline sits on a curve inside a
 * 1200-unit viewBox, so the measurement and the correction are both in
 * viewBox units - no reading of CSS pixels, and no dependence on how the SVG
 * happens to be scaled into the page at this width.
 *
 * With JS off the headline is still correct, just less tightly fitted.
 */
(function () {
	'use strict';

	var MIN_SCALE = 0.42;   // never shrink past this fraction of the chosen size
	var STEP      = 0.045;
	var SAFE      = 0.86;   // of the viewBox width - leaves the frame its margin

	function fit(title) {
		var svg = title.querySelector('.nt-mh__title-svg');
		if (!svg) { return; }

		var group = svg.querySelector('.nt-mh__type');
		if (!group) { return; }

		// The face is the copy whose width matters; the extrusion copies sit
		// underneath it and are a few units fatter by design.
		var face = group.querySelector('.nt-mh__type-face');
		if (!face) { return; }

		var base = parseFloat(group.getAttribute('data-nt-mh-base'))
			|| parseFloat(svg.getAttribute('data-nt-mh-size'))
			|| parseFloat(group.getAttribute('font-size'))
			|| 96;

		// Remember the starting size so a resize can grow the text back up
		// rather than only ever shrinking it further.
		if (!group.getAttribute('data-nt-mh-base')) {
			group.setAttribute('data-nt-mh-base', String(base));
		}

		var viewBox = (svg.getAttribute('viewBox') || '0 0 1200 330').split(/\s+/);
		var limit = (parseFloat(viewBox[2]) || 1200) * SAFE;

		var scale = 1;
		group.setAttribute('font-size', String(base));

		function tooWide() {
			// getComputedTextLength measures the glyphs on the path, which is
			// what actually has to fit - a bounding box would include the
			// stroke and over-report.
			try {
				return face.getComputedTextLength() > limit;
			} catch (e) {
				return false;   // not rendered yet; try again after fonts load
			}
		}

		while (tooWide() && scale > MIN_SCALE) {
			scale -= STEP;
			group.setAttribute('font-size', String(base * scale));
		}
	}

	function fitAll() {
		var titles = document.querySelectorAll('[data-nt-mh-fit]');
		Array.prototype.forEach.call(titles, fit);
	}

	function ready() {
		fitAll();

		// Webfonts land after first paint and change every measurement - the
		// display serif is much wider than the fallback it replaces.
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
