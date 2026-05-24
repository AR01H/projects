/* The Cane House - Main JS */
(function () {
    'use strict';

    // ── Mobile Nav ─────────────────────────────────────────────────────────────
    function initMobileNav() {
        const hamburger = document.getElementById('ch-hamburger');
        const mobileNav = document.getElementById('ch-mobile-nav');
        if (!hamburger || !mobileNav) return;

        hamburger.addEventListener('click', function () {
            const isOpen = mobileNav.classList.toggle('open');
            hamburger.classList.toggle('open', isOpen);
            hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        // Close on direct link click (not summary toggle)
        mobileNav.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function () {
                mobileNav.classList.remove('open');
                hamburger.classList.remove('open');
                hamburger.setAttribute('aria-expanded', 'false');
            });
        });

        // Close on outside click
        document.addEventListener('click', function (e) {
            if (!e.target.closest('#ch-nav') && !e.target.closest('#ch-mobile-nav')) {
                mobileNav.classList.remove('open');
                hamburger.classList.remove('open');
                hamburger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // ── Nav Scroll Effect ──────────────────────────────────────────────────────
    function initNavScroll() {
        const nav = document.getElementById('ch-nav');
        if (!nav) return;
        window.addEventListener('scroll', function () {
            nav.classList.toggle('scrolled', window.scrollY > 60);
        }, { passive: true });
    }

    // ── Scroll Animations (IntersectionObserver) ───────────────────────────────
    function initScrollAnimations() {
        const targets = document.querySelectorAll('.fade-up, .fade-left, .fade-right');
        if (!targets.length) return;

        const observer = new IntersectionObserver(function (entries) {
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
        const track  = document.getElementById('ch-reviews-track');
        const dots   = document.querySelectorAll('#ch-nav-dots .ch-dot');
        const prev   = document.getElementById('ch-rev-prev');
        const next   = document.getElementById('ch-rev-next');
        if (!track) return;

        const cards = track.querySelectorAll('.ch-review-card');
        let current = 0;
        let timer;

        function show(idx) {
            cards.forEach(function (c, i) { c.classList.toggle('active', i === idx); });
            dots.forEach(function (d, i) { d.classList.toggle('active', i === idx); });
            current = idx;
        }

        function advance() {
            show((current + 1) % cards.length);
        }

        function retreat() {
            show((current - 1 + cards.length) % cards.length);
        }

        function resetTimer() {
            clearInterval(timer);
            timer = setInterval(advance, 5000);
        }

        if (next)  next.addEventListener('click',  function () { advance(); resetTimer(); });
        if (prev)  prev.addEventListener('click',  function () { retreat(); resetTimer(); });

        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () { show(i); resetTimer(); });
            dot.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { show(i); resetTimer(); }
            });
        });

        timer = setInterval(advance, 5000);
    }

    // ── Juice Showcase Carousel ────────────────────────────────────────────────
    function initJuiceShowcase() {
        const track = document.getElementById('ch-showcase-track');
        const prev  = document.getElementById('ch-showcase-prev');
        const next  = document.getElementById('ch-showcase-next');
        if (!track) return;

        const cards = track.querySelectorAll('.ch-showcase-card');
        let current = 0;

        function update() {
            const count = cards.length;
            cards.forEach(function (c, i) {
                c.classList.remove('active', 'next', 'prev');
                if (i === current) c.classList.add('active');
                else if (i === (current + 1) % count) c.classList.add('next');
                else if (i === (current - 1 + count) % count) c.classList.add('prev');
            });
        }

        if (next) next.addEventListener('click', function () { current = (current + 1) % cards.length; update(); });
        if (prev) prev.addEventListener('click', function () { current = (current - 1 + cards.length) % cards.length; update(); });

        setInterval(function () { current = (current + 1) % cards.length; update(); }, 4000);
    }

    // ── FAQ Accordion ──────────────────────────────────────────────────────────
    function initFaqAccordion() {
        const items = document.querySelectorAll('.ch-faq-item');
        items.forEach(function (item) {
            const btn = item.querySelector('.ch-faq-question');
            if (!btn) return;
            btn.addEventListener('click', function () {
                const isOpen = item.classList.contains('active');
                // Close all
                items.forEach(function (it) {
                    it.classList.remove('active');
                    const q = it.querySelector('.ch-faq-question');
                    if (q) q.setAttribute('aria-expanded', 'false');
                });
                // Toggle clicked
                if (!isOpen) {
                    item.classList.add('active');
                    btn.setAttribute('aria-expanded', 'true');
                }
            });
        });
    }

    // ── Smooth scroll for anchor links ────────────────────────────────────────
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(function (a) {
            a.addEventListener('click', function (e) {
                const id = a.getAttribute('href').slice(1);
                const el = document.getElementById(id);
                if (el) {
                    e.preventDefault();
                    const navH = document.getElementById('ch-nav')?.offsetHeight || 72;
                    window.scrollTo({ top: el.offsetTop - navH - 8, behavior: 'smooth' });
                }
            });
        });
    }

    // ── Adaptive Nav - desktop menu if items fit, hamburger (3-lines) if not ──
    function initNavPriority() {
        var nav = document.getElementById('ch-nav');
        if (!nav) return;

        var mobileNav  = document.getElementById('ch-mobile-nav');
        var hamburger  = document.getElementById('ch-hamburger');

        function adjust() {
            var body = document.body;

            // Reset to desktop state so we can measure accurately
            body.classList.remove('nav--collapsed');
            void nav.offsetWidth; // force reflow

            if (nav.scrollWidth <= nav.clientWidth + 2) {
                // All items fit - stay in desktop mode
                return;
            }

            // Doesn't fit - switch straight to hamburger
            body.classList.add('nav--collapsed');
            if (mobileNav) mobileNav.classList.remove('open');
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

    // ── Init ───────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        initMobileNav();
        initNavScroll();
        initNavPriority();
        initScrollAnimations();
        initReviewCarousel();
        initJuiceShowcase();
        initFaqAccordion();
        initSmoothScroll();
    });

})();
