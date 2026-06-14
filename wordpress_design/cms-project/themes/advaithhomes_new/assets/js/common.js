/* ============================================================
   common.js - Site chrome behaviour (loaded site-wide).

   Powers the header/navigation/search and footer parts:
     • Mobile menu open/close (+ body scroll lock)
     • Mobile submenu accordions
     • Desktop dropdown keyboard / touch accessibility
     • Site search panel (open, focus, close)
     • Sticky-header shadow on scroll
     • Scroll progress bar
     • Active nav-link highlight
   Vanilla JS, no jQuery dependency.
   ============================================================ */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    }

    ready(function () {
        initMobileMenu();
        initMobileSubmenus();
        initDesktopDropdowns();
        initSearchPanel();
        initSearchSuggest();
        initStickyHeader();
        initScrollProgress();
        initActiveNavLink();
    });

    /* ---------- Mobile menu ---------- */
    function initMobileMenu() {
        var btn = document.querySelector('.mobile-menu-btn');
        var menu = document.getElementById('mobileMenu');
        if (!btn || !menu) { return; }

        function setOpen(open) {
            menu.classList.toggle('open', open);
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            btn.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
            btn.innerHTML = open ? '✕' : '☰';
            /* overlay is position:fixed so background scroll is already blocked */
        }

        btn.addEventListener('click', function () {
            setOpen(!menu.classList.contains('open'));
        });

        // Close after following any in-menu link.
        menu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () { setOpen(false); });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && menu.classList.contains('open')) { setOpen(false); }
        });
    }

    /* ---------- Mobile submenu accordions ---------- */
    function initMobileSubmenus() {
        document.querySelectorAll('.mobile-nav-toggle').forEach(function (toggle) {
            var submenu = toggle.parentNode.querySelector('.mobile-submenu');
            if (!submenu) { return; }
            toggle.addEventListener('click', function () {
                var open = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', open ? 'false' : 'true');
                if (open) {
                    submenu.setAttribute('hidden', '');
                } else {
                    submenu.removeAttribute('hidden');
                }
            });
        });
    }

    /* ---------- Desktop dropdowns (hover via CSS; here: touch + a11y) ---------- */
    function initDesktopDropdowns() {
        var items = document.querySelectorAll('.nav-item.has-dropdown');
        if (!items.length) { return; }

        function closeAll(except) {
            items.forEach(function (item) {
                if (item !== except) {
                    item.classList.remove('open');
                    var l = item.querySelector('.nav-link');
                    if (l) { l.setAttribute('aria-expanded', 'false'); }
                }
            });
        }

        items.forEach(function (item) {
            var link = item.querySelector('.nav-link');
            if (!link) { return; }

            // On coarse pointers the first tap opens the panel instead of navigating.
            link.addEventListener('click', function (e) {
                if (window.matchMedia('(hover: none)').matches && !item.classList.contains('open')) {
                    e.preventDefault();
                    closeAll(item);
                    item.classList.add('open');
                    link.setAttribute('aria-expanded', 'true');
                }
            });

            // Keyboard: open on focus, close when focus leaves the whole item.
            link.addEventListener('focus', function () { closeAll(item); });
            item.addEventListener('focusout', function (e) {
                if (!item.contains(e.relatedTarget)) {
                    item.classList.remove('open');
                    link.setAttribute('aria-expanded', 'false');
                }
            });
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.nav-item.has-dropdown')) { closeAll(null); }
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { closeAll(null); }
        });
    }

    /* ---------- Site search panel ---------- */
    function initSearchPanel() {
        var btn = document.querySelector('.btn-search');
        var panel = document.getElementById('headerSearch');
        if (!btn || !panel) { return; }
        var input = panel.querySelector('.header-search-input');
        var closeBtn = panel.querySelector('.header-search-close');

        function setOpen(open) {
            if (open) {
                panel.removeAttribute('hidden');
            } else {
                panel.setAttribute('hidden', '');
            }
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open && input) { input.focus(); }
        }

        btn.addEventListener('click', function () {
            setOpen(panel.hasAttribute('hidden'));
        });
        if (closeBtn) {
            closeBtn.addEventListener('click', function () { setOpen(false); btn.focus(); });
        }
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !panel.hasAttribute('hidden')) { setOpen(false); btn.focus(); }
        });
        document.addEventListener('click', function (e) {
            if (panel.hasAttribute('hidden')) { return; }
            if (!e.target.closest('#headerSearch') && !e.target.closest('.btn-search')) { setOpen(false); }
        });

        // Don't submit an empty query.
        var form = panel.querySelector('.header-search-form');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (input && !input.value.trim()) { e.preventDefault(); input.focus(); }
            });
        }
    }

    /* ---------- Live search type-ahead (WordPress core REST search) ---------- */
    function initSearchSuggest() {
        document.querySelectorAll('form[data-suggest]').forEach(function (form) {
            var endpoint = form.getAttribute('data-suggest');
            var input = form.querySelector('input[type="search"]');
            var box = form.parentNode.querySelector('.js-suggest');
            if (endpoint && input && box) {
                wireSuggest(input, box, endpoint);
            }
        });
    }

    function decodeEntities(str) {
        var ta = document.createElement('textarea');
        ta.innerHTML = str;
        return ta.value;
    }

    function wireSuggest(input, box, endpoint) {
        var timer = null;
        var controller = null;
        var items = [];
        var active = -1;

        function hide() {
            box.setAttribute('hidden', '');
            box.innerHTML = '';
            items = [];
            active = -1;
            input.setAttribute('aria-expanded', 'false');
        }

        function paint() {
            items.forEach(function (el, i) {
                el.classList.toggle('is-active', i === active);
                if (i === active) { el.scrollIntoView({ block: 'nearest' }); }
            });
        }

        function render(results) {
            if (!results.length) { hide(); return; }
            box.innerHTML = '';
            items = results.map(function (r) {
                var a = document.createElement('a');
                a.className = 'search-suggest-item';
                a.href = r.url;
                a.setAttribute('role', 'option');

                var title = document.createElement('span');
                title.className = 'search-suggest-title';
                title.textContent = decodeEntities(r.title);

                var type = document.createElement('span');
                type.className = 'search-suggest-type';
                type.textContent = r.subtype;

                a.appendChild(title);
                a.appendChild(type);
                box.appendChild(a);
                return a;
            });
            active = -1;
            box.removeAttribute('hidden');
            input.setAttribute('aria-expanded', 'true');
        }

        function fetchSuggest(query) {
            if (!('fetch' in window)) { return; }
            if (controller && controller.abort) { controller.abort(); }
            controller = ('AbortController' in window) ? new AbortController() : null;

            var url = endpoint + (endpoint.indexOf('?') === -1 ? '?' : '&') +
                'search=' + encodeURIComponent(query) +
                '&per_page=7&_fields=title,url,subtype,type';

            fetch(url, {
                signal: controller ? controller.signal : undefined,
                headers: { 'Accept': 'application/json' }
            })
                .then(function (res) { return res.ok ? res.json() : []; })
                .then(function (data) {
                    var list = (Array.isArray(data) ? data : []).map(function (it) {
                        var t = it.title;
                        if (t && typeof t === 'object' && t.rendered) { t = t.rendered; }
                        return {
                            title: typeof t === 'string' ? t : '',
                            url: it.url || '#',
                            subtype: it.subtype || it.type || ''
                        };
                    }).filter(function (it) { return it.title; });
                    render(list);
                })
                .catch(function () { /* aborted or network error - ignore */ });
        }

        input.addEventListener('input', function () {
            var query = input.value.trim();
            if (timer) { clearTimeout(timer); }
            if (query.length < 2) { hide(); return; }
            timer = setTimeout(function () { fetchSuggest(query); }, 200);
        });

        input.addEventListener('keydown', function (e) {
            if (box.hasAttribute('hidden') || !items.length) { return; }
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                active = (active + 1) % items.length;
                paint();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                active = (active - 1 + items.length) % items.length;
                paint();
            } else if (e.key === 'Enter') {
                if (active >= 0 && items[active]) {
                    e.preventDefault();
                    window.location.href = items[active].href;
                }
            } else if (e.key === 'Escape') {
                // First Escape closes only the suggestions, not the whole panel.
                e.stopPropagation();
                hide();
            }
        });

        document.addEventListener('click', function (e) {
            if (e.target !== input && !box.contains(e.target)) { hide(); }
        });
    }

    /* ---------- Sticky-header shadow ---------- */
    function initStickyHeader() {
        var header = document.getElementById('siteHeader');
        if (!header) { return; }
        function onScroll() { header.classList.toggle('is-stuck', window.scrollY > 4); }
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* ---------- Scroll progress bar ---------- */
    function initScrollProgress() {
        var bar = document.querySelector('.scroll-progress');
        if (!bar) { return; }
        window.addEventListener('scroll', function () {
            var docHeight = document.documentElement.scrollHeight - window.innerHeight;
            var pct = docHeight > 0 ? (window.scrollY / docHeight) * 100 : 0;
            bar.style.width = pct + '%';
        }, { passive: true });
    }

    /* ---------- Active nav-link highlight ---------- */
    function initActiveNavLink() {
        var path = window.location.pathname.replace(/\/+$/, '');
        if (!path) { return; }
        document.querySelectorAll('.main-nav .nav-link, .mobile-nav-link').forEach(function (link) {
            var href = link.getAttribute('href');
            if (!href) { return; }
            var linkPath;
            try {
                linkPath = new URL(href, window.location.origin).pathname.replace(/\/+$/, '');
            } catch (err) {
                return;
            }
            if (linkPath && linkPath === path) { link.classList.add('active'); }
        });
    }
})();
