/**
 * Advaith Homes — Site JS
 * Runs after DOM ready. All data from AH_THEME (wp_localize_script).
 */
(function () {
	'use strict';

	var DATA = window.AH_THEME || {};
	var posts = DATA.blogPosts  || [];
	var news  = DATA.newsItems  || [];
	var wa    = DATA.whatsapp   || '+447747223762';

	// ── DOM Ready ─────────────────────────────────────
	document.addEventListener('DOMContentLoaded', function () {
		initNav();
		initNewsTicker();
		initReveal();
		initFAQ();
		initCountUp();
		initCoverflow();
		initTestimonials();
		initContactForm();
		injectWhatsApp();
	});

	// ── Navigation ────────────────────────────────────
	function initNav() {
		var nav       = document.getElementById('mainNav');
		var hamburger = document.getElementById('ahHamburger');
		var mobileNav = document.getElementById('ahMobileNav');

		// Scroll shadow
		if (nav) {
			window.addEventListener('scroll', function () {
				nav.classList.toggle('scrolled', window.scrollY > 20);
			}, { passive: true });
		}

		// Hamburger toggle
		if (hamburger && mobileNav) {
			hamburger.addEventListener('click', function () {
				var open = mobileNav.classList.toggle('open');
				hamburger.classList.toggle('open', open);
				hamburger.setAttribute('aria-expanded', open);
			});
		}

		// Active link
		var path = window.location.pathname;
		document.querySelectorAll('.nav__link[data-href]').forEach(function (link) {
			if (path === link.dataset.href || (link.dataset.href !== '/' && path.indexOf(link.dataset.href) === 0)) {
				link.classList.add('active');
			}
		});
	}

	// ── News Ticker ───────────────────────────────────
	function initNewsTicker() {
		var track    = document.getElementById('ahNewsTrack');
		var closeBtn = document.getElementById('ahTickerClose');
		var ticker   = document.getElementById('ahNewsTicker');

		if (!track || !news.length) return;

		var html = news.map(function (n) {
			var cls = (n.tag_class || 'tag-gold').replace('tag-', 'news-ticker__tag--');
			return '<a href="' + escUrl(n.url) + '" class="news-ticker__item">'
				+ '<span class="news-ticker__tag ' + cls + '">' + esc(n.tag) + '</span>'
				+ esc(n.title)
				+ '</a>';
		}).join('');

		// Duplicate for seamless loop
		track.innerHTML = html + html;

		if (closeBtn && ticker) {
			closeBtn.addEventListener('click', function () {
				ticker.classList.add('hidden');
				document.body.classList.remove('has-ticker');
			});
		}
	}

	// ── Scroll Reveal ─────────────────────────────────
	function initReveal() {
		var elements = document.querySelectorAll('.reveal');
		if (!elements.length) return;

		// Immediately show anything at/above the fold (generous +200px buffer)
		elements.forEach(function (el) {
			if (el.getBoundingClientRect().top < window.innerHeight + 200) {
				el.classList.add('visible');
			}
		});

		// Hard fallback: everything visible after 800ms regardless
		setTimeout(function () {
			document.querySelectorAll('.reveal:not(.visible)').forEach(function (el) {
				el.classList.add('visible');
			});
		}, 800);

		if (!('IntersectionObserver' in window)) {
			elements.forEach(function (el) { el.classList.add('visible'); });
			return;
		}

		var obs = new IntersectionObserver(function (entries) {
			entries.forEach(function (e) {
				if (!e.isIntersecting) return;
				e.target.classList.add('visible');
				obs.unobserve(e.target);
			});
		}, { threshold: 0.05, rootMargin: '0px 0px -20px 0px' });

		window._revealObserver = obs;

		elements.forEach(function (el) {
			if (!el.classList.contains('visible')) {
				obs.observe(el);
			}
		});
	}

	// ── FAQ Accordion ─────────────────────────────────
	function initFAQ() {
		document.querySelectorAll('.faq-trigger').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var item   = btn.closest('.faq-item');
				var isOpen = item.classList.contains('is-open');
				document.querySelectorAll('.faq-item.is-open').forEach(function (i) {
					i.classList.remove('is-open');
					var t = i.querySelector('.faq-trigger');
					if (t) t.setAttribute('aria-expanded', 'false');
				});
				if (!isOpen) {
					item.classList.add('is-open');
					btn.setAttribute('aria-expanded', 'true');
				}
			});
		});
	}

	// ── Count-Up Animation ────────────────────────────
	function initCountUp() {
		document.querySelectorAll('.count-up').forEach(function (el) {
			var target = +el.dataset.target;
			var obs2 = new IntersectionObserver(function (entries) {
				if (!entries[0].isIntersecting) return;
				var current = 0;
				var inc = target / 60;
				var t = setInterval(function () {
					current = Math.min(current + inc, target);
					el.textContent = Math.floor(current).toLocaleString();
					if (current >= target) clearInterval(t);
				}, 16);
				obs2.unobserve(el);
			});
			obs2.observe(el);
		});
	}

	// ── 3D Coverflow Carousel ─────────────────────────
	function initCoverflow() {
		var items = document.querySelectorAll('.coverflow-item');
		if (!items.length) return;

		var currentIndex = 0;
		var total = items.length;
		var autoPlay;

		function update() {
			items.forEach(function (item, idx) {
				item.classList.remove('active', 'prev', 'next', 'prev-hidden', 'next-hidden');
				if (idx === currentIndex) {
					item.classList.add('active');
				} else if (idx === (currentIndex - 1 + total) % total) {
					item.classList.add('prev');
				} else if (idx === (currentIndex + 1) % total) {
					item.classList.add('next');
				} else if (idx === (currentIndex - 2 + total) % total) {
					item.classList.add('prev-hidden');
				} else {
					item.classList.add('next-hidden');
				}
			});
		}

		function startAuto() {
			autoPlay = setInterval(function () {
				currentIndex = (currentIndex + 1) % total;
				update();
			}, 5000);
		}

		var prevBtn = document.querySelector('.coverflow-btn.prev');
		var nextBtn = document.querySelector('.coverflow-btn.next');
		var container = document.querySelector('.coverflow-container');

		if (prevBtn) prevBtn.addEventListener('click', function () { clearInterval(autoPlay); currentIndex = (currentIndex - 1 + total) % total; update(); startAuto(); });
		if (nextBtn) nextBtn.addEventListener('click', function () { clearInterval(autoPlay); currentIndex = (currentIndex + 1) % total; update(); startAuto(); });

		items.forEach(function (item, idx) {
			item.addEventListener('click', function () { currentIndex = idx; update(); });
		});

		if (container) {
			container.addEventListener('mouseenter', function () { clearInterval(autoPlay); });
			container.addEventListener('mouseleave', startAuto);
		}

		update();
		startAuto();
	}

	// ── Testimonials Slider ───────────────────────────
	function initTestimonials() {
		var track   = document.getElementById('ahTestimonialsTrack');
		var dotsEl  = document.getElementById('ahTestDots');
		var prevBtn = document.getElementById('ahTestPrev');
		var nextBtn = document.getElementById('ahTestNext');
		if (!track) return;

		var cards = track.querySelectorAll('.testimonial-card');
		if (!cards.length) return;

		var tIndex = 0;

		// Build dots
		if (dotsEl) {
			cards.forEach(function (_, i) {
				var d = document.createElement('button');
				d.className = 'testimonials-dot' + (i === 0 ? ' is-active' : '');
				d.setAttribute('aria-label', 'Go to testimonial ' + (i + 1));
				d.addEventListener('click', function () { show(i); });
				dotsEl.appendChild(d);
			});
		}

		function show(idx) {
			tIndex = ((idx % cards.length) + cards.length) % cards.length;
			track.style.transform = 'translateX(-' + (tIndex * 100) + '%)';
			if (dotsEl) {
				dotsEl.querySelectorAll('.testimonials-dot').forEach(function (d, i) {
					d.classList.toggle('is-active', i === tIndex);
				});
			}
		}

		if (nextBtn) nextBtn.addEventListener('click', function () { show(tIndex + 1); });
		if (prevBtn) prevBtn.addEventListener('click', function () { show(tIndex - 1); });

		// Touch/swipe
		var touchStartX = 0;
		track.addEventListener('touchstart', function (e) { touchStartX = e.touches[0].clientX; }, { passive: true });
		track.addEventListener('touchend', function (e) {
			var diff = touchStartX - e.changedTouches[0].clientX;
			if (Math.abs(diff) > 50) show(tIndex + (diff > 0 ? 1 : -1));
		});

		var autoplay = setInterval(function () { show(tIndex + 1); }, 7000);
		track.addEventListener('mouseenter', function () { clearInterval(autoplay); });
	}

	// ── Contact Form AJAX ─────────────────────────────
	function initContactForm() {
		var form   = document.getElementById('ahContactForm');
		var status = document.getElementById('ahContactStatus');
		var submit = document.getElementById('ahContactSubmit');
		if (!form) return;

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var data = new FormData(form);
			data.append('action', 'ah_contact_submit');
			if (submit) { submit.disabled = true; submit.textContent = 'Sending…'; }

			fetch(DATA.ajaxUrl || '/wp-admin/admin-ajax.php', { method: 'POST', body: data })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (status) {
						status.textContent = res.data || (res.success ? 'Message sent! We\'ll be in touch shortly.' : 'Something went wrong. Please try again.');
						status.className = 'form-status ' + (res.success ? 'success' : 'error');
					}
					if (res.success) form.reset();
				})
				.catch(function () {
					if (status) { status.textContent = 'Network error. Please try again.'; status.className = 'form-status error'; }
				})
				.finally(function () {
					if (submit) { submit.disabled = false; submit.textContent = 'Send Message'; }
				});
		});
	}

	// ── WhatsApp Float Button ─────────────────────────
	function injectWhatsApp() {
		if (document.querySelector('.floating-chat')) return;
		var msg = encodeURIComponent('Hi Advaith Homes, I would like to learn more about your services...');
		var btn = document.createElement('a');
		btn.className = 'floating-chat';
		btn.href = 'https://wa.me/' + wa.replace(/[^0-9]/g, '') + '?text=' + msg;
		btn.target = '_blank';
		btn.rel = 'noopener noreferrer';
		btn.setAttribute('aria-label', 'Chat on WhatsApp');
		btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M380.9 97.1C339 55.1 283.2 32 223.9 32c-122.4 0-222 99.6-222 222 0 39.1 10.2 77.3 29.6 111L0 480l117.7-30.9c32.4 17.7 68.9 27 106.1 27h.1c122.3 0 224.1-99.6 224.1-222 0-59.3-25.2-115-67.1-157.1zm-157 341.6c-33.2 0-65.7-8.9-94-25.7l-6.7-4-69.8 18.3L72 359.2l-4.4-7c-18.5-29.4-28.2-63.3-28.2-98.2 0-101.7 82.8-184.5 184.6-184.5 49.3 0 95.6 19.2 130.4 54.1 34.8 34.9 56.2 81.2 56.1 130.5 0 101.8-84.9 184.6-186.6 184.6zm101.2-138.2c-5.5-2.8-32.8-16.2-37.9-18-5.1-1.9-8.8-2.8-12.5 2.8-3.7 5.6-14.3 18-17.6 21.8-3.2 3.7-6.5 4.2-12 1.4-32.6-16.3-54-29.1-75.5-66-5.7-9.8 5.7-9.1 16.3-30.3 1.8-3.7.9-6.9-.5-9.7-1.4-2.8-12.5-30.1-17.1-41.2-4.5-10.8-9.1-9.3-12.5-9.5-3.2-.2-6.9-.2-10.6-.2-3.7 0-9.7 1.4-14.8 6.9-5.1 5.6-19.4 19-19.4 46.3 0 27.3 19.9 53.7 22.6 57.4 2.8 3.7 39.1 59.7 94.8 83.8 35.2 15.2 49 16.5 66.6 13.9 10.7-1.6 32.8-13.4 37.4-26.4 4.6-13 4.6-24.1 3.2-26.4-1.3-2.5-5-3.9-10.5-6.6z"/></svg>'
			+ '<span>Chat with us</span>';
		document.body.appendChild(btn);
	}

	// ── Utilities ─────────────────────────────────────
	function esc(str) {
		return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
	}
	function escUrl(url) {
		return String(url || '#').replace(/"/g,'%22').replace(/'/g,'%27');
	}

})();
