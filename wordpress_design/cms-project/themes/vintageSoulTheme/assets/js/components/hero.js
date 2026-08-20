
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;

	var MOBILE_QUERY = '(max-width: 767px)';
	var SWIPE_THRESHOLD = 40; // px - a deliberate swipe, not an accidental drag

	function init() {
		dom.qsa('[data-vst-hero]').forEach(function (carousel) {
			var slides  = dom.qsa('.vst-hero__slide', carousel);
			var tabs    = dom.qsa('.vst-hero__dot', carousel);
			var toggleBtn = dom.qs('[data-hero-toggle]', carousel);
			var stage   = dom.qs('.vst-hero__stage', carousel);

			pickMobileVideoSources(carousel);

			if ( slides.length < 2 ) return;

			var index = 0;
			var autoplay = carousel.hasAttribute('data-vst-hero-autoplay');
			var playing = autoplay;
			var pauseOnHover = carousel.getAttribute('data-pause-on-hover') !== '0';
			var touchEnabled = carousel.getAttribute('data-touch-enabled') !== '0';
			var keyboardEnabled = carousel.getAttribute('data-keyboard-enabled') !== '0';
			var autoplayMs = parseInt(carousel.getAttribute('data-autoplay-delay'), 10);
			if (!autoplayMs || autoplayMs < 1000) autoplayMs = 6000;
			var timer = null;

			function onVideoEnded() {
				show(index + 1);
			}

			function clearTimer() {
				if (timer) {
					window.clearTimeout(timer);
					timer = null;
				}
			}

			// Stops the image-slide auto-advance timer WITHOUT touching an
			// active video's playback - hover/focus pausing the countdown so
			// a reader can finish an image slide's text is expected, but a
			// playing video visibly freezing because the cursor crossed over
			// it reads as broken, not paused. Video still ends on its own via
			// the 'ended' listener regardless of hover state.
			function stopAutoAdvance() {
				clearTimer();
			}

			// A real pause (the toggle button, or the tab going hidden) -
			// this one DOES stop video playback, unlike hover/focus above.
			function pauseActiveMedia() {
				clearTimer();
				var video = dom.qs('video.vst-hero__media-el', slides[index]);
				if (video) video.pause();
			}

			function playActiveMedia(fresh) {
				var video = dom.qs('video.vst-hero__media-el', slides[index]);
				if (video) {
					if (fresh) {
						video.currentTime = 0;
						video.addEventListener('ended', onVideoEnded, { once: true });
					}
					video.muted = true;
					var playPromise = video.play();
					if (playPromise && playPromise.catch) playPromise.catch(function () {});
				} else {
					timer = window.setTimeout(function () { show(index + 1); }, autoplayMs);
				}
			}

			function queueNext() {
				clearTimer();
				if (!autoplay || !playing || document.hidden) return;
				playActiveMedia(true);
			}

			function resumeAutoplay() {
				if (!autoplay || !playing || document.hidden) return;
				playActiveMedia(false);
			}

			function show(next) {
				clearTimer();

				slides.forEach(function (slide) {
					var video = dom.qs('video.vst-hero__media-el', slide);
					if (video) {
						video.removeEventListener('ended', onVideoEnded);
						video.pause();
					}
				});
				index = (next + slides.length) % slides.length;
				slides.forEach(function (slide, i) {
					var active = i === index;
					slide.classList.toggle('is-active', active);
					slide.inert = !active;
				});
				tabs.forEach(function (tab, i) {
					var active = i === index;
					tab.classList.toggle('is-active', active);
					tab.setAttribute('aria-selected', active ? 'true' : 'false');
				});
				queueNext();
			}

			tabs.forEach(function (tab, i) {
				tab.addEventListener('click', function () { show(i); });
			});

			if (keyboardEnabled) {
				carousel.addEventListener('keydown', function (event) {
					if (event.key === 'ArrowLeft') show(index - 1);
					else if (event.key === 'ArrowRight') show(index + 1);
				});
			}

			if (touchEnabled && stage) {
				var touchStartX = null;
				var touchStartY = null;

				stage.addEventListener('pointerdown', function (event) {
					if (event.pointerType === 'mouse') return;
					touchStartX = event.clientX;
					touchStartY = event.clientY;
				}, { passive: true });

				stage.addEventListener('pointerup', function (event) {
					if (touchStartX === null) return;
					var deltaX = event.clientX - touchStartX;
					var deltaY = event.clientY - touchStartY;
					touchStartX = null;
					touchStartY = null;
					if (Math.abs(deltaX) < SWIPE_THRESHOLD || Math.abs(deltaX) < Math.abs(deltaY)) return;
					show(deltaX < 0 ? index + 1 : index - 1);
				}, { passive: true });
			}

			if (autoplay) {
				if (toggleBtn) {
					toggleBtn.addEventListener('click', function () {
						playing = !playing;
						toggleBtn.classList.toggle('is-paused', !playing);
						toggleBtn.setAttribute(
							'aria-label',
							playing
								? (toggleBtn.dataset.labelPause || 'Pause slideshow')
								: (toggleBtn.dataset.labelPlay || 'Play slideshow')
						);
						if (playing) queueNext();
						else pauseActiveMedia();
					});
				}

				if (pauseOnHover) {
					carousel.addEventListener('mouseenter', stopAutoAdvance);
					carousel.addEventListener('mouseleave', resumeAutoplay);
				}
				carousel.addEventListener('focusin', stopAutoAdvance);
				carousel.addEventListener('focusout', function (event) {
					if (!carousel.contains(event.relatedTarget)) resumeAutoplay();
				});
				document.addEventListener('visibilitychange', function () {
					if (document.hidden) pauseActiveMedia();
					else resumeAutoplay();
				});

				queueNext();
			}
		});
	}

	// A video slide's `mobile_src` (if any) is picked once, before first
	// play, instead of loading both - keeps this a one-request asset like
	// the <picture>/<source> swap already does for images with zero JS.
	function pickMobileVideoSources(carousel) {
		if (!window.matchMedia || !window.matchMedia(MOBILE_QUERY).matches) return;
		dom.qsa('video.vst-hero__media-el[data-mobile-src]', carousel).forEach(function (video) {
			var mobileSrc = video.getAttribute('data-mobile-src');
			if (mobileSrc) {
				video.setAttribute('src', mobileSrc);
			}
		});
	}

	window.VintageSoul.app.register('hero', init);
})(window, document);
