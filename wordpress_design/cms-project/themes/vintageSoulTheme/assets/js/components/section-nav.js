(function (window, document) {
	'use strict';

	var MIN_SECTIONS_REQUIRED = 3;
	var SCROLL_REVEAL_THRESHOLD = 120;

	function cleanTitle(text) {
		if (!text) return '';
		return text.replace(/[—–\-~*•]/g, '').trim();
	}

	function getSectionTitle(section) {
		if (section.dataset.sectionTitle) {
			return cleanTitle(section.dataset.sectionTitle);
		}

		var heading = section.querySelector('h1, h2, .section-title, .section-header__title, .about-section-title, h3');
		if (heading && heading.textContent) {
			var text = cleanTitle(heading.textContent);
			if (text.length > 26) {
				text = text.substring(0, 23) + '...';
			}
			return text;
		}

		var id = section.id || '';
		return id.replace(/[-_]/g, ' ').replace(/\b\w/g, function (l) { return l.toUpperCase(); });
	}

	function getSectionIcon(id, title) {
		id = (id || '').toLowerCase();
		title = (title || '').toLowerCase();

		if (id.includes('hero') || id.includes('top')) {
			return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';
		}
		if (id.includes('intro') || id.includes('mission')) {
			return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>';
		}
		if (id.includes('story') || id.includes('history') || id.includes('heritage') || id.includes('about')) {
			return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>';
		}
		if (id.includes('blog') || id.includes('article') || id.includes('chronicle') || id.includes('post') || id.includes('journal')) {
			return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>';
		}
		if (id.includes('video') || id.includes('showcase') || id.includes('watch')) {
			return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"/></svg>';
		}
		if (id.includes('sourc') || id.includes('farm') || id.includes('origin') || id.includes('cane')) {
			return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>';
		}
		if (id.includes('cert') || id.includes('qual') || id.includes('standard')) {
			return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="m15.4 12.5 2.6 7.5-6-3-6 3 2.6-7.5"/></svg>';
		}
		if (id.includes('benefit') || id.includes('health') || id.includes('wellness')) {
			return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>';
		}
		if (id.includes('serve') || id.includes('step') || id.includes('process')) {
			return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';
		}
		if (id.includes('memor') || id.includes('gallery') || id.includes('moment') || id.includes('photo')) {
			return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>';
		}
		if (id.includes('review') || id.includes('testim') || id.includes('quote')) {
			return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';
		}
		if (id.includes('community') || id.includes('social-stream') || id.includes('social') || id.includes('feed')) {
			return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>';
		}
		if (id.includes('event') || id.includes('cater') || id.includes('party') || id.includes('wedding')) {
			return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
		}
		if (id.includes('franchise') || id.includes('kiosk') || id.includes('business') || id.includes('partner')) {
			return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/></svg>';
		}
		if (id.includes('drink') || id.includes('juice') || id.includes('product') || id.includes('menu') || id.includes('order')) {
			return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2h8l1 9H7l1-9Z"/><path d="M17 11l-1.5 11h-7L7 11"/></svg>';
		}
		if (id.includes('contact') || id.includes('enquir') || id.includes('touch') || id.includes('concierge')) {
			return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>';
		}
		if (id.includes('faq') || id.includes('question') || id.includes('help')) {
			return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
		}

		return '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>';
	}

	function init() {
		var navContainer = document.getElementById('vintage-section-nav');
		var navTrack = document.getElementById('vintage-section-nav-track');
		if (!navContainer || !navTrack) return;

		// Query all major section containers with an ID
		var allSections = Array.from(document.querySelectorAll('section[id], div[id].section, div[id].hero-carousel, header[id].about-hero, header[id].contact-hero, header[id].page-hero'));
		
		var validSections = allSections.filter(function (sec) {
			var id = sec.id || '';
			if (!id || sec.offsetHeight < 80) return false;
			if (/^(wpadminbar|masthead|colophon|main|scroll-top|partners)/i.test(id)) return false;
			if (sec.classList.contains('ribbon-ticker') || sec.classList.contains('trust-ribbon') || sec.classList.contains('logo-strip')) return false;
			return true;
		});

		// If fewer than MIN_SECTIONS_REQUIRED, hide the rail
		if (validSections.length < MIN_SECTIONS_REQUIRED) {
			navContainer.style.display = 'none';
			return;
		}

		navTrack.innerHTML = '';

		var navItems = [];
		var idleTimer = null;

		function resetIdleTimer() {
			navContainer.classList.remove('is-collapsed');
			clearTimeout(idleTimer);
			idleTimer = setTimeout(function () {
				if (window.scrollY > SCROLL_REVEAL_THRESHOLD) {
					navContainer.classList.add('is-collapsed');
				}
			}, 6000);
		}

		validSections.forEach(function (sec, idx) {
			var id = sec.id;
			var title = getSectionTitle(sec);
			var iconSvg = getSectionIcon(id, title);

			var item = document.createElement('div');
			item.className = 'vintage-section-nav__item' + (idx === 0 ? ' is-active' : '');
			item.setAttribute('data-target-id', id);

			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'vintage-section-nav__btn';
			btn.setAttribute('aria-label', 'Navigate to ' + title);
			btn.innerHTML = iconSvg;

			var tooltip = document.createElement('span');
			tooltip.className = 'vintage-section-nav__tooltip';
			tooltip.textContent = title;

			btn.appendChild(tooltip);
			item.appendChild(btn);
			navTrack.appendChild(item);

			btn.addEventListener('click', function (e) {
				e.preventDefault();
				resetIdleTimer();
				var targetEl = document.getElementById(id);
				if (targetEl) {
					var offset = 65;
					var targetPos = targetEl.getBoundingClientRect().top + window.pageYOffset - offset;
					window.scrollTo({
						top: targetPos,
						behavior: 'smooth'
					});
				}
			});

			navItems.push({
				id: id,
				element: sec,
				navItem: item
			});
		});

		var lastActiveId = null;

		function autoScrollToActiveItem(activeObj) {
			if (!activeObj || !activeObj.navItem || !navTrack) return;
			if (lastActiveId === activeObj.id) return;
			lastActiveId = activeObj.id;

			var item = activeObj.navItem;
			var trackHeight = navTrack.clientHeight;
			var itemTop = item.offsetTop;
			var itemHeight = item.offsetHeight;

			// Target scroll position centering the active item in the 70vh track
			var targetTop = itemTop - (trackHeight / 2) + (itemHeight / 2);

			navTrack.scrollTo({
				top: Math.max(0, targetTop),
				behavior: 'smooth'
			});
		}

		function onScroll() {
			var scrollY = window.scrollY;

			// Visibility threshold
			if (scrollY > SCROLL_REVEAL_THRESHOLD) {
				navContainer.classList.add('is-visible');
			} else {
				navContainer.classList.remove('is-visible');
			}

			// Active section determination
			var scrollPos = scrollY + 140;
			var currentActive = navItems[0];

			for (var i = 0; i < navItems.length; i++) {
				var top = navItems[i].element.offsetTop;
				if (scrollPos >= top) {
					currentActive = navItems[i];
				}
			}

			navItems.forEach(function (item) {
				if (item === currentActive) {
					item.navItem.classList.add('is-active');
				} else {
					item.navItem.classList.remove('is-active');
				}
			});

			if (currentActive) {
				autoScrollToActiveItem(currentActive);
			}
		}

		window.addEventListener('scroll', onScroll, { passive: true });
		window.addEventListener('mousemove', resetIdleTimer, { passive: true });
		window.addEventListener('keydown', resetIdleTimer, { passive: true });

		onScroll();
		resetIdleTimer();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

})(window, document);
