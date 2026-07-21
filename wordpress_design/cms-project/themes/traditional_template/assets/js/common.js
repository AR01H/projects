/**
 * assets/js/common.js - The NT helper. Loaded on every page.
 *
 * window.ntSite is injected by core/assets.php:
 *   { homeUrl, ajaxUrl, restUrl, restNonce, nonces: { action: nonce } }
 *
 * NT.ajax( action, data )  - POST to admin-ajax for an action registered in
 *                            config/ajax.php. The wp action name prefix and
 *                            per-action nonce are attached automatically.
 *                            Resolves to the JSON body ({ success, data }).
 *
 * NT.rest( route, params ) - GET a route registered in config/rest.php
 *                            (e.g. NT.rest('posts', { page: 2 })).
 *
 * Any <form data-nt-ajax-form="action_key"> (rendered by
 * components/parts/generic-form.php) is auto-wired below: submit posts its
 * fields through NT.ajax(), shows the result in its .nt-form-status element,
 * and resets on success. No page-specific JS needed for a plain form.
 */
(function () {
	'use strict';

	var site = window.ntSite || {};

	function ajax(action, data) {
		var body = new FormData();
		body.append('action', 'nt_' + action);
		if (site.nonces && site.nonces[action]) {
			body.append('nonce', site.nonces[action]);
		}
		Object.keys(data || {}).forEach(function (key) {
			body.append(key, data[key]);
		});
		return fetch(site.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		}).then(function (res) { return res.json(); });
	}

	function rest(route, params) {
		var url = new URL(String(site.restUrl).replace(/\/$/, '') + '/' + String(route).replace(/^\//, ''));
		Object.keys(params || {}).forEach(function (key) {
			url.searchParams.set(key, params[key]);
		});
		return fetch(url.toString(), {
			credentials: 'same-origin',
			headers: { 'X-WP-Nonce': site.restNonce || '' }
		}).then(function (res) { return res.json(); });
	}

	/** Tiny helpers used by the page scripts. */
	function el(tag, className, text) {
		var node = document.createElement(tag);
		if (className) { node.className = className; }
		if (text) { node.textContent = text; } // textContent = XSS-safe
		return node;
	}

	function debounce(fn, wait) {
		var t;
		return function () {
			var args = arguments, ctx = this;
			clearTimeout(t);
			t = setTimeout(function () { fn.apply(ctx, args); }, wait);
		};
	}

	/** Wire every data-nt-ajax-form on the page to NT.ajax(). */
	function initAjaxForms() {
		var forms = document.querySelectorAll('form[data-nt-ajax-form]');
		forms.forEach(function (form) {
			if (form.dataset.ntAjaxBound) { return; }
			form.dataset.ntAjaxBound = '1';

			var action    = form.getAttribute('data-nt-ajax-form');
			var status    = form.querySelector('.nt-form-status');
			var submitBtn = form.querySelector('[type="submit"]');
			var sentLabel = submitBtn ? submitBtn.textContent : '';

			function setStatus(message, isError) {
				if (!status) { return; }
				status.style.display = 'block';
				status.textContent = message;
				status.classList.toggle('is-error', !!isError);
			}

			form.addEventListener('submit', function (e) {
				e.preventDefault();
				if (!action) { return; }

				var data = {};
				new FormData(form).forEach(function (value, key) { data[key] = value; });

				if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Sending…'; }
				setStatus('Sending…', false);

				ajax(action, data).then(function (res) {
					var ok  = !!(res && res.success);
					var msg = (res && res.data && res.data.message) || (ok ? 'Thank you!' : 'Something went wrong. Please try again.');
					setStatus(msg, !ok);
					if (ok) { form.reset(); }
				}).catch(function () {
					setStatus('Network error. Please try again.', true);
				}).then(function () {
					if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = sentLabel; }
				});
			});
		});
	}

	/**
	 * Lightweight, dependency-free modal wiring.
	 *   - [data-nt-open="modalId"]  opens  #modalId  (adds .is-open)
	 *   - [data-nt-close]           closes its nearest .nt-modal
	 *   - clicking the .nt-modal backdrop, or Escape, also closes it
	 */
	function openModal(modal) {
		if (!modal) { return; }
		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
		document.body.style.overflow = 'hidden';
	}
	function closeModal(modal) {
		if (!modal) { return; }
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
		document.body.style.overflow = '';
	}
	function initModals() {
		document.querySelectorAll('[data-nt-open]').forEach(function (trigger) {
			if (trigger.dataset.ntOpenBound) { return; }
			trigger.dataset.ntOpenBound = '1';
			trigger.addEventListener('click', function (e) {
				e.preventDefault();
				openModal(document.getElementById(trigger.getAttribute('data-nt-open')));
			});
		});
		document.querySelectorAll('.nt-modal').forEach(function (modal) {
			if (modal.dataset.ntModalBound) { return; }
			modal.dataset.ntModalBound = '1';
			modal.addEventListener('click', function (e) {
				if (e.target === modal || e.target.hasAttribute('data-nt-close') || e.target.closest('[data-nt-close]')) {
					closeModal(modal);
				}
			});
		});
	}
	document.addEventListener('keydown', function (e) {
		if (e.key === 'Escape') {
			document.querySelectorAll('.nt-modal.is-open').forEach(closeModal);
		}
	});

	/**
	 * Multi-step wizard forms. Drives the markup rendered by
	 * components/parts/generic-multistep-form.php (.nt-bk-step / .nt-bk-next /
	 * .nt-bk-back / .nt-bk-prog-step / .nt-bk-prog-fill) inside a modal.
	 * Validates required fields per step, then submits every field via
	 * NT.ajax(form.dataset.ntAction) and shows a success panel.
	 */
	function escapeHtml(s) {
		var d = document.createElement('div');
		d.textContent = (s == null ? '' : s);
		return d.innerHTML;
	}
	function initWizards() {
		document.querySelectorAll('form[data-nt-wizard]').forEach(function (form) {
			if (form.dataset.ntWizBound) { return; }
			form.dataset.ntWizBound = '1';

			var root      = form.closest('.nt-bk-modal-scroll') || form.parentNode;
			var steps     = Array.prototype.slice.call(form.querySelectorAll('.nt-bk-step'));
			var progSteps = Array.prototype.slice.call(root.querySelectorAll('.nt-bk-prog-step'));
			var progFill  = root.querySelector('.nt-bk-prog-fill');
			var msg       = form.querySelector('.nt-form-feedback');
			var action    = form.getAttribute('data-nt-action');
			var total     = steps.length;
			var current   = 1;
			if (!total) { return; }

			function say(text, type) {
				if (!msg) { return; }
				msg.textContent = text;
				msg.className = 'nt-form-feedback ' + (type || '');
				msg.style.display = text ? 'block' : 'none';
			}
			function show(n) {
				current = Math.max(1, Math.min(total, n));
				steps.forEach(function (s) { s.classList.toggle('active', parseInt(s.dataset.step, 10) === current); });
				progSteps.forEach(function (p) {
					var ps = parseInt(p.dataset.step, 10);
					p.classList.toggle('active', ps === current);
					p.classList.toggle('done', ps < current);
				});
				if (progFill && total > 1) { progFill.style.width = ((current - 1) / (total - 1) * 100) + '%'; }
				say('');
			}
			function stepValid() {
				var stepEl = steps[current - 1];
				var ok = true;
				stepEl.querySelectorAll('[required]').forEach(function (f) {
					var empty = !String(f.value).trim();
					f.classList.toggle('nt-invalid', empty);
					if (empty) { ok = false; }
				});
				if (!ok) { say('Please fill in the required fields to continue.', 'error'); }
				return ok;
			}

			form.querySelectorAll('.nt-bk-next').forEach(function (b) {
				b.addEventListener('click', function () { if (stepValid()) { show(parseInt(b.dataset.next, 10)); } });
			});
			form.querySelectorAll('.nt-bk-back').forEach(function (b) {
				b.addEventListener('click', function () { show(parseInt(b.dataset.back, 10)); });
			});
			progSteps.forEach(function (p) {
				p.addEventListener('click', function () {
					var ps = parseInt(p.dataset.step, 10);
					if (ps < current) { show(ps); }
				});
			});

			form.addEventListener('submit', function (e) {
				e.preventDefault();
				if (!stepValid()) { return; }
				var btn = form.querySelector('[type="submit"]');
				var original = btn ? btn.textContent : '';
				if (btn) { btn.disabled = true; btn.textContent = 'Sending…'; }

				var data = {};
				new FormData(form).forEach(function (v, k) { data[k] = v; });

				ajax(action, data).then(function (res) {
					if (res && res.success) {
						var prog = root.querySelector('.nt-bk-progress');
						if (prog) { prog.style.display = 'none'; }
						var last = steps[total - 1];
						if (last) {
							last.innerHTML =
								'<div class="nt-wiz-success">' +
								'<div class="nt-wiz-success__icon">🎉</div>' +
								'<h3>All done!</h3>' +
								'<p>' + escapeHtml((res.data && res.data.message) || "Thank you! We'll be in touch soon.") + '</p>' +
								'<button type="button" class="btn" data-nt-close>Close</button>' +
								'</div>';
						}
					} else {
						say((res && res.data && res.data.message) || 'Something went wrong. Please try again.', 'error');
						if (btn) { btn.disabled = false; btn.textContent = original; }
					}
				}).catch(function () {
					say('Network error. Please try again.', 'error');
					if (btn) { btn.disabled = false; btn.textContent = original; }
				});
			});

			show(1);
		});
	}

	/**
	 * Home media banner - fading carousel of image AND video slides.
	 * Markup: components/media-carousel.php (.nt-media-carousel).
	 */
	function initMediaCarousels() {
		document.querySelectorAll('.nt-media-carousel').forEach(function (root) {
			if (root.dataset.ntMediaBound) { return; }
			root.dataset.ntMediaBound = '1';

			var slides = Array.prototype.slice.call(root.querySelectorAll('.nt-media-slide'));
			var dots   = Array.prototype.slice.call(root.querySelectorAll('[data-nt-media-dot]'));
			if (slides.length < 2) {
				var only = slides[0] && slides[0].querySelector('video');
				if (only) { only.play && only.play().catch(function () {}); }
				return;
			}

			var current  = 0;
			var interval = parseInt(root.getAttribute('data-interval'), 10) || 6000;
			var autoplay = root.getAttribute('data-autoplay') === '1';
			var timer    = null;

			function playVideoIn(slide) {
				var v = slide.querySelector('video');
				if (v) { v.currentTime = 0; var p = v.play(); if (p && p.catch) { p.catch(function () {}); } }
			}
			function pauseVideoIn(slide) {
				var v = slide.querySelector('video');
				if (v) { v.pause(); }
			}
			function go(n) {
				var prev = current;
				current = (n + slides.length) % slides.length;
				if (prev === current) { return; }
				slides.forEach(function (s, i) { s.classList.toggle('is-active', i === current); });
				dots.forEach(function (d, i) { d.classList.toggle('is-active', i === current); });
				pauseVideoIn(slides[prev]);
				playVideoIn(slides[current]);
			}
			function next() { go(current + 1); }
			function prev() { go(current - 1); }
			function restart() {
				if (!autoplay) { return; }
				if (timer) { clearInterval(timer); }
				timer = setInterval(next, interval);
			}

			var nextBtn = root.querySelector('[data-nt-media-next]');
			var prevBtn = root.querySelector('[data-nt-media-prev]');
			if (nextBtn) { nextBtn.addEventListener('click', function () { next(); restart(); }); }
			if (prevBtn) { prevBtn.addEventListener('click', function () { prev(); restart(); }); }
			dots.forEach(function (d) {
				d.addEventListener('click', function () { go(parseInt(d.getAttribute('data-nt-media-dot'), 10)); restart(); });
			});
			root.addEventListener('mouseenter', function () { if (timer) { clearInterval(timer); } });
			root.addEventListener('mouseleave', restart);

			playVideoIn(slides[0]);
			restart();
		});
	}

	/**
	 * Toggle .scrolled on the header once the page moves past the top. Drives
	 * the blended transparent-header state (body.nt-hero-top in vintage.css).
	 * Reliable/vanilla so it works regardless of the legacy bundle.
	 */
	function initNavScroll() {
		var nav = document.querySelector('.nt-nav');
		if (!nav) { return; }
		function onScroll() { nav.classList.toggle('scrolled', window.scrollY > 60); }
		window.addEventListener('scroll', onScroll, { passive: true });
		onScroll();
	}

	function init() {
		initAjaxForms();
		initModals();
		initWizards();
		initMediaCarousels();
		initNavScroll();
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	window.NT = { ajax: ajax, rest: rest, el: el, debounce: debounce, openModal: openModal, closeModal: closeModal };
}());
