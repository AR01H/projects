/**
 * VintageSoulTheme - Vintage Carousel Arrow Controller
 *
 * Automatically hides/shows arrow navigation buttons based on whether
 * the carousel has scrollable overflow. Also handles contact form
 * submission with a graceful success toast.
 */
(function () {
	'use strict';

	/* ═══════════════════════════════════════════════
	   1. CAROUSEL ARROW VISIBILITY
	   Hide arrows when track content fits without scrolling.
	══════════════════════════════════════════════════ */
	function updateCarouselArrows(wrapper) {
		var track = wrapper.querySelector('.vintage-card-carousel');
		if (!track) return;

		var prev = wrapper.querySelector('.vintage-carousel-ctrl--prev');
		var next = wrapper.querySelector('.vintage-carousel-ctrl--next');
		if (!prev && !next) return;

		function checkOverflow() {
			var canScroll = track.scrollWidth > track.clientWidth + 4;
			if (prev) {
				prev.style.visibility = canScroll ? 'visible' : 'hidden';
				prev.style.pointerEvents = canScroll ? '' : 'none';
				prev.setAttribute('aria-hidden', canScroll ? 'false' : 'true');
			}
			if (next) {
				next.style.visibility = canScroll ? 'visible' : 'hidden';
				next.style.pointerEvents = canScroll ? '' : 'none';
				next.setAttribute('aria-hidden', canScroll ? 'false' : 'true');
			}
		}

		checkOverflow();

		// Also update on track scroll (to show/hide prev arrow when at start)
		track.addEventListener('scroll', function () {
			if (!prev || !next) return;
			var atStart = track.scrollLeft <= 4;
			var atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 4;
			var canScroll = track.scrollWidth > track.clientWidth + 4;
			prev.style.visibility = (canScroll && !atStart) ? 'visible' : 'hidden';
			next.style.visibility = (canScroll && !atEnd) ? 'visible' : 'hidden';
		});
	}

	function initCarouselArrows() {
		var wrappers = document.querySelectorAll('.vintage-carousel-wrapper');
		wrappers.forEach(updateCarouselArrows);
	}

	/* ═══════════════════════════════════════════════
	   2. CONTACT FORM SUBMISSION HANDLER
	   Show a vintage success toast on submit.
	   API stub is a no-op; swap for real endpoint later.
	══════════════════════════════════════════════════ */
	function showVintageToast(message, type) {
		type = type || 'success';
		var toast = document.createElement('div');
		toast.className = 'vintage-form-toast vintage-form-toast--' + type;
		toast.setAttribute('role', 'status');
		toast.setAttribute('aria-live', 'polite');
		toast.innerHTML =
			'<span class="vintage-form-toast__icon">' +
			(type === 'success' ? '✓' : '✕') +
			'</span>' +
			'<span class="vintage-form-toast__text">' + message + '</span>';

		toast.style.cssText = [
			'position: fixed',
			'bottom: 32px',
			'left: 50%',
			'transform: translateX(-50%) translateY(20px)',
			'opacity: 0',
			'z-index: 999999',
			'display: flex',
			'align-items: center',
			'gap: 10px',
			'padding: 14px 28px 14px 20px',
			'background: linear-gradient(135deg, #0d2f16 0%, #11381b 100%)',
			'border: 1.5px solid #caa06d',
			'box-shadow: inset 0 0 0 1px #8e622d, 0 8px 28px rgba(0,0,0,0.5)',
			'border-radius: 6px',
			'color: #f6d599',
			'font-family: Cinzel, serif',
			'font-size: 13px',
			'font-weight: 700',
			'letter-spacing: 0.06em',
			'text-transform: uppercase',
			'transition: opacity 0.3s ease, transform 0.3s ease',
			'max-width: min(420px, 90vw)',
			'text-align: left',
			'white-space: nowrap',
		].join(';');

		if (type === 'error') {
			toast.style.background = 'linear-gradient(135deg, #3b0d0d 0%, #5a1212 100%)';
			toast.style.borderColor = '#d4a06d';
		}

		document.body.appendChild(toast);

		requestAnimationFrame(function () {
			requestAnimationFrame(function () {
				toast.style.opacity = '1';
				toast.style.transform = 'translateX(-50%) translateY(0)';
			});
		});

		setTimeout(function () {
			toast.style.opacity = '0';
			toast.style.transform = 'translateX(-50%) translateY(20px)';
			setTimeout(function () {
				if (toast.parentNode) toast.parentNode.removeChild(toast);
			}, 350);
		}, 4500);
	}

	function handleContactFormSubmit(form) {
		form.addEventListener('submit', function (e) {
			e.preventDefault();

			var btn = form.querySelector('[type="submit"]');
			var originalHTML = btn ? btn.innerHTML : '';
			if (btn) {
				btn.disabled = true;
				btn.innerHTML = '<span class="btn__text">SENDING…</span>';
			}

			// API stub — replace with actual fetch() call when endpoint is ready
			setTimeout(function () {
				if (btn) {
					btn.disabled = false;
					btn.innerHTML = originalHTML;
				}
				form.reset();
				showVintageToast('Message sent! We\'ll be in touch within 24 hours.', 'success');
			}, 900);
		});
	}

	function initContactForms() {
		var forms = document.querySelectorAll(
			'.vintage-form, .contact-vintage-form, #contact-vintage-form, [data-contact-form]'
		);
		forms.forEach(function (form) {
			if (form.getAttribute('data-vs-contact-handled') === 'true') return;
			form.setAttribute('data-vs-contact-handled', 'true');
			handleContactFormSubmit(form);
		});
	}

	/* ═══════════════════════════════════════════════
	   3. INIT
	══════════════════════════════════════════════════ */
	function init() {
		initCarouselArrows();
		initContactForms();

		// Re-check after images/fonts load (may change sizes)
		window.addEventListener('load', initCarouselArrows);

		// Re-check on resize
		var resizeTimer;
		window.addEventListener('resize', function () {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(initCarouselArrows, 150);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
