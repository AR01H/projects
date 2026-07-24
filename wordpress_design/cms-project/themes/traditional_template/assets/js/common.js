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

	/**
	 * Image lightbox - generic and opt-in, no library.
	 *
	 * Mark a container with data-nt-lightbox; every <img> inside it becomes
	 * clickable and opens full-screen. Images in the SAME container form a
	 * gallery you can page through (arrows / swipe-free prev-next buttons).
	 * Caption comes from the image's data-caption, else its alt text.
	 *
	 *   <div data-nt-lightbox> <img src alt data-caption> ... </div>
	 *
	 * Keyboard: Esc closes, Left/Right navigate. Focus returns to the opener.
	 */
	function initLightbox() {
		var groups = document.querySelectorAll('[data-nt-lightbox]');
		if (!groups.length) { return; }

		var overlay, imgEl, capEl, current = [], index = 0, opener = null;

		function build() {
			overlay = document.createElement('div');
			overlay.className = 'nt-lightbox';
			overlay.setAttribute('role', 'dialog');
			overlay.setAttribute('aria-modal', 'true');
			overlay.setAttribute('aria-label', 'Image viewer');
			overlay.innerHTML =
				'<button type="button" class="nt-lightbox__close" aria-label="Close">&times;</button>' +
				'<button type="button" class="nt-lightbox__nav nt-lightbox__nav--prev" aria-label="Previous image">&#8249;</button>' +
				'<figure class="nt-lightbox__figure">' +
					'<img class="nt-lightbox__img" src="" alt="">' +
					'<figcaption class="nt-lightbox__cap"></figcaption>' +
				'</figure>' +
				'<button type="button" class="nt-lightbox__nav nt-lightbox__nav--next" aria-label="Next image">&#8250;</button>';
			document.body.appendChild(overlay);
			imgEl = overlay.querySelector('.nt-lightbox__img');
			capEl = overlay.querySelector('.nt-lightbox__cap');

			overlay.querySelector('.nt-lightbox__close').addEventListener('click', close);
			overlay.querySelector('.nt-lightbox__nav--prev').addEventListener('click', function (e) { e.stopPropagation(); step(-1); });
			overlay.querySelector('.nt-lightbox__nav--next').addEventListener('click', function (e) { e.stopPropagation(); step(1); });
			overlay.addEventListener('click', function (e) { if (e.target === overlay) { close(); } });
			document.addEventListener('keydown', function (e) {
				if (!overlay.classList.contains('is-open')) { return; }
				if (e.key === 'Escape') { close(); }
				if (e.key === 'ArrowLeft') { step(-1); }
				if (e.key === 'ArrowRight') { step(1); }
			});
		}

		function show() {
			var img = current[index];
			if (!img) { return; }
			imgEl.src = img.currentSrc || img.src;
			imgEl.alt = img.alt || '';
			var cap = img.getAttribute('data-caption') || img.alt || '';
			capEl.textContent = cap;
			capEl.hidden = (cap === '');
			var multi = current.length > 1;
			overlay.querySelector('.nt-lightbox__nav--prev').hidden = !multi;
			overlay.querySelector('.nt-lightbox__nav--next').hidden = !multi;
		}
		function step(dir) {
			if (!current.length) { return; }
			index = (index + dir + current.length) % current.length;
			show();
		}
		function open(list, i, from) {
			if (!overlay) { build(); }
			current = list; index = i; opener = from || null;
			show();
			overlay.classList.add('is-open');
			document.body.classList.add('nt-lightbox-open');
			overlay.querySelector('.nt-lightbox__close').focus();
		}
		function close() {
			if (!overlay) { return; }
			overlay.classList.remove('is-open');
			document.body.classList.remove('nt-lightbox-open');
			if (opener && typeof opener.focus === 'function') { opener.focus(); }
		}

		groups.forEach(function (group) {
			var imgs = Array.prototype.slice.call(group.querySelectorAll('img'));
			if (!imgs.length) { return; }
			imgs.forEach(function (img, i) {
				img.classList.add('nt-lightbox-trigger');
				if (!img.hasAttribute('tabindex')) { img.setAttribute('tabindex', '0'); }
				img.setAttribute('role', 'button');
				img.addEventListener('click', function () { open(imgs, i, img); });
				img.addEventListener('keydown', function (e) {
					if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(imgs, i, img); }
				});
			});
		});
	}

	/**
	 * Tag filtering - generic, data-attribute driven, no config.
	 *
	 *   <div data-nt-filter>
	 *     <button data-nt-filter-btn="all">All</button>
	 *     <button data-nt-filter-btn="classic">Classic</button>
	 *     <article data-nt-filter-item data-tags="classic herbal"> … </article>
	 *     <p data-nt-filter-empty hidden>Nothing here</p>
	 *   </div>
	 *
	 * "all" (or an empty key) shows everything. Progressive enhancement: with
	 * JS off every item stays visible, so content is never script-dependent.
	 */
	function initFilters() {
		var scopes = document.querySelectorAll('[data-nt-filter]');
		scopes.forEach(function (scope) {
			var buttons = scope.querySelectorAll('[data-nt-filter-btn]');
			var items   = scope.querySelectorAll('[data-nt-filter-item]');
			var empty   = scope.querySelector('[data-nt-filter-empty]');
			if (!buttons.length || !items.length) { return; }

			function apply(key) {
				var shown = 0;
				items.forEach(function (item) {
					var tags = (item.getAttribute('data-tags') || '').split(/\s+/);
					var show = (!key || key === 'all' || tags.indexOf(key) !== -1);
					item.hidden = !show;
					if (show) { shown++; }
				});
				if (empty) { empty.hidden = (shown > 0); }
			}

			buttons.forEach(function (btn) {
				btn.addEventListener('click', function () {
					buttons.forEach(function (b) {
						b.classList.toggle('is-active', b === btn);
						b.setAttribute('aria-selected', b === btn ? 'true' : 'false');
					});
					apply(btn.getAttribute('data-nt-filter-btn'));
				});
			});

			var initial = scope.querySelector('[data-nt-filter-btn].is-active');
			apply(initial ? initial.getAttribute('data-nt-filter-btn') : 'all');
		});
	}

	/**
	 * Scroll UI: a reading-progress rule across the top of the page and a
	 * back-to-top button that appears once you are well down the page.
	 *
	 * Both elements are created here rather than in a template, so nothing in
	 * the markup has to opt in and no page can end up with a duplicate. Skipped
	 * entirely on pages too short to scroll meaningfully.
	 */
	function initScrollUI() {
		var doc = document.documentElement;
		if (!document.body) { return; }

		var progress = document.createElement('div');
		progress.className = 'nt-progress';

		var toTop = document.createElement('button');
		toTop.type = 'button';
		toTop.className = 'nt-totop';
		toTop.setAttribute('aria-label', 'Back to top');
		toTop.innerHTML = '<span aria-hidden="true">&#8593;</span>';

		document.body.appendChild(progress);
		document.body.appendChild(toTop);

		var ticking = false;

		function update() {
			ticking = false;
			var max = (doc.scrollHeight || 0) - window.innerHeight;
			if (max <= 40) {
				progress.style.transform = 'scaleX(0)';
				toTop.classList.remove('is-visible');
				return;
			}
			var y = window.pageYOffset || doc.scrollTop || 0;
			var ratio = Math.min(1, Math.max(0, y / max));
			progress.style.transform = 'scaleX(' + ratio + ')';
			toTop.classList.toggle('is-visible', y > window.innerHeight * 0.8);
		}

		function onScroll() {
			if (ticking) { return; }
			ticking = true;
			window.requestAnimationFrame(update);
		}

		toTop.addEventListener('click', function () {
			var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
			window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
			// Send focus somewhere sensible at the top instead of leaving it on
			// a button that is about to fade out.
			var target = document.querySelector('header a, header button, #nt-main');
			if (target) {
				if (!target.hasAttribute('tabindex') && target.id === 'nt-main') {
					target.setAttribute('tabindex', '-1');
				}
				target.focus({ preventScroll: true });
			}
		});

		window.addEventListener('scroll', onScroll, { passive: true });
		window.addEventListener('resize', onScroll);
		update();
	}

	function init() {
		initAjaxForms();
		initModals();
		initWizards();
		initMediaCarousels();
		initNavScroll();
		initLightbox();
		initFilters();
		initScrollUI();
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	window.NT = { ajax: ajax, rest: rest, el: el, debounce: debounce, openModal: openModal, closeModal: closeModal };
}());
