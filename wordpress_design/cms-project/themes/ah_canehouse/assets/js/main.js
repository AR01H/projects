/* The Cane House - Main JS */
(function () {
    'use strict';

    // ── Mobile Nav ─────────────────────────────────────────────────────────────
    function initMobileNav() {
        var hamburger = document.getElementById('ch-hamburger');
        var mobileNav = document.getElementById('ch-mobile-nav');
        if (!hamburger || !mobileNav) return;

        function openNav() {
            mobileNav.classList.add('open');
            hamburger.classList.add('open');
            hamburger.setAttribute('aria-expanded', 'true');
        }
        function closeNav() {
            mobileNav.classList.remove('open');
            hamburger.classList.remove('open');
            hamburger.setAttribute('aria-expanded', 'false');
        }

        hamburger.addEventListener('click', function () {
            if (mobileNav.classList.contains('open')) {
                closeNav();
            } else {
                openNav();
            }
        });

        // Close on link click
        mobileNav.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', closeNav);
        });

        // Close outside click
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#ch-nav') && !e.target.closest('#ch-mobile-nav')) {
                closeNav();
            }
        });
    }

    // ── Nav Scroll Effect ──────────────────────────────────────────────────────
    function initNavScroll() {
        var nav = document.getElementById('ch-nav');
        if (!nav) return;
        window.addEventListener('scroll', function () {
            nav.classList.toggle('scrolled', window.scrollY > 60);
        }, { passive: true });
    }

    // ── Adaptive Nav (collapse to hamburger when items don't fit) ─────────────
    function initNavPriority() {
        var nav = document.getElementById('ch-nav');
        if (!nav) return;

        var mobileNav = document.getElementById('ch-mobile-nav');
        var hamburger = document.getElementById('ch-hamburger');
        var links     = document.getElementById('ch-nav-links');

        function adjust() {
            var body = document.body;
            // Reset to desktop so we can measure
            body.classList.remove('nav--collapsed');
            if (links) links.style.display = '';
            void nav.offsetWidth;

            var inner = nav.querySelector('.ch-nav__inner');
            if (!inner) return;

            if (inner.scrollWidth <= inner.clientWidth + 2) return;

            // Doesn't fit — collapse
            body.classList.add('nav--collapsed');
            if (mobileNav && !mobileNav.classList.contains('open')) mobileNav.classList.remove('open');
            if (hamburger) {
                hamburger.classList.remove('open');
                hamburger.setAttribute('aria-expanded', 'false');
            }
        }

        var resizeTimer;
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(adjust, 60);
        }, { passive: true });
        adjust();
    }

    // ── Search Panel ───────────────────────────────────────────────────────────
    function initSearchPanel() {
        var toggleBtn = document.getElementById('ch-search-toggle');
        var panel     = document.getElementById('ch-search-panel');
        var closeBtn  = document.getElementById('ch-search-close');
        var input     = document.getElementById('ch-search-input');
        if (!toggleBtn || !panel) return;

        function openPanel() {
            panel.classList.add('is-open');
            panel.setAttribute('aria-hidden', 'false');
            toggleBtn.classList.add('is-active');
            toggleBtn.setAttribute('aria-expanded', 'true');
            if (input) setTimeout(function () { input.focus(); }, 120);
        }
        function closePanel() {
            panel.classList.remove('is-open');
            panel.setAttribute('aria-hidden', 'true');
            toggleBtn.classList.remove('is-active');
            toggleBtn.setAttribute('aria-expanded', 'false');
        }

        toggleBtn.addEventListener('click', function () {
            panel.classList.contains('is-open') ? closePanel() : openPanel();
        });

        if (closeBtn) closeBtn.addEventListener('click', closePanel);

        // ESC key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closePanel();
        });

        // Search input — redirect to WP search
        if (input) {
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' && input.value.trim()) {
                    var q = encodeURIComponent(input.value.trim());
                    window.location.href = (typeof chTheme !== 'undefined' ? chTheme.siteUrl : '/') + '?s=' + q;
                }
            });
        }
    }

    // ── Scroll Animations (IntersectionObserver) ───────────────────────────────
    function initScrollAnimations() {
        var targets = document.querySelectorAll('.fade-up, .fade-left, .fade-right');
        if (!targets.length) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        targets.forEach(function (el) { observer.observe(el); });
    }

    // ── Review Carousel ────────────────────────────────────────────────────────
    function initReviewCarousel() {
        var track = document.getElementById('ch-reviews-track');
        var prev  = document.getElementById('ch-rev-prev');
        var next  = document.getElementById('ch-rev-next');
        if (!track) return;

        var cards   = track.querySelectorAll('.ch-review-card');
        var current = 0;
        var timer;

        function getDots() {
            return document.querySelectorAll('#ch-nav-dots .ch-dot');
        }

        function show(idx) {
            cards.forEach(function (c, i) {
                c.classList.toggle('active', i === idx);
            });
            getDots().forEach(function (d, i) {
                d.classList.toggle('active', i === idx);
                d.setAttribute('aria-selected', i === idx ? 'true' : 'false');
            });
            current = idx;
        }
        function advance()    { show((current + 1) % cards.length); }
        function retreat()    { show((current - 1 + cards.length) % cards.length); }
        function resetTimer() { clearInterval(timer); timer = setInterval(advance, 6000); }

        if (next) next.addEventListener('click', function () { advance(); resetTimer(); });
        if (prev) prev.addEventListener('click', function () { retreat(); resetTimer(); });

        getDots().forEach(function (dot, i) {
            dot.addEventListener('click', function () { show(i); resetTimer(); });
        });

        timer = setInterval(advance, 6000);
    }

    // ── Juice Showcase (3D coverflow) ─────────────────────────────────────────
    function initJuiceShowcase() {
        var track = document.getElementById('ch-showcase-track');
        var prev  = document.getElementById('ch-showcase-prev');
        var next  = document.getElementById('ch-showcase-next');
        if (!track) return;

        var cards   = track.querySelectorAll('.ch-showcase-card');
        var current = 0;

        function update() {
            var count = cards.length;
            cards.forEach(function (c, i) {
                c.classList.remove('active', 'next', 'prev');
                if (i === current) c.classList.add('active');
                else if (i === (current + 1) % count) c.classList.add('next');
                else if (i === (current - 1 + count) % count) c.classList.add('prev');
            });
        }

        if (next) next.addEventListener('click', function () { current = (current + 1) % cards.length; update(); });
        if (prev) prev.addEventListener('click', function () { current = (current - 1 + cards.length) % cards.length; update(); });

        var autoTimer = setInterval(function () { current = (current + 1) % cards.length; update(); }, 4000);
        // Pause on hover
        track.addEventListener('mouseenter', function () { clearInterval(autoTimer); });
        track.addEventListener('mouseleave', function () { autoTimer = setInterval(function () { current = (current + 1) % cards.length; update(); }, 4000); });
    }

    // ── FAQ Accordion ──────────────────────────────────────────────────────────
    function initFaqAccordion() {
        var items = document.querySelectorAll('.ch-faq-item');
        items.forEach(function (item) {
            var btn = item.querySelector('.ch-faq-question');
            if (!btn) return;
            btn.addEventListener('click', function () {
                var isOpen = item.classList.contains('active');
                items.forEach(function (it) {
                    it.classList.remove('active');
                    var q = it.querySelector('.ch-faq-question');
                    if (q) q.setAttribute('aria-expanded', 'false');
                });
                if (!isOpen) {
                    item.classList.add('active');
                    btn.setAttribute('aria-expanded', 'true');
                }
            });
        });
    }

    // ── Footer Accordion (mobile only) ────────────────────────────────────────
    function initFooterAccordion() {
        var toggles = document.querySelectorAll('.ch-footer__acc-toggle');
        toggles.forEach(function (toggle) {
            var body = toggle.parentElement.querySelector('.ch-footer__acc-body');
            if (!body) return;

            toggle.addEventListener('click', function () {
                var isOpen = toggle.getAttribute('aria-expanded') === 'true';
                toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
                body.classList.toggle('is-open', !isOpen);
            });
        });
    }

    // ── Smooth scroll for anchor links ────────────────────────────────────────
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                var id = a.getAttribute('href').slice(1);
                var el = document.getElementById(id);
                if (el) {
                    e.preventDefault();
                    var navH = document.getElementById('ch-nav') ? document.getElementById('ch-nav').offsetHeight : 72;
                    window.scrollTo({ top: el.offsetTop - navH - 8, behavior: 'smooth' });
                }
            });
        });
    }

    // ── Story Cards (tabbed reveal) ────────────────────────────────────────────
    function initStoryCards() {
        var section = document.getElementById('story-cards');
        if (!section) return;

        var tabs   = Array.from(section.querySelectorAll('.ch-sc-tab'));
        var panels = Array.from(section.querySelectorAll('.ch-sc-panel'));
        var dots   = Array.from(section.querySelectorAll('.ch-sc-progress-dot'));
        var timer;

        function show(idx) {
            tabs.forEach(function(t, i) {
                t.classList.toggle('active', i === idx);
                t.setAttribute('aria-selected', i === idx ? 'true' : 'false');
            });
            panels.forEach(function(p, i) {
                if (i === idx) {
                    p.classList.add('active');
                    // Re-trigger fade animation
                    p.style.animation = 'none';
                    void p.offsetWidth;
                    p.style.animation = '';
                } else {
                    p.classList.remove('active');
                }
            });
            dots.forEach(function(d, i) { d.classList.toggle('active', i === idx); });
        }

        function current() {
            return tabs.findIndex(function(t) { return t.classList.contains('active'); });
        }

        function startTimer() {
            clearInterval(timer);
            timer = setInterval(function() { show((current() + 1) % tabs.length); }, 6000);
        }

        tabs.forEach(function(tab, i) {
            tab.addEventListener('click', function() { show(i); startTimer(); });
        });
        dots.forEach(function(dot, i) {
            dot.addEventListener('click', function() { show(i); startTimer(); });
        });

        section.addEventListener('mouseenter', function() { clearInterval(timer); });
        section.addEventListener('mouseleave', startTimer);

        startTimer();
    }

    // ── Init ───────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        initNavScroll();
        initNavPriority();
        initMobileNav();
        initSearchPanel();
        initScrollAnimations();
        initReviewCarousel();
        initJuiceShowcase();
        initFaqAccordion();
        initFooterAccordion();
        initSmoothScroll();
        initStoryCards();
    });

})();
