/* ==========================================================================
   carousel_mini_video_scroll - shelf scrolling + YouTube/video play facade.
   Initialises every .ah-mvs on the page.
   Markup: components/carousels/carousel_mini_video_scroll.php
   ========================================================================== */
(function () {
	'use strict';

	function initMiniVideo(root) {
		if (root.dataset.mvsReady === '1') return;   // guard double-init
		root.dataset.mvsReady = '1';

		var track = root.querySelector('.ah-mvs-track');
		if (!track) return;
		var prev = root.querySelector('.ah-mvs-arrow--prev');
		var next = root.querySelector('.ah-mvs-arrow--next');

		/* ── Scrolling ──────────────────────────────────────────────────── */
		function pageAmount() { return Math.max(200, Math.round(track.clientWidth * 0.85)); }

		function updateArrows() {
			var max = track.scrollWidth - track.clientWidth - 2;
			if (prev) prev.classList.toggle('is-disabled', track.scrollLeft <= 0);
			if (next) next.classList.toggle('is-disabled', track.scrollLeft >= max);
		}

		if (prev) prev.addEventListener('click', function () { track.scrollBy({ left: -pageAmount(), behavior: 'smooth' }); });
		if (next) next.addEventListener('click', function () { track.scrollBy({ left:  pageAmount(), behavior: 'smooth' }); });
		track.addEventListener('scroll', updateArrows, { passive: true });
		window.addEventListener('resize', updateArrows);
		updateArrows();

		/* ── Play facade ────────────────────────────────────────────────── */

		function stopThumb(thumb) {
			var frame = thumb.querySelector('.ah-mvs-frame');
			if (frame) {
				if (frame.tagName === 'VIDEO') { try { frame.pause(); } catch (e) {} }
				frame.remove();
			}
			thumb.classList.remove('is-playing');
		}

		function stopOthers(except) {
			track.querySelectorAll('.ah-mvs-play-btn.is-playing').forEach(function (t) {
				if (t !== except) stopThumb(t);
			});
		}

		function playInto(thumb) {
			if (thumb.classList.contains('is-playing')) return;
			stopOthers(thumb);

			var type = thumb.dataset.type, id = thumb.dataset.yt, src = thumb.dataset.src;
			var frame;

			if (type === 'youtube' && id) {
				frame = document.createElement('iframe');
				frame.src = 'https://www.youtube.com/embed/' + id + '?autoplay=1&rel=0&modestbranding=1&playsinline=1';
				frame.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
				frame.setAttribute('allowfullscreen', '');
			} else if (src) {
				frame = document.createElement('video');
				frame.src = src; frame.controls = true; frame.autoplay = true; frame.playsInline = true;
			} else {
				return;
			}

			frame.className = 'ah-mvs-frame';
			thumb.appendChild(frame);
			thumb.classList.add('is-playing');
		}

		track.querySelectorAll('.ah-mvs-play-btn').forEach(function (thumb) {
			thumb.addEventListener('click', function () { playInto(thumb); });
			thumb.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') { e.preventDefault(); playInto(thumb); }
			});
		});
	}

	function boot() {
		document.querySelectorAll('.ah-mvs').forEach(initMiniVideo);
	}

	if (document.readyState !== 'loading') boot();
	else document.addEventListener('DOMContentLoaded', boot);
})();
