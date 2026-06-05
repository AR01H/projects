/* The Cane House - Main JS */
(function () {
    'use strict';

    // ── Swipe gesture helper ────────────────────────────────────────────────────
    // Attaches touch swipe detection to an element.
    // onLeft  = user swiped finger right→left (means "next")
    // onRight = user swiped finger left→right (means "previous")
    function addSwipe(el, onLeft, onRight) {
        if (!el) return;
        var startX = 0, startY = 0, tracking = false;

        el.addEventListener('touchstart', function (e) {
            var t = e.changedTouches[0];
            startX = t.clientX;
            startY = t.clientY;
            tracking = true;
        }, { passive: true });

        el.addEventListener('touchend', function (e) {
            if (!tracking) return;
            tracking = false;
            var t  = e.changedTouches[0];
            var dx = t.clientX - startX;
            var dy = t.clientY - startY;
            // Require a clear horizontal swipe (min 40px, mostly sideways)
            if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy) * 1.4) {
                if (dx < 0) { onLeft && onLeft(); }
                else        { onRight && onRight(); }
            }
        }, { passive: true });
    }

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

            // Doesn't fit - collapse
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

        // Search input - redirect to WP search
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

        // Swipe support
        addSwipe(track,
            function () { advance(); resetTimer(); },
            function () { retreat(); resetTimer(); }
        );

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

        function goNext() { current = (current + 1) % cards.length; update(); }
        function goPrev() { current = (current - 1 + cards.length) % cards.length; update(); }

        if (next) next.addEventListener('click', goNext);
        if (prev) prev.addEventListener('click', goPrev);

        // Swipe support
        addSwipe(track, goNext, goPrev);

        var autoTimer = setInterval(function () { current = (current + 1) % cards.length; update(); }, 4000);
        // Pause on hover
        track.addEventListener('mouseenter', function () { clearInterval(autoTimer); });
        track.addEventListener('mouseleave', function () { autoTimer = setInterval(function () { current = (current + 1) % cards.length; update(); }, 4000); });
    }

    // ── Hire Packages Carousel (removed in favor of generic CHCarousel) ────────
    // ── Certifications Carousel ───────────────────────────────────────────────
    function initCertsCarousel() {
        var track = document.getElementById('ch-certs-track');
        var prev  = document.getElementById('ch-certs-prev');
        var next  = document.getElementById('ch-certs-next');
        if (!track) return;

        var cards   = track.querySelectorAll('.ch-cert-card');
        var current = 0;

        function getDots() {
            return document.querySelectorAll('#ch-certs-dots .ch-dot');
        }

        function show(idx) {
            cards.forEach(function (c, i) { c.classList.toggle('active', i === idx); });
            getDots().forEach(function (d, i) {
                d.classList.toggle('active', i === idx);
                d.setAttribute('aria-selected', i === idx ? 'true' : 'false');
            });
            current = idx;
        }
        function advance() { show((current + 1) % cards.length); }
        function retreat() { show((current - 1 + cards.length) % cards.length); }

        if (next) next.addEventListener('click', advance);
        if (prev) prev.addEventListener('click', retreat);

        getDots().forEach(function (dot, i) {
            dot.addEventListener('click', function () { show(i); });
        });

        addSwipe(track, advance, retreat);

        // Auto-rotate every 5 s
        var timer = setInterval(advance, 5000);
        track.addEventListener('mouseenter', function () { clearInterval(timer); });
        track.addEventListener('mouseleave', function () { timer = setInterval(advance, 5000); });
    }

    // ── Event Packages Carousel (events page) ────────────────────────────────
    function initPkgCarousel() {
        var track = document.getElementById('ch-pkg-track');
        var prev  = document.getElementById('ch-pkg-prev');
        var next  = document.getElementById('ch-pkg-next');
        if (!track) return;

        var cards   = track.querySelectorAll('.ch-package-card');
        var current = 0;

        function getDots() { return document.querySelectorAll('#ch-pkg-dots .ch-dot'); }

        function show(idx) {
            cards.forEach(function (c, i) { c.classList.toggle('active', i === idx); });
            getDots().forEach(function (d, i) {
                d.classList.toggle('active', i === idx);
                d.setAttribute('aria-selected', i === idx ? 'true' : 'false');
            });
            current = idx;
            if (window.innerWidth > 767) {
                cards[idx].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
            }
        }
        var raf;
        track.addEventListener('scroll', function() {
            if (window.innerWidth <= 767) return;
            if (raf) cancelAnimationFrame(raf);
            raf = requestAnimationFrame(function() {
                var cardWidth = cards[0].offsetWidth + 32;
                var idx = Math.round(track.scrollLeft / cardWidth);
                if (idx !== current && idx >= 0 && idx < cards.length) {
                    current = idx;
                    getDots().forEach(function (d, i) {
                        d.classList.toggle('active', i === idx);
                        d.setAttribute('aria-selected', i === idx ? 'true' : 'false');
                    });
                }
            });
        }, { passive: true });
        function advance() { show((current + 1) % cards.length); }
        function retreat() { show((current - 1 + cards.length) % cards.length); }

        if (next) next.addEventListener('click', advance);
        if (prev) prev.addEventListener('click', retreat);
        getDots().forEach(function (dot, i) { dot.addEventListener('click', function () { show(i); }); });
        addSwipe(track, advance, retreat);
    }

    // ── Homepage Events Preview Carousel ─────────────────────────────────────
    function initEpcCarousel() {
        var track = document.getElementById('ch-epc-track');
        var prev  = document.getElementById('ch-epc-prev');
        var next  = document.getElementById('ch-epc-next');
        if (!track) return;

        var cards   = track.querySelectorAll('.ch-epc');
        var current = 0;

        function getDots() { return document.querySelectorAll('#ch-epc-dots .ch-dot'); }

        function show(idx) {
            cards.forEach(function (c, i) { c.classList.toggle('active', i === idx); });
            getDots().forEach(function (d, i) {
                d.classList.toggle('active', i === idx);
                d.setAttribute('aria-selected', i === idx ? 'true' : 'false');
            });
            current = idx;
        }
        function advance() { show((current + 1) % cards.length); }
        function retreat() { show((current - 1 + cards.length) % cards.length); }

        if (next) next.addEventListener('click', advance);
        if (prev) prev.addEventListener('click', retreat);
        getDots().forEach(function (dot, i) { dot.addEventListener('click', function () { show(i); }); });
        addSwipe(track, advance, retreat);
    }

    // ── Franchise Why / Steps / Reviews carousels ────────────────────────────
    function makeFranchiseCarousel(trackId, prevId, nextId, dotsId, cardSel) {
        var track = document.getElementById(trackId);
        var prev  = document.getElementById(prevId);
        var next  = document.getElementById(nextId);
        if (!track) return;
        var cards   = track.querySelectorAll(cardSel);
        var current = 0;
        function getDots() { return document.querySelectorAll('#' + dotsId + ' .ch-dot'); }
        function show(idx) {
            cards.forEach(function (c, i) { c.classList.toggle('active', i === idx); });
            getDots().forEach(function (d, i) {
                d.classList.toggle('active', i === idx);
                d.setAttribute('aria-selected', i === idx ? 'true' : 'false');
            });
            current = idx;
        }
        function advance() { show((current + 1) % cards.length); }
        function retreat() { show((current - 1 + cards.length) % cards.length); }
        if (next) next.addEventListener('click', advance);
        if (prev) prev.addEventListener('click', retreat);
        getDots().forEach(function (dot, i) { dot.addEventListener('click', function () { show(i); }); });
        addSwipe(track, advance, retreat);
    }
    function initFranchiseCarousels() {
        makeFranchiseCarousel('ch-fwhy-track',  'ch-fwhy-prev',  'ch-fwhy-next',  'ch-fwhy-dots',  '.ch-fw-card');
        makeFranchiseCarousel('ch-fstep-track', 'ch-fstep-prev', 'ch-fstep-next', 'ch-fstep-dots', '.ch-step-card');
        makeFranchiseCarousel('ch-rfr-track',   'ch-rfr-prev',   'ch-rfr-next',   'ch-rfr-dots',   '.ch-rfr-card');
    }

    // ── Gallery Strip Carousels (mobile) ─────────────────────────────────────
    function initGalleryStrips() {
        document.querySelectorAll('.ch-gstrip').forEach(function (gstrip) {
            var id    = gstrip.getAttribute('data-id');
            var track = document.getElementById(id + '-track');
            var prev  = document.getElementById(id + '-prev');
            var next  = document.getElementById(id + '-next');
            if (!track) return;

            var cards   = track.querySelectorAll('.ch-gstrip__card');
            var current = 0;

            function getDots() { return document.querySelectorAll('#' + id + '-dots .ch-dot'); }

            function show(idx) {
                cards.forEach(function (c, i) { c.classList.toggle('active', i === idx); });
                getDots().forEach(function (d, i) {
                    d.classList.toggle('active', i === idx);
                    d.setAttribute('aria-selected', i === idx ? 'true' : 'false');
                });
                current = idx;
            }
            function advance() { show((current + 1) % cards.length); }
            function retreat() { show((current - 1 + cards.length) % cards.length); }

            if (next) next.addEventListener('click', advance);
            if (prev) prev.addEventListener('click', retreat);
            getDots().forEach(function (dot, i) { dot.addEventListener('click', function () { show(i); }); });
            addSwipe(track, advance, retreat);
        });
    }

    // ── Events Reviews Carousel ───────────────────────────────────────────────
    function initRevEvCarousel() {
        var track = document.getElementById('ch-rev-ev-track');
        var prev  = document.getElementById('ch-rev-ev-prev');
        var next  = document.getElementById('ch-rev-ev-next');
        if (!track) return;

        var cards   = track.querySelectorAll('.ch-rev-ev-card');
        var current = 0;

        function getDots() { return document.querySelectorAll('#ch-rev-ev-dots .ch-dot'); }

        function show(idx) {
            cards.forEach(function (c, i) { c.classList.toggle('active', i === idx); });
            getDots().forEach(function (d, i) {
                d.classList.toggle('active', i === idx);
                d.setAttribute('aria-selected', i === idx ? 'true' : 'false');
            });
            current = idx;
        }
        function advance() { show((current + 1) % cards.length); }
        function retreat() { show((current - 1 + cards.length) % cards.length); }

        if (next) next.addEventListener('click', advance);
        if (prev) prev.addEventListener('click', retreat);
        getDots().forEach(function (dot, i) { dot.addEventListener('click', function () { show(i); }); });
        addSwipe(track, advance, retreat);
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

        function nextCard() { show((current() + 1) % tabs.length); startTimer(); }
        function prevCard() { show((current() - 1 + tabs.length) % tabs.length); startTimer(); }

        // Swipe to change the STORY (card) - only on the text content and
        // any non-gallery visual. A multi-image gallery handles its own swipe
        // (changes the image, not the card).
        section.querySelectorAll('.ch-sc-panel').forEach(function(panel) {
            var content = panel.querySelector('.ch-sc-panel-content');
            var visual  = panel.querySelector('.ch-sc-panel-visual');
            var gallery = visual && visual.querySelector('.ch-sc-gallery');

            if (content) addSwipe(content, nextCard, prevCard);
            if (visual && !gallery) addSwipe(visual, nextCard, prevCard);
        });

        startTimer();
    }

    // ── Story Card Galleries (multi-image crossfade) ───────────────────────────
    function initStoryGalleries() {
        document.querySelectorAll('.ch-sc-gallery').forEach(function (gallery) {
            var imgs = Array.prototype.slice.call(gallery.querySelectorAll('.ch-sc-gallery-img'));
            var dots = Array.prototype.slice.call(gallery.querySelectorAll('.ch-sc-gallery-dot'));
            if (imgs.length < 2) return;

            var idx = 0, timer;

            function show(i) {
                idx = (i + imgs.length) % imgs.length;
                imgs.forEach(function (im, n) { im.classList.toggle('active', n === idx); });
                dots.forEach(function (d, n)  { d.classList.toggle('active', n === idx); });
            }
            function start() { clearInterval(timer); timer = setInterval(function () { show(idx + 1); }, 3500); }

            dots.forEach(function (dot, n) {
                dot.addEventListener('click', function () { show(n); start(); });
            });

            // Pause on hover (desktop), swipe support (mobile)
            gallery.addEventListener('mouseenter', function () { clearInterval(timer); });
            gallery.addEventListener('mouseleave', start);
            addSwipe(gallery, function () { show(idx + 1); start(); }, function () { show(idx - 1); start(); });

            start();
        });
    }

    // ── Scroll to Top Button ──────────────────────────────────────────────────
    function initScrollToTop() {
        var btn = document.getElementById('ch-scroll-to-top');
        var fill = document.querySelector('.ch-scroll-glass__fill');
        if (!btn) return;

        function updateScrollProgress() {
            // Calculate scroll percentage
            var scrollTop = window.scrollY;
            var docHeight = document.documentElement.scrollHeight - window.innerHeight;
            var scrollPercent = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;

            // Show button when scrolled down
            if (scrollTop > 300) {
                btn.classList.add('visible');
            } else {
                btn.classList.remove('visible');
            }

            // Update juice fill height (max 16px, scales with scroll)
            if (fill) {
                var maxFillHeight = 16;
                var fillHeight = (scrollPercent / 100) * maxFillHeight;
                fill.style.height = fillHeight + 'px';
                fill.style.opacity = scrollPercent > 5 ? 1 : scrollPercent / 5;
            }
        }

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        window.addEventListener('scroll', updateScrollProgress, { passive: true });
        btn.addEventListener('click', scrollToTop);

        // Initial call
        updateScrollProgress();
    }

    // ── Post Gallery Carousel ─────────────────────────────────────────────────
    function initPostGalleries() {
        document.querySelectorAll('.ch-post-gallery').forEach(function (gallery) {
            var id     = gallery.getAttribute('id');
            var track  = gallery.querySelector('.ch-post-gallery__track');
            var slides = gallery.querySelectorAll('.ch-post-gallery__slide');
            var dots   = gallery.querySelectorAll('.ch-post-gallery__dot');
            var arrows = gallery.querySelectorAll('.ch-post-gallery__arrow');

            if (!track || slides.length < 2) return;

            var current = 0;

            function show(idx) {
                current = (idx + slides.length) % slides.length;
                slides.forEach(function (s, i) { s.classList.toggle('active', i === current); });
                dots.forEach(function (d, i) { d.classList.toggle('active', i === current); });
            }

            function advance() { show(current + 1); }
            function retreat() { show(current - 1); }

            // Dots and arrows
            dots.forEach(function (dot, i) {
                dot.addEventListener('click', function () { show(i); });
            });
            arrows.forEach(function (arrow) {
                var isNext = arrow.classList.contains('ch-post-gallery__arrow--next');
                arrow.addEventListener('click', isNext ? advance : retreat);
            });

            // Swipe support
            addSwipe(track, advance, retreat);
        });
    }

    // ── Privacy Policy Modal ───────────────────────────────────────────────────
    function initPrivacyModal() {
        var modal   = document.getElementById('ch-pp-modal');
        var overlay = document.getElementById('ch-pp-overlay');
        if (!modal) return;

        var lastTrigger = null;

        function openModal(triggerEl) {
            lastTrigger = triggerEl || null;
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            var firstClose = modal.querySelector('.ch-pp-close');
            if (firstClose) setTimeout(function () { firstClose.focus(); }, 50);
        }
        function closeModal() {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            if (lastTrigger) lastTrigger.focus();
        }

        // All triggers on the page (class or id)
        document.addEventListener('click', function (e) {
            var t = e.target.closest('#ch-pp-trigger, .ch-pp-trigger');
            if (t) { e.preventDefault(); openModal(t); }
        });

        if (overlay) overlay.addEventListener('click', closeModal);
        modal.querySelectorAll('.ch-pp-close').forEach(function (btn) {
            btn.addEventListener('click', closeModal);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
        });
    }

    // ── Generic one-card carousel (data-attribute driven, reusable) ─────────────
    // Markup:
    //   <div data-oc data-oc-autoplay="4000">
    //     <div data-oc-track> <slide/> <slide/> … </div>
    //     <div data-oc-dots> <button data-go="0"></button> … </div>   (optional)
    //     <button data-oc-prev></button> <button data-oc-next></button> (optional)
    //   </div>
    // The slides are the direct children of [data-oc-track]. Autoplay runs only
    // while the track actually overflows (i.e. the mobile carousel is active).
    function initOneCardCarousels() {
        document.querySelectorAll('[data-oc]').forEach(function (root) {
            if (root.dataset.ocInit) return;
            var track = root.querySelector('[data-oc-track]');
            if (!track) return;
            var items = Array.prototype.slice.call(track.children);
            if (items.length < 2) return;
            root.dataset.ocInit = '1';

            var dots = Array.prototype.slice.call(root.querySelectorAll('[data-oc-dots] [data-go]'));
            var prev = root.querySelector('[data-oc-prev]');
            var next = root.querySelector('[data-oc-next]');
            var auto = parseInt(root.getAttribute('data-oc-autoplay') || '0', 10) || 0;
            var cur = 0, timer = null;

            function step()    { return (items[1].offsetLeft - items[0].offsetLeft) || track.clientWidth; }
            function isOn()    { return track.scrollWidth > track.clientWidth + 4; }
            function setDots() { dots.forEach(function (d, k) { d.classList.toggle('active', k === cur); }); }
            function go(i, smooth) {
                cur = (i + items.length) % items.length;
                track.scrollTo({ left: items[cur].offsetLeft - items[0].offsetLeft, behavior: smooth === false ? 'auto' : 'smooth' });
                setDots();
            }
            function start() { stop(); if (auto > 0 && isOn()) timer = setInterval(function () { go(cur + 1); }, auto); }
            function stop()  { if (timer) { clearInterval(timer); timer = null; } }

            if (prev) prev.addEventListener('click', function () { go(cur - 1); start(); });
            if (next) next.addEventListener('click', function () { go(cur + 1); start(); });
            dots.forEach(function (d, i) { d.addEventListener('click', function () { go(i); start(); }); });

            var raf;
            track.addEventListener('scroll', function () {
                if (raf) cancelAnimationFrame(raf);
                raf = requestAnimationFrame(function () {
                    cur = Math.max(0, Math.min(items.length - 1, Math.round(track.scrollLeft / step())));
                    setDots();
                });
            }, { passive: true });

            ['pointerdown', 'touchstart', 'mouseenter'].forEach(function (e) { track.addEventListener(e, stop, { passive: true }); });
            ['pointerup', 'touchend', 'mouseleave'].forEach(function (e) { track.addEventListener(e, start, { passive: true }); });
            window.addEventListener('resize', function () { setDots(); start(); });

            setDots();
            start();
        });
    }

    // ── Init ───────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        initOneCardCarousels();
        initNavScroll();
        initNavPriority();
        initMobileNav();
        initSearchPanel();
        initScrollAnimations();
        initScrollToTop();
        initReviewCarousel();
        // initHireCarousel(); removed in favor of CHCarousel
        initCertsCarousel();
        initPkgCarousel();
        initEpcCarousel();
        initRevEvCarousel();
        initFranchiseCarousels();
        initGalleryStrips();
        initPostGalleries();
        initJuiceShowcase();
        initFaqAccordion();
        initFooterAccordion();
        initSmoothScroll();
        initStoryCards();
        initStoryGalleries();
        initPrivacyModal();
    });

})();

