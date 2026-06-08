/* ==========================================================================
   carousel_video_scroll — slider behaviour (dots, arrows, swipe, YouTube facade).
   Initialises every .ch-vs on the page; reads config from data- attributes.
   Markup: components/carousels/carousel_video_scroll.php
   ========================================================================== */
(function () {
	'use strict';

	function initVideoScroll(root) {
		if (root.dataset.vsReady === '1') return;   // guard against double-init
		root.dataset.vsReady = '1';

		var track  = root.querySelector('.ch-vs-track');
		var vp     = root.querySelector('.ch-vs-viewport');
		if (!track || !vp) return;

		var slides = Array.prototype.slice.call(track.querySelectorAll('.ch-vs-slide'));
		var dots   = Array.prototype.slice.call(root.querySelectorAll('.ch-vs-dot'));
		var total  = slides.length;
		if (!total) return;

		var loop     = root.dataset.loop === '1';
		var autoplay = parseInt(root.dataset.autoplay || '0', 10);
		var index    = 0;
		var timer    = null;

		function stopMedia() {
			/* Collapse any open player (YouTube iframe OR <video>) back to its facade */
			track.querySelectorAll('.ch-vs-media[data-yt-open="1"]').forEach(function (media) {
				var btn   = media.querySelector('.ch-vs-yt');
				var frame = media.querySelector('iframe, video');
				if (frame) { if (frame.tagName === 'VIDEO') { try { frame.pause(); } catch (e) {} } frame.remove(); }
				if (btn) btn.style.display = '';
				media.removeAttribute('data-yt-open');
			});
		}

		function go(i) {
			if (loop) { i = (i + total) % total; }
			else      { i = Math.max(0, Math.min(total - 1, i)); }
			stopMedia();
			index = i;
			track.style.transform = 'translateX(' + (-index * 100) + '%)';
			slides.forEach(function (s, n) { s.classList.toggle('is-active', n === index); });
			dots.forEach(function (d, n) {
				d.classList.toggle('is-active', n === index);
				d.setAttribute('aria-selected', n === index ? 'true' : 'false');
			});
		}

		function startAuto() { if (autoplay > 0 && total > 1) timer = setInterval(function () { go(index + 1); }, autoplay); }
		function stopAuto()  { if (timer) { clearInterval(timer); timer = null; } }
		function restart()   { stopAuto(); startAuto(); }

		/* Arrows */
		var prev = root.querySelector('.ch-vs-arrow--prev');
		var next = root.querySelector('.ch-vs-arrow--next');
		if (prev) prev.addEventListener('click', function () { go(index - 1); restart(); });
		if (next) next.addEventListener('click', function () { go(index + 1); restart(); });

		/* Dots */
		dots.forEach(function (d) {
			d.addEventListener('click', function () { go(parseInt(d.dataset.go, 10) || 0); restart(); });
		});

		/* Keyboard when the carousel has focus */
		root.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowLeft')  { go(index - 1); restart(); }
			if (e.key === 'ArrowRight') { go(index + 1); restart(); }
		});

		/* Play facade → load the player (YouTube iframe or <video>) on click/keypress */
		function playFacade(btn) {
			var media = btn.closest('.ch-vs-media');
			if (!media || media.getAttribute('data-yt-open') === '1') return;

			var type = btn.dataset.type;
			var frame;
			if (type === 'video') {
				if (!btn.dataset.src) return;
				frame = document.createElement('video');
				frame.src = btn.dataset.src;
				frame.controls = true; frame.autoplay = true; frame.playsInline = true;
			} else {
				if (!btn.dataset.yt) return;
				frame = document.createElement('iframe');
				frame.src = 'https://www.youtube.com/embed/' + btn.dataset.yt + '?autoplay=1&rel=0&modestbranding=1';
				frame.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
				frame.setAttribute('allowfullscreen', '');
			}
			btn.style.display = 'none';
			media.appendChild(frame);
			media.setAttribute('data-yt-open', '1');
			stopAuto(); /* don't slide away from a playing video */
		}

		track.querySelectorAll('.ch-vs-yt').forEach(function (btn) {
			btn.addEventListener('click', function () { playFacade(btn); });
			btn.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') { e.preventDefault(); playFacade(btn); }
			});
		});

		/* Touch swipe */
		var startX = 0, dragging = false;
		vp.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; dragging = true; }, { passive: true });
		vp.addEventListener('touchend', function (e) {
			if (!dragging) return;
			dragging = false;
			var dx = e.changedTouches[0].clientX - startX;
			if (Math.abs(dx) > 40) { dx < 0 ? go(index + 1) : go(index - 1); restart(); }
		}, { passive: true });

		/* Pause autoplay on hover */
		root.addEventListener('mouseenter', stopAuto);
		root.addEventListener('mouseleave', startAuto);

		go(0);
		startAuto();
	}

	function boot() {
		document.querySelectorAll('.ch-vs').forEach(initVideoScroll);
	}

	if (document.readyState !== 'loading') boot();
	else document.addEventListener('DOMContentLoaded', boot);
})();
