/**
 * VintageSoulTheme - "Featured In" Logo Strip Marquee
 *
 * Auto-scrolls continuously. Previously this just paused on hover (CSS
 * `animation-play-state: paused`) with no way to move it manually - now
 * hovering + scrolling the mouse wheel nudges the strip by hand, and
 * autoplay resumes the moment the pointer leaves.
 */
(function (window, document) {
	'use strict';

	function init() {
		var wrapper = document.querySelector('.logo-strip-vintage');
		var track = wrapper ? wrapper.querySelector('.logo-strip-vintage__track') : null;
		if (!wrapper || !track) return;

		// Content is rendered 3x (see components/sections/logo-strip-section.php)
		// so the loop can wrap seamlessly - one third of the track's width is
		// exactly one full loop.
		var loopWidth = track.scrollWidth / 3;
		if (!loopWidth || !isFinite(loopWidth)) return;

		var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var AUTOPLAY_PX_PER_SEC = 32;

		var offset = 0;
		var isHovering = false;
		var lastTime = null;

		function wrap() {
			// Keep the offset bounded to one loop's width either direction so
			// it never grows without limit, whichever way the wheel nudges it.
			while (offset <= -loopWidth) offset += loopWidth;
			while (offset > 0) offset -= loopWidth;
		}

		function apply() {
			track.style.transform = 'translateX(' + offset + 'px)';
		}

		function tick(time) {
			if (lastTime === null) lastTime = time;
			var dt = (time - lastTime) / 1000;
			lastTime = time;

			if (!isHovering && !reduceMotion) {
				offset -= AUTOPLAY_PX_PER_SEC * dt;
				wrap();
				apply();
			}

			requestAnimationFrame(tick);
		}

		wrapper.addEventListener('mouseenter', function () {
			isHovering = true;
		});
		wrapper.addEventListener('mouseleave', function () {
			isHovering = false;
			lastTime = null;
		});

		wrapper.addEventListener('wheel', function (e) {
			offset -= e.deltaY;
			wrap();
			apply();
			e.preventDefault();
		}, { passive: false });

		apply();
		requestAnimationFrame(tick);
	}

	if (window.VintageSoul && window.VintageSoul.app && typeof window.VintageSoul.app.register === 'function') {
		window.VintageSoul.app.register('logo-strip-marquee', init);
	} else if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})(window, document);