// (function () {
//   var style = document.createElement('style');
//   style.textContent = '* { cursor: none !important; }';
//   document.head.appendChild(style);

//   var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
//   svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
//   svg.setAttribute('width', '18');
//   svg.setAttribute('height', '56');
//   svg.setAttribute('viewBox', '0 0 18 56');
//   svg.style.cssText = [
//     'position:fixed',
//     'pointer-events:none',
//     'z-index:999999',
//     'top:0',
//     'left:0',
//     'transform:translate(-50%,-95%) rotate(-15deg)',
//   ].join(';');

//   svg.innerHTML = `
//     <defs>
//       <linearGradient id="cg" x1="0%" y1="0%" x2="100%" y2="0%">
//         <stop offset="0%"  stop-color="#4a8c3f"/>
//         <stop offset="45%" stop-color="#6db560"/>
//         <stop offset="100%" stop-color="#3d7535"/>
//       </linearGradient>
//     </defs>
//     <rect x="6" y="3" width="6" height="51" rx="3" fill="url(#cg)"/>
//     <rect x="5" y="14" width="8" height="3" rx="1.5" fill="#3a6e30"/>
//     <rect x="5" y="28" width="8" height="3" rx="1.5" fill="#3a6e30"/>
//     <rect x="5" y="42" width="8" height="3" rx="1.5" fill="#3a6e30"/>
//     <line x1="9" y1="14" x2="2"  y2="7"  stroke="#5cb854" stroke-width="1.2" stroke-linecap="round"/>
//     <ellipse cx="1"  cy="6"  rx="3" ry="1.5" fill="#7dd670" transform="rotate(-30 1 6)"/>
//     <line x1="9" y1="28" x2="16" y2="21" stroke="#5cb854" stroke-width="1.2" stroke-linecap="round"/>
//     <ellipse cx="17" cy="20" rx="3" ry="1.5" fill="#7dd670" transform="rotate(30 17 20)"/>
//     <line x1="9" y1="42" x2="2"  y2="35" stroke="#5cb854" stroke-width="1.2" stroke-linecap="round"/>
//     <ellipse cx="1"  cy="34" rx="3" ry="1.5" fill="#7dd670" transform="rotate(-30 1 34)"/>
//     <line x1="9" y1="3"  x2="4"  y2="0"  stroke="#5cb854" stroke-width="1.2" stroke-linecap="round"/>
//     <ellipse cx="3"  cy="-1" rx="3.5" ry="1.5" fill="#9ee897" transform="rotate(-20 3 -1)"/>
//     <line x1="9" y1="3"  x2="14" y2="0"  stroke="#5cb854" stroke-width="1.2" stroke-linecap="round"/>
//     <ellipse cx="15" cy="-1" rx="3.5" ry="1.5" fill="#9ee897" transform="rotate(20 15 -1)"/>
//   `;

