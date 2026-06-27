/* main.js - Site-wide JavaScript */

/* ── Nav dropdown viewport clamp ─────────────────────────────── */
(function () {
	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.nav-item.has-dropdown').forEach(function (item) {
			item.addEventListener('mouseenter', function () {
				var dd = item.querySelector('.nav-dropdown');
				if (!dd) return;
				// Reset to CSS default (left:0) so measurement is accurate.
				dd.style.left  = '';
				dd.style.right = '';
				var ddRect   = dd.getBoundingClientRect();
				var itemRect = item.getBoundingClientRect();
				var vw  = window.innerWidth;
				var pad = 8;
				var shift = 0;
				// Shift left if overflowing right edge.
				if (ddRect.right > vw - pad) {
					shift = (vw - pad) - ddRect.right;
				}
				// Clamp so left edge doesn't go off-screen either.
				if (itemRect.left + shift < pad) {
					shift = pad - itemRect.left;
				}
				if (shift !== 0) {
					dd.style.left  = shift + 'px';
					dd.style.right = 'auto';
				}
			});
		});
	});
}());

/* ── Page Loader ─────────────────────────────────────────────── */
(function () {
	var loader = document.getElementById('adn-page-loader');
	if (!loader) return;

	var _hidden = false;
	function hideLoader() {
		if (_hidden) return;
		_hidden = true;
		loader.classList.add('adn-loader-done');
		setTimeout(function () {
			if (loader.parentNode) { loader.parentNode.removeChild(loader); }
		}, 400);
	}

	// Hide as soon as DOM is ready - covers all cases.
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', hideLoader);
	} else {
		// DOMContentLoaded already fired (script is in footer).
		hideLoader();
	}
	// Hard fallback - should never reach this.
	setTimeout(hideLoader, 3000);
}());


/* Pre-header marquee: activate scroll if text is wider than viewport */
(function () {
    var bar = document.getElementById('adn-preheader');
    var txt = document.getElementById('adn-preheader-text');
    if (!bar || !txt) return;
    if (txt.scrollWidth > bar.clientWidth) {
        var speed = Math.max(10, txt.scrollWidth / 80); // px-per-second -> seconds
        txt.style.animationDuration = speed + 's';
        bar.classList.add('is-marquee');
    }
}());