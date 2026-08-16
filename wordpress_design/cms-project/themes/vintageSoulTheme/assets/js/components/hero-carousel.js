
(function (window, document) {
	'use strict';

	var dom = window.VintageSoul.dom;

	var AUTOPLAY_MS = 4000;

	function init() {
		dom.qsa('[data-vs-hero-carousel]').forEach(function (carousel) {
			var slides = dom.qsa('.hero-carousel__slide', carousel);
			var dots = dom.qsa('.hero-carousel__dot', carousel);
			var prevBtn = dom.qs('.hero-carousel__arrow--prev', carousel);
			var nextBtn = dom.qs('.hero-carousel__arrow--next', carousel);
			var toggleBtn = dom.qs('[data-hero-toggle]', carousel);
			if (slides.length < 2) return;

			var index = 0;
			var autoplay = carousel.hasAttribute('data-hero-autoplay');
			var playing = autoplay;
			var timer = null;

			function activeVideo() {
				return dom.qs('.hero-carousel__media video', slides[index]);
			}

			function onVideoEnded() {
				show(index + 1);
			}

			function clearTimer() {
				if (timer) {
					window.clearTimeout(timer);
					timer = null;
				}
			}

			function pauseActiveMedia() {
				clearTimer();
				var video = activeVideo();
				if (video) video.pause();
			}

			function playActiveMedia(fresh) {
				var video = activeVideo();
				if (video) {
					if (fresh) {
						video.currentTime = 0;
						video.addEventListener('ended', onVideoEnded, { once: true });
					}
					video.muted = true;
					var playPromise = video.play();
					if (playPromise && playPromise.catch) playPromise.catch(function () {});
				} else {
					timer = window.setTimeout(function () { show(index + 1); }, AUTOPLAY_MS);
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
					var video = dom.qs('.hero-carousel__media video', slide);
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
				dots.forEach(function (dot, i) {
					var active = i === index;
					dot.classList.toggle('is-active', active);
					dot.setAttribute('aria-selected', active ? 'true' : 'false');
				});
				queueNext();
			}

			if (prevBtn) prevBtn.addEventListener('click', function () { show(index - 1); });
			if (nextBtn) nextBtn.addEventListener('click', function () { show(index + 1); });
			dots.forEach(function (dot, i) {
				dot.addEventListener('click', function () { show(i); });
			});

			carousel.addEventListener('keydown', function (event) {
				if (event.key === 'ArrowLeft') show(index - 1);
				else if (event.key === 'ArrowRight') show(index + 1);
			});

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

				carousel.addEventListener('mouseenter', pauseActiveMedia);
				carousel.addEventListener('mouseleave', resumeAutoplay);
				carousel.addEventListener('focusin', pauseActiveMedia);
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

	window.VintageSoul.app.register('hero-carousel', init);
})(window, document);