//   document.body.appendChild(svg);

//   var mx = -300, my = -300, cx = -300, cy = -300;
//   var angle = -15, targetAngle = -15, prevX = -300;

//   document.addEventListener('mousemove', function (e) {
//     var dx = e.clientX - prevX;
//     targetAngle = -15 + Math.max(-22, Math.min(22, dx * 1.8));
//     prevX = e.clientX;
//     mx = e.clientX;
//     my = e.clientY;
//   });

//   (function loop() {
//     cx += (mx - cx) * 0.18;
//     cy += (my - cy) * 0.18;
//     angle += (targetAngle - angle) * 0.12;
//     targetAngle += (-15 - targetAngle) * 0.06;
//     svg.style.left = cx + 'px';
//     svg.style.top  = cy + 'px';
//     svg.style.transform =
//       'translate(-50%,-95%) rotate(' + angle.toFixed(2) + 'deg)';
//     requestAnimationFrame(loop);
//   })();
// })();

// (function(){
//   var st=document.createElement('style');
//   st.textContent='*{cursor:none!important;}';
//   document.head.appendChild(st);

//   var svg=document.createElementNS('http://www.w3.org/2000/svg','svg');
//   svg.setAttribute('width','18');
//   svg.setAttribute('height','56');
//   svg.setAttribute('viewBox','0 0 18 56');
//   svg.style.cssText='position:fixed;pointer-events:none;z-index:2147483647;top:0;left:0;will-change:transform;';
//   svg.innerHTML='<defs><linearGradient id="g1" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" stop-color="#4a8c3f"/><stop offset="50%" stop-color="#6db560"/><stop offset="100%" stop-color="#3d7535"/></linearGradient></defs><rect x="6" y="3" width="6" height="51" rx="3" fill="url(#g1)"/><rect x="5" y="14" width="8" height="3" rx="1.5" fill="#3a6e30"/><rect x="5" y="28" width="8" height="3" rx="1.5" fill="#3a6e30"/><rect x="5" y="42" width="8" height="3" rx="1.5" fill="#3a6e30"/><line x1="9" y1="14" x2="2" y2="7" stroke="#5cb854" stroke-width="1.2" stroke-linecap="round"/><ellipse cx="1" cy="6" rx="3" ry="1.5" fill="#7dd670" transform="rotate(-30 1 6)"/><line x1="9" y1="28" x2="16" y2="21" stroke="#5cb854" stroke-width="1.2" stroke-linecap="round"/><ellipse cx="17" cy="20" rx="3" ry="1.5" fill="#7dd670" transform="rotate(30 17 20)"/><line x1="9" y1="42" x2="2" y2="35" stroke="#5cb854" stroke-width="1.2" stroke-linecap="round"/><ellipse cx="1" cy="34" rx="3" ry="1.5" fill="#7dd670" transform="rotate(-30 1 34)"/><line x1="9" y1="3" x2="4" y2="0" stroke="#5cb854" stroke-width="1.2" stroke-linecap="round"/><ellipse cx="3" cy="-1" rx="3.5" ry="1.5" fill="#9ee897" transform="rotate(-20 3 -1)"/><line x1="9" y1="3" x2="14" y2="0" stroke="#5cb854" stroke-width="1.2" stroke-linecap="round"/><ellipse cx="15" cy="-1" rx="3.5" ry="1.5" fill="#9ee897" transform="rotate(20 15 -1)"/>';
//   document.body.appendChild(svg);

//   var mx=-300,my=-300,cx=-300,cy=-300;
//   var angle=-15,ta=-15,px=-300,run=false;
//   document.addEventListener('mousemove',function(e){
//     var dx=e.clientX-px;
//     ta=-15+Math.max(-22,Math.min(22,dx*1.8));
//     px=e.clientX; mx=e.clientX; my=e.clientY;
//     if(!run){run=true;loop();}
//   });
//   function loop(){
//     cx+=(mx-cx)*0.18; cy+=(my-cy)*0.18;
//     angle+=(ta-angle)*0.12; ta+=(-15-ta)*0.06;
//     svg.style.transform='translate3d('+(cx-9)+ 'px,'+(cy-53)+ 'px,0) rotate('+angle.toFixed(1)+'deg)';
//     requestAnimationFrame(loop);
//   }
// })();