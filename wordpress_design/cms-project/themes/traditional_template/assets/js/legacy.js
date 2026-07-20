
/* --- carousel-mini-video.js --- */
/* ==========================================================================
   carousel_mini_video_scroll - shelf scrolling + YouTube/video play facade.
   Initialises every .nt-mvs on the page.
   Markup: components/carousels/carousel_mini_video_scroll.php
   ========================================================================== */
(function () {
	'use strict';

	function initMiniVideo(root) {
		if (root.dataset.mvsReady === '1') return;   // guard double-init
		root.dataset.mvsReady = '1';

		var track = root.querySelector('.nt-mvs-track');
		if (!track) return;
		var prev = root.querySelector('.nt-mvs-arrow--prev');
		var next = root.querySelector('.nt-mvs-arrow--next');

		/* ── Scrolling ──────────────────────────────────────────────────── */
		function pageAmount() { return Math.max(200, Math.round(track.clientWidth * 0.85)); }

		function updateArrows() {
			var max = track.scrollWidth - track.clientWidth - 2;
			if (prev) prev.classList.toggle('is-disabled', track.scrollLeft <= 0);
			if (next) next.classList.toggle('is-disabled', track.scrollLeft >= max);
		}

		if (prev) prev.addEventListener('click', function () { track.scrollBy({ left: -pageAmount(), behavior: 'smooth' }); });
		if (next) next.addEventListener('click', function () { track.scrollBy({ left:  pageAmount(), behavior: 'smooth' }); });
		track.addEventListener('scroll', updateArrows, { passive: true });
		window.addEventListener('resize', updateArrows);
		updateArrows();

		/* ── Play facade ────────────────────────────────────────────────── */

		/* Revert a card to its thumbnail (removes the iframe/video, shows overlays). */
		function stopThumb(thumb) {
			var frame = thumb.querySelector('.nt-mvs-frame');
			if (frame) {
				if (frame.tagName === 'VIDEO') { try { frame.pause(); } catch (e) {} }
				frame.remove();
			}
			thumb.classList.remove('is-playing');
		}

		/* Stop every other playing card in this carousel. */
		function stopOthers(except) {
			track.querySelectorAll('.nt-mvs-play-btn.is-playing').forEach(function (t) {
				if (t !== except) stopThumb(t);
			});
		}

		function playInto(thumb) {
			if (thumb.classList.contains('is-playing')) return;
			stopOthers(thumb);   // only one video plays at a time

			var type = thumb.dataset.type, id = thumb.dataset.yt, src = thumb.dataset.src;
			var frame;

			if (type === 'youtube' && id) {
				frame = document.createElement('iframe');
				frame.src = 'https://www.youtube.com/embed/' + id + '?autoplay=1&rel=0&modestbranding=1&playsinline=1';
				frame.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
				frame.setAttribute('allowfullscreen', '');
			} else if (src) {
				frame = document.createElement('video');
				frame.src = src; frame.controls = true; frame.autoplay = true; frame.playsInline = true;
			} else {
				return;
			}

			frame.className = 'nt-mvs-frame';
			thumb.appendChild(frame);
			thumb.classList.add('is-playing');
		}

		track.querySelectorAll('.nt-mvs-play-btn').forEach(function (thumb) {
			thumb.addEventListener('click', function () { playInto(thumb); });
			thumb.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') { e.preventDefault(); playInto(thumb); }
			});
		});
	}

	function boot() {
		document.querySelectorAll('.nt-mvs').forEach(initMiniVideo);
	}

	if (document.readyState !== 'loading') boot();
	else document.addEventListener('DOMContentLoaded', boot);
})();

/* --- carousel-video.js --- */
/* ==========================================================================
   carousel_video_scroll - slider behaviour (dots, arrows, swipe, YouTube facade).
   Initialises every .nt-vs on the page; reads config from data- attributes.
   Markup: components/carousels/carousel_video_scroll.php
   ========================================================================== */
(function () {
	'use strict';

	function initVideoScroll(root) {
		if (root.dataset.vsReady === '1') return;   // guard against double-init
		root.dataset.vsReady = '1';

		var track  = root.querySelector('.nt-vs-track');
		var vp     = root.querySelector('.nt-vs-viewport');
		if (!track || !vp) return;

		var slides = Array.prototype.slice.call(track.querySelectorAll('.nt-vs-slide'));
		var dots   = Array.prototype.slice.call(root.querySelectorAll('.nt-vs-dot'));
		var total  = slides.length;
		if (!total) return;

		var loop     = root.dataset.loop === '1';
		var autoplay = parseInt(root.dataset.autoplay || '0', 10);
		var index    = 0;
		var timer    = null;

		function stopMedia() {
			/* Collapse any open player (YouTube iframe OR <video>) back to its facade */
			track.querySelectorAll('.nt-vs-media[data-yt-open="1"]').forEach(function (media) {
				var btn   = media.querySelector('.nt-vs-yt');
				var frame = media.querySelector('iframe, video');
				if (frame) { if (frame.tagName === 'VIDEO') { try { frame.pause(); } catch (e) {} } frame.remove(); }
				if (btn) btn.style.display = '';
				media.removeAttribute('data-yt-open');
			});
		}

		function go(i) {
			if (loop) { i = (i + total) % total; }
			else      { i = Math.max(0, Math.min(total - 1, i)); }
			stopMedia();
			index = i;
			track.style.transform = 'translateX(' + (-index * 100) + '%)';
			slides.forEach(function (s, n) { s.classList.toggle('is-active', n === index); });
			dots.forEach(function (d, n) {
				d.classList.toggle('is-active', n === index);
				d.setAttribute('aria-selected', n === index ? 'true' : 'false');
			});
		}

		function startAuto() { if (autoplay > 0 && total > 1) timer = setInterval(function () { go(index + 1); }, autoplay); }
		function stopAuto()  { if (timer) { clearInterval(timer); timer = null; } }
		function restart()   { stopAuto(); startAuto(); }

		/* Arrows */
		var prev = root.querySelector('.nt-vs-arrow--prev');
		var next = root.querySelector('.nt-vs-arrow--next');
		if (prev) prev.addEventListener('click', function () { go(index - 1); restart(); });
		if (next) next.addEventListener('click', function () { go(index + 1); restart(); });

		/* Dots */
		dots.forEach(function (d) {
			d.addEventListener('click', function () { go(parseInt(d.dataset.go, 10) || 0); restart(); });
		});

		/* Keyboard when the carousel has focus */
		root.addEventListener('keydown', function (e) {
			if (e.key === 'ArrowLeft')  { go(index - 1); restart(); }
			if (e.key === 'ArrowRight') { go(index + 1); restart(); }
		});

		/* Play facade → load the player (YouTube iframe or <video>) on click/keypress */
		function playFacade(btn) {
			var media = btn.closest('.nt-vs-media');
			if (!media || media.getAttribute('data-yt-open') === '1') return;

			var type = btn.dataset.type;
			var frame;
			if (type === 'video') {
				if (!btn.dataset.src) return;
				frame = document.createElement('video');
				frame.src = btn.dataset.src;
				frame.controls = true; frame.autoplay = true; frame.playsInline = true;
			} else {
				if (!btn.dataset.yt) return;
				frame = document.createElement('iframe');
				frame.src = 'https://www.youtube.com/embed/' + btn.dataset.yt + '?autoplay=1&rel=0&modestbranding=1';
				frame.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
				frame.setAttribute('allowfullscreen', '');
			}
			btn.style.display = 'none';
			media.appendChild(frame);
			media.setAttribute('data-yt-open', '1');
			stopAuto(); /* don't slide away from a playing video */
		}

		track.querySelectorAll('.nt-vs-yt').forEach(function (btn) {
			btn.addEventListener('click', function () { playFacade(btn); });
			btn.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') { e.preventDefault(); playFacade(btn); }
			});
		});

		/* Touch swipe */
		var startX = 0, dragging = false;
		vp.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; dragging = true; }, { passive: true });
		vp.addEventListener('touchend', function (e) {
			if (!dragging) return;
			dragging = false;
			var dx = e.changedTouches[0].clientX - startX;
			if (Math.abs(dx) > 40) { dx < 0 ? go(index + 1) : go(index - 1); restart(); }
		}, { passive: true });

		/* Pause autoplay on hover */
		root.addEventListener('mouseenter', stopAuto);
		root.addEventListener('mouseleave', startAuto);

		go(0);
		startAuto();
	}

	function boot() {
		document.querySelectorAll('.nt-vs').forEach(initVideoScroll);
	}

	if (document.readyState !== 'loading') boot();
	else document.addEventListener('DOMContentLoaded', boot);
})();

/* --- carousel.js --- */
/**
 * CH Generic Carousel - carousel.js
 */
( function ( window ) {
    'use strict';

    function CHCarousel( el, options ) {
        if ( ! ( el instanceof HTMLElement ) ) return;
        this.el   = el;
        this.opts = Object.assign( {
            autoplay:     parseInt( el.dataset.autoplay, 10 ) || 0,
            loop:         el.dataset.loop !== 'false',
            selectorMode: el.dataset.selector === 'true',
            ticker:       el.dataset.ticker === 'true',
            direction:    el.dataset.direction || 'horizontal',
            showNav:      el.dataset.nav !== 'hide',
            onChange:     null,
        }, options || {} );

        this._index    = 0;
        this._total    = 0;
        this._timer    = null;
        this._pointerX = null;
        this._pointerY = null;
        this._build();
    }

    /* ── build ──────────────────────────────────────────────── */
    CHCarousel.prototype._build = function () {
        var el        = this.el;
        this.track    = el.querySelector( '.nt-carousel__track' );
        this.items    = Array.from( el.querySelectorAll( '.nt-carousel__item' ) );
        this.viewport = el.querySelector( '.nt-carousel__viewport' );
        this.navEl    = el.querySelector( '.nt-carousel__nav' );
        this.dots     = Array.from( el.querySelectorAll( '.nt-carousel__dot' ) );
        this.prevBtn  = el.querySelector( '[data-dir="prev"]' );
        this.nextBtn  = el.querySelector( '[data-dir="next"]' );
        this._total   = this.items.length;
        this._isV     = this.opts.direction === 'vertical';

        if ( ! this.track || ! this._total ) return;

        if ( this.opts.ticker ) {
            this._initTicker();
            return;
        }

        this._checkNav();

        // If looping and we have enough items, clone them for a seamless loop
        if ( this.opts.loop && !this._noCarousel && this._total > 1 ) {
            var self = this;
            var clones = [];
            this.items.forEach( function( item ) {
                var clone = item.cloneNode(true);
                clone.classList.add('is-clone');
                clone.setAttribute('aria-hidden', 'true');
                self.track.appendChild(clone);
                clones.push(clone);
            } );
            this.items = this.items.concat(clones);
            this._cloned = true;

            this.track.addEventListener('transitionend', function(e) {
                if (e.target !== self.track) return;
                if (self._index >= self._total) {
                    self.track.style.transition = 'none';
                    self._index = self._index % self._total;
                    self._slide();
                    // Update navigation indicators after looping
                    self._updateDots();
                    self._updateArrows();
                    void self.track.offsetWidth;
                    self.track.style.transition = '';
                }
            });
        }

        this._bindEvents();
        this._watchContainer();

        if ( this.opts.autoplay     ) this._startAutoplay();
        if ( this.opts.selectorMode ) this._initSelector();
    };

    /* ── ticker ─────────────────────────────────────────────── */
    CHCarousel.prototype._initTicker = function () {
        var track     = this.track;
        var origItems = Array.from( track.children );
        origItems.forEach( function ( item ) {
            track.appendChild( item.cloneNode( true ) );
        } );
        var self = this;
        requestAnimationFrame( function () {
            var size  = self._isV ? track.scrollHeight / 2 : track.scrollWidth / 2;
            var speed = parseFloat( self.el.dataset.tickerSpeed ) || 60;
            var dur   = size / speed;
            self.el.style.setProperty( '--cc-ticker-duration', dur.toFixed(1) + 's' );
        } );
        if ( this.opts.selectorMode ) this._initSelector();
    };

    /* ── how many items fit ─────────────────────────────────── */
    CHCarousel.prototype._visibleCount = function () {
        /* Read from the track element (not the viewport which has container isolation) */
        var raw = getComputedStyle( this.track ).getPropertyValue( '--cc-items-visible' ).trim();
        var n   = parseInt( raw, 10 );

        if ( ! n || isNaN( n ) ) {
            if ( this.items[0] && this.viewport ) {
                var vSize = this._isV ? this.viewport.offsetHeight : this.viewport.offsetWidth;
                var iSize = this._isV ? this.items[0].offsetHeight : this.items[0].offsetWidth;
                n = iSize > 0 ? Math.round( vSize / iSize ) : 1;
            } else {
                n = 1;
            }
        }
        return Math.max( 1, n );
    };

    /* ── max valid index ────────────────────────────────────── */
    CHCarousel.prototype._maxIndex = function () {
        if (this._cloned) return this._total - 1;
        return Math.max( 0, this._total - this._visibleCount() );
    };

    /* ── show / hide nav ────────────────────────────────────── */
    CHCarousel.prototype._checkNav = function () {
        if ( ! this.opts.showNav ) {
            this._setNavVisible( false );
            this._noCarousel = true;
            return;
        }
        var fits = this._total <= this._visibleCount();
        this._setNavVisible( ! fits );
        this._noCarousel = fits;
    };

    CHCarousel.prototype._setNavVisible = function ( visible ) {
        if ( ! this.navEl ) return;
        this.navEl.style.display = visible ? '' : 'none';
    };

    /* ── go to index ────────────────────────────────────────── */
    CHCarousel.prototype.goTo = function ( index ) {
        if ( this._noCarousel ) { this._index = 0; return; }

        var maxIdx = this._maxIndex();

        if ( this.opts.loop ) {
            if ( this._cloned ) {
                if ( index < 0 ) {
                    this.track.style.transition = 'none';
                    this._index = this._total;
                    this._slide();
                    void this.track.offsetWidth;
                    this.track.style.transition = '';
                    index = this._total - 1;
                }
            } else {
                if ( index > maxIdx ) index = 0;
                if ( index < 0      ) index = maxIdx;
            }
        } else {
            index = Math.max( 0, Math.min( index, maxIdx ) );
        }

        this._index = index;
        this._slide();
        this._updateDots();
        this._updateArrows();

        if ( typeof this.opts.onChange === 'function' ) {
            this.opts.onChange( this._index % this._total );
        }
    };

    CHCarousel.prototype.next = function () { this.goTo( this._index + 1 ); };
    CHCarousel.prototype.prev = function () { this.goTo( this._index - 1 ); };

    /* ── slide the track ────────────────────────────────────── */
    CHCarousel.prototype._slide = function () {
        if ( ! this.items[ this._index ] ) return;

        /* Measure the actual rendered item - includes its margin/gap share */
        var trackStyle = getComputedStyle( this.track );
        var gap = parseFloat( this._isV
            ? ( trackStyle.rowGap    || trackStyle.gap )
            : ( trackStyle.columnGap || trackStyle.gap )
        ) || 0;

        if ( this._isV ) {
            var itemH  = this.items[ this._index ].getBoundingClientRect().height
                      || this.items[ this._index ].offsetHeight;
            var offset = this._index * ( itemH + gap );
            this.track.style.transform = 'translateY(-' + offset + 'px)';
        } else {
            var itemW  = this.items[ this._index ].getBoundingClientRect().width
                      || this.items[ this._index ].offsetWidth;
            var offset = this._index * ( itemW + gap );
            this.track.style.transform = 'translateX(-' + offset + 'px)';
        }
    };

    /* ── dots: highlight the FIRST visible item's dot ───────── */
    CHCarousel.prototype._updateDots = function () {
        var idx = this._index;
        var maxIdx = this._maxIndex();
        this.dots.forEach( function ( dot, i ) {
            dot.style.display = i > maxIdx ? 'none' : '';
            dot.classList.toggle( 'is-active', i === idx );
        } );
    };

    /* ── arrows ─────────────────────────────────────────────── */
    CHCarousel.prototype._updateArrows = function () {
        if ( ! this.prevBtn || ! this.nextBtn ) return;
        if ( this.opts.loop ) {
            this.prevBtn.disabled = false;
            this.nextBtn.disabled = false;
            return;
        }
        var maxIdx = this._maxIndex();
        this.prevBtn.disabled = this._index <= 0;
        this.nextBtn.disabled = this._index >= maxIdx;
    };

    /* ── events ─────────────────────────────────────────────── */
    CHCarousel.prototype._bindEvents = function () {
        var self = this;

        if ( this.prevBtn ) {
            this.prevBtn.addEventListener( 'click', function () {
                self._stopAutoplay(); self.prev(); self._startAutoplay();
            } );
        }
        if ( this.nextBtn ) {
            this.nextBtn.addEventListener( 'click', function () {
                self._stopAutoplay(); self.next(); self._startAutoplay();
            } );
        }

        this.dots.forEach( function ( dot, i ) {
            dot.addEventListener( 'click', function () {
                self._stopAutoplay(); self.goTo( i ); self._startAutoplay();
            } );
        } );

        if ( this.viewport ) {
            this.viewport.addEventListener( 'pointerdown', function ( e ) {
                self._pointerX = e.clientX;
                self._pointerY = e.clientY;
            } );
            this.viewport.addEventListener( 'pointerup', function ( e ) {
                if ( self._pointerX === null ) return;
                var dx = e.clientX - self._pointerX;
                var dy = e.clientY - self._pointerY;
                self._pointerX = self._pointerY = null;
                if ( self._isV ) {
                    if ( Math.abs( dy ) < 30 ) return;
                    self._stopAutoplay();
                    dy < 0 ? self.next() : self.prev();
                    self._startAutoplay();
                } else {
                    if ( Math.abs( dx ) < 30 ) return;
                    self._stopAutoplay();
                    dx < 0 ? self.next() : self.prev();
                    self._startAutoplay();
                }
            } );
            this.viewport.addEventListener( 'pointerleave', function () {
                self._pointerX = self._pointerY = null;
            } );
        }
    };

    /* ── ResizeObserver ─────────────────────────────────────── */
    CHCarousel.prototype._watchContainer = function () {
        var self = this;
        if ( ! window.ResizeObserver ) return;
        var ro = new ResizeObserver( function () {
            self._checkNav();
            var maxIdx = self._maxIndex();
            if ( self._index > maxIdx ) self._index = maxIdx;
            self._slide();
            self._updateDots();
            self._updateArrows();
        } );
        ro.observe( this.el );
    };

    /* ── autoplay ───────────────────────────────────────────── */
    CHCarousel.prototype._startAutoplay = function () {
        if ( ! this.opts.autoplay || this._noCarousel ) return;
        var self = this;
        this._stopAutoplay();
        this._timer = setInterval( function () { self.next(); }, self.opts.autoplay );
    };
    CHCarousel.prototype._stopAutoplay = function () {
        if ( this._timer ) { clearInterval( this._timer ); this._timer = null; }
    };
    CHCarousel.prototype._bindHoverPause = function () {
        var self = this;
        if ( ! this.opts.autoplay ) return;
        this.el.addEventListener( 'mouseenter', function () { self._stopAutoplay(); } );
        this.el.addEventListener( 'mouseleave', function () { self._startAutoplay(); } );
    };

    /* ── selector mode ──────────────────────────────────────── */
    CHCarousel.prototype._initSelector = function () {
        var self = this;
        this.items.forEach( function ( item, i ) {
            var card = item.querySelector( '.nt-card--selector' );
            if ( ! card ) return;
            card.addEventListener( 'click', function () {
                self.el.querySelectorAll( '.nt-card--selector' )
                    .forEach( function ( c ) { c.classList.remove( 'is-active' ); } );
                card.classList.add( 'is-active' );
                if ( typeof self.opts.onChange === 'function' ) {
                    self.opts.onChange( i, card.dataset.value || i );
                }
            } );
        } );
        var first = this.el.querySelector( '.nt-card--selector' );
        if ( first ) first.classList.add( 'is-active' );
    };

    /* ── init ───────────────────────────────────────────────── */
    CHCarousel.init = function ( scope ) {
        ( scope || document )
            .querySelectorAll( '.nt-carousel:not([data-carousel-init])' )
            .forEach( function ( el ) {
                var instance   = new CHCarousel( el );
                el._chCarousel = instance;
                el.setAttribute( 'data-carousel-init', 'true' );
                instance._bindHoverPause();
            } );
    };

    window.CHCarousel = CHCarousel;

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', function () { CHCarousel.init(); } );
    } else {
        CHCarousel.init();
    }

} ( window ) );
/* --- form-step-modal.js --- */
/* ==========================================================================
   chStepModal - reusable multi-step modal wizard controller.

   Pairs with the PHP component components/forms/form_step_modal.php.
   Drives the shared chrome (open/close, step nav, progress bar, summary,
   submit + success) for ANY wizard, keyed by its `prefix`. Each form supplies
   only what differs (validation, summary, payload) via callbacks.

   Required DOM (all derived from prefix, e.g. 'bk'):
     #nt-{prefix}-form     the <form>            (or pass opts.formId)
     #nt-{prefix}-modal    the modal wrapper
     #nt-{prefix}-open     the button that opens it
     #nt-{prefix}-msg      feedback element
     #nt-{prefix}-submit   submit button
     #nt-{prefix}-summary  summary container (filled on the last step)
     [data-{prefix}-close] any close trigger
     .nt-bk-step / .nt-bk-next / .nt-bk-back / .nt-bk-prog-step (shared classes)

   Options:
     prefix         (string)  required - id/attr prefix
     formId         (string)  override form id (default 'nt-{prefix}-form')
     action         (string)  WP ajax action for submit
     sendingLabel   (string)  submit button text while sending
     successIcon / successTitle / successMessage  success screen content
     allowJumpBack  (bool)    click a completed progress step to go back (default true)
     validateStep   (fn ctx,step → bool)  return false to block advancing
     buildSummary   (fn ctx → html)       html for the last-step summary
     collectData    (fn ctx → FormData)   payload (default: new FormData(form))
     onInit         (fn ctx)              extra per-form wiring (e.g. qty buttons)

   ctx exposes: form, modal, step(), total, showMsg, hideMsg, clearErrors,
                fieldError, val(name), escHtml(str).
   ========================================================================== */
(function () {
	'use strict';

	var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

	function escHtml(s) {
		var d = document.createElement('div');
		d.textContent = (s == null ? '' : s);
		return d.innerHTML;
	}

	window.chStepModal = function (opts) {
		opts = opts || {};
		var prefix = opts.prefix;
		if (!prefix || typeof chTheme === 'undefined') return;

		var form   = document.getElementById(opts.formId || ('nt-' + prefix + '-form'));
		var modal  = document.getElementById('nt-' + prefix + '-modal');
		var openBtn = document.getElementById('nt-' + prefix + '-open');
		if (!form || !modal) return;

		var box       = modal.querySelector('.nt-bk-modal-box');
		var steps     = Array.prototype.slice.call(form.querySelectorAll('.nt-bk-step'));
		var progSteps = Array.prototype.slice.call(modal.querySelectorAll('.nt-bk-prog-step'));
		var progFill  = modal.querySelector('.nt-bk-prog-fill');
		var msgEl     = document.getElementById('nt-' + prefix + '-msg');
		var submitBtn = document.getElementById('nt-' + prefix + '-submit');
		var summary   = document.getElementById('nt-' + prefix + '-summary');
		var total     = steps.length;
		var current   = 1;
		var closeAttr = 'data-' + prefix + '-close';

		/* ── helpers (also handed to callbacks via ctx) ──────────────────── */
		function showMsg(text, type) { if (!msgEl) return; msgEl.textContent = text; msgEl.className = 'nt-form-feedback ' + type; msgEl.style.display = 'block'; }
		function hideMsg() { if (msgEl) msgEl.style.display = 'none'; }
		function clearErrors() {
			form.querySelectorAll('.nt-field-error').forEach(function (e) { e.remove(); });
			form.querySelectorAll('.nt-bk-field').forEach(function (e) { e.classList.remove('invalid'); });
		}
		function fieldError(field, message) {
			if (!field) return;
			field.classList.add('invalid');
			var e = document.createElement('span');
			e.className = 'nt-field-error';
			e.textContent = message;
			field.appendChild(e);
		}
		function val(name) { var el = form.querySelector('[name="' + name + '"]'); return el ? String(el.value).trim() : ''; }

		var ctx = {
			form: form, modal: modal,
			step: function () { return current; }, total: total,
			showMsg: showMsg, hideMsg: hideMsg,
			clearErrors: clearErrors, fieldError: fieldError,
			val: val, escHtml: escHtml
		};

		/* ── open / close ────────────────────────────────────────────────── */
		function openModal() {
			modal.classList.add('is-open');
			modal.setAttribute('aria-hidden', 'false');
			document.body.style.overflow = 'hidden';
			var prog = modal.querySelector('.nt-bk-progress');
			if (prog) prog.style.display = '';   // restore if a prior success hid it
			goTo(1);
		}
		function closeModal() {
			modal.classList.remove('is-open');
			modal.setAttribute('aria-hidden', 'true');
			document.body.style.overflow = '';
		}
		if (openBtn) openBtn.addEventListener('click', openModal);
		modal.querySelectorAll('[' + closeAttr + ']').forEach(function (el) { el.addEventListener('click', closeModal); });
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal(); });

		/* ── step navigation ─────────────────────────────────────────────── */
		function goTo(step) {
			current = step;
			steps.forEach(function (s) { s.classList.toggle('active', parseInt(s.dataset.step, 10) === step); });
			progSteps.forEach(function (p) {
				var ps = parseInt(p.dataset.step, 10);
				p.classList.toggle('active', ps === step);
				p.classList.toggle('done', ps < step);
			});
			if (progFill && total > 1) progFill.style.width = ((step - 1) / (total - 1) * 100) + '%';
			if (step === total && typeof opts.buildSummary === 'function' && summary) {
				summary.innerHTML = opts.buildSummary(ctx) || '';
			}
			hideMsg();
			if (box) box.scrollTop = 0;
		}

		form.querySelectorAll('.nt-bk-next').forEach(function (btn) {
			btn.addEventListener('click', function () {
				if (typeof opts.validateStep === 'function' && !opts.validateStep(ctx, current)) return;
				goTo(parseInt(btn.dataset.next, 10));
			});
		});
		form.querySelectorAll('.nt-bk-back').forEach(function (btn) {
			btn.addEventListener('click', function () { goTo(parseInt(btn.dataset.back, 10)); });
		});
		if (opts.allowJumpBack !== false) {
			progSteps.forEach(function (p) {
				p.addEventListener('click', function () {
					var ps = parseInt(p.dataset.step, 10);
					if (ps < current) goTo(ps);
				});
			});
		}

		/* ── submit ──────────────────────────────────────────────────────── */
		form.addEventListener('submit', function (e) {
			e.preventDefault();

			var nameEl  = form.querySelector('[name="' + prefix + '_name"]');
			var emailEl = form.querySelector('[name="' + prefix + '_email"]');
			if (nameEl && !nameEl.value.trim()) { showMsg('Please enter your name.', 'error'); nameEl.focus(); return; }
			if (emailEl && (!emailEl.value.trim() || !EMAIL_RE.test(emailEl.value.trim()))) {
				showMsg('Please enter a valid email address.', 'error'); emailEl.focus(); return;
			}

			var originalText = submitBtn ? submitBtn.textContent : '';
			if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = opts.sendingLabel || 'Sending… 🌿'; }

			var data = (typeof opts.collectData === 'function') ? opts.collectData(ctx) : new FormData(form);
			data.append('action', opts.action);
			data.append('nonce', chTheme.nonce);

			fetch(chTheme.ajaxUrl, { method: 'POST', body: data })
				.then(function (r) { return r.json(); })
				.catch(function () { return null; })
				.then(function (res) {
					if (res && res.success) {
						renderSuccess(res.data && res.data.message);
					} else {
						showMsg((res && res.data && res.data.message) ? res.data.message : 'Something went wrong. Please try again.', 'error');
						if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = originalText; }
					}
				});

			function renderSuccess(message) {
				var lastStep = form.querySelector('.nt-bk-step[data-step="' + total + '"]');
				if (lastStep) {
					lastStep.innerHTML =
						'<div class="nt-bk-success">' +
						'<div class="nt-bk-success-icon">' + (opts.successIcon || '🎉') + '</div>' +
						'<h3>' + (opts.successTitle || 'Sent!') + '</h3>' +
						'<p>' + escHtml(message || opts.successMessage || "Thanks! We'll be in touch soon. 🌿") + '</p>' +
						'<button type="button" class="btn-lime" ' + closeAttr + ' style="margin-top:1.2rem;">Close</button>' +
						'</div>';
					var cb = lastStep.querySelector('[' + closeAttr + ']');
					if (cb) cb.addEventListener('click', closeModal);
				}
				var prog = modal.querySelector('.nt-bk-progress');
				if (prog) prog.style.display = 'none';
			}
		});

		if (typeof opts.onInit === 'function') opts.onInit(ctx);

		return { open: openModal, close: closeModal, goTo: goTo };
	};
})();

/* --- forms.js --- */
/* The Cane House - Forms JS
   Multi-step wizards are driven by the shared chStepModal controller
   (assets/js/form-step-modal.js); this file holds the contact form, the
   native-share button, and each wizard's form-specific config. */
(function ($) {
    'use strict';

    if (typeof chTheme === 'undefined') return;

    // ── Contact Form ───────────────────────────────────────────────────────────
    function initContactForm() {
        var form   = document.getElementById('nt-contact-form');
        var msg    = document.getElementById('nt-form-msg');
        var submit = document.getElementById('nt-form-submit');
        if (!form) return;

        function showMsg(text, type) {
            if (!msg) return;
            msg.textContent = text;
            msg.className   = 'nt-form-feedback ' + type;
            msg.style.display = 'block';
            msg.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function clearErrors() {
            form.querySelectorAll('.nt-field-error:not(.nt-consent-error)').forEach(function (el) { el.remove(); });
            form.querySelectorAll('.invalid').forEach(function (el) { el.classList.remove('invalid'); });
            var consentErr = form.querySelector('.nt-consent-error');
            if (consentErr) consentErr.style.display = 'none';
            var dg = form.querySelector('.nt-disclaimer-group');
            if (dg) dg.classList.remove('has-error');
        }

        function showFieldError(field, message) {
            field.classList.add('invalid');
            var err = document.createElement('span');
            err.className   = 'nt-field-error';
            err.textContent = message;
            field.insertAdjacentElement('afterend', err);
        }

        function validate() {
            var ok      = true;
            var name    = document.getElementById('nt-name');
            var email   = document.getElementById('nt-email');
            var consent = document.getElementById('nt-consent');
            clearErrors();

            if (name && name.value.trim() === '') {
                showFieldError(name, 'Please enter your name.');
                ok = false;
            }
            if (email) {
                if (email.value.trim() === '') {
                    showFieldError(email, 'Please enter your email address.');
                    ok = false;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                    showFieldError(email, 'Please enter a valid email address.');
                    ok = false;
                }
            }
            if (consent && !consent.checked) {
                var consentErr = form.querySelector('.nt-consent-error');
                if (consentErr) consentErr.style.display = 'block';
                consent.closest('.nt-disclaimer-group').classList.add('has-error');
                ok = false;
            }
            return ok;
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!validate()) return;

            var originalText = submit.textContent;
            submit.disabled    = true;
            submit.textContent = 'Sending... 🌿';

            var data = new FormData(form);
            data.append('action', 'NT_contact_submit');
            data.append('nonce',  chTheme.nonce);

            fetch(chTheme.ajaxUrl, { method: 'POST', body: data })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    if (res.success) {
                        var formContainer = form.closest('.nt-contact-form');
                        var message = res.data.message || "Thanks! We'll be in touch soon. 🌿";

                        var successBox = document.createElement('div');
                        successBox.style.cssText = 'display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:500px;text-align:center;padding:4rem 2.5rem;';

                        var emoji = document.createElement('div');
                        emoji.textContent = '🌿';
                        emoji.style.cssText = 'font-size:5.5rem;margin-bottom:2rem;line-height:1;';

                        var title = document.createElement('h3');
                        title.textContent = 'Message Sent!';
                        title.style.cssText = 'font-family:var(--nt-font-display);font-size:2.2rem;font-weight:900;color:var(--client-color-1);margin-bottom:1rem;letter-spacing:-0.01em;';

                        var msgText = document.createElement('p');
                        msgText.textContent = message;
                        msgText.style.cssText = 'font-size:1.05rem;color:var(--client-color-15-muted);line-height:1.75;max-width:480px;margin:0;';

                        successBox.appendChild(emoji);
                        successBox.appendChild(title);
                        successBox.appendChild(msgText);

                        formContainer.innerHTML = '';
                        formContainer.appendChild(successBox);
                    } else {
                        showMsg(res.data.message || 'Something went wrong. Please try again.', 'error');
                        submit.disabled    = false;
                        submit.textContent = originalText;
                    }
                })
                .catch(function () {
                    showMsg('Connection error. Please try again.', 'error');
                    submit.disabled    = false;
                    submit.textContent = originalText;
                });
        });
    }

    // ── Native Share Button ─────────────────────────────────────────────────────
    function initNativeShare() {
        var shareBtn = document.getElementById('nt-native-share');
        if (!shareBtn) return;

        shareBtn.addEventListener('click', function (e) {
            e.preventDefault();
            var url = window.location.href;
            var title = document.querySelector('h1') ? document.querySelector('h1').textContent : document.title;

            if (navigator.share) {
                navigator.share({ title: title, text: 'Check this out!', url: url })
                    .catch(function (err) { if (err.name !== 'AbortError') console.error('Share error:', err); });
            } else if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(function () {
                    var originalText = shareBtn.innerHTML;
                    shareBtn.innerHTML = '✓ Link copied!';
                    setTimeout(function () { shareBtn.innerHTML = originalText; }, 2000);
                }).catch(function () {
                    alert('Copy to clipboard failed. URL: ' + url);
                });
            } else {
                prompt('Copy link:', url);
            }
        });
    }

    // ── Booking Wizard ──────────────────────────────────────────────────────────
    function initBookingWizard() {
        if (typeof chStepModal !== 'function') return;
        chStepModal({
            prefix:         'bk',
            formId:         'nt-booking-form',
            action:         'NT_booking_submit',
            successTitle:   'Order Request Sent!',
            successMessage: "Thanks! We'll be in touch very soon to confirm your order. 🌿",

            validateStep: function (ctx, step) {
                ctx.clearErrors();
                var canes = ctx.form.querySelectorAll('[name="bk_cane[]"]:checked').length;
                var flav  = ctx.form.querySelectorAll('[name="bk_flavour[]"]:checked').length;
                if (step === 1 && canes === 0) { ctx.showMsg('Please choose at least one cane type. 🌾', 'error'); return false; }
                if (step === 2 && flav  === 0) { ctx.showMsg('Please pick at least one flavour. 🍋', 'error'); return false; }
                if (step === 4) {
                    var ok = true;
                    var occasion = ctx.form.querySelector('[name="bk_occasion"]');
                    var date     = ctx.form.querySelector('[name="bk_date"]');
                    var guests   = ctx.form.querySelector('[name="bk_guests"]');
                    var location = ctx.form.querySelector('[name="bk_location"]');
                    if (!occasion.value.trim()) { ctx.fieldError(occasion.closest('.nt-bk-field'), 'Please select an occasion.'); ok = false; }
                    if (!date.value.trim())     { ctx.fieldError(date.closest('.nt-bk-field'), 'Please enter the event date.'); ok = false; }
                    if (!guests.value.trim() || parseInt(guests.value, 10) < 1) { ctx.fieldError(guests.closest('.nt-bk-field'), 'Please enter the number of guests (minimum 1).'); ok = false; }
                    if (!location.value.trim()) { ctx.fieldError(location.closest('.nt-bk-field'), 'Please enter the venue location.'); ok = false; }
                    if (!ok) { ctx.showMsg('Please fill in all required event details. 📋', 'error'); return false; }
                }
                return true;
            },

            buildSummary: function (ctx) {
                var canes = Array.prototype.map.call(ctx.form.querySelectorAll('[name="bk_cane[]"]:checked'),    function (c) { return c.value; });
                var flav  = Array.prototype.map.call(ctx.form.querySelectorAll('[name="bk_flavour[]"]:checked'), function (c) { return c.value; });
                var rows = [
                    ['🌾 Cane',     canes.join(', ')],
                    ['🍋 Flavours', flav.join(', ')],
                    ['🎉 Occasion', ctx.val('bk_occasion')],
                    ['📅 Date',     ctx.val('bk_date')],
                    ['👥 Guests',   ctx.val('bk_guests')],
                    ['📍 Location', ctx.val('bk_location')]
                ];
                var html = '<div class="nt-bk-summary-title">Your Order Summary</div><div class="nt-bk-summary-grid">';
                rows.forEach(function (r) {
                    if (!r[1]) return;
                    html += '<div class="nt-bk-summary-row"><span class="nt-bk-summary-label">' + r[0] + '</span><span class="nt-bk-summary-val">' + ctx.escHtml(r[1]) + '</span></div>';
                });
                return html + '</div>';
            },

            collectData: function (ctx) {
                var d = new FormData();
                d.append('bk_name',     ctx.val('bk_name'));
                d.append('bk_email',    ctx.val('bk_email'));
                d.append('bk_phone',    ctx.val('bk_phone'));
                d.append('bk_occasion', ctx.val('bk_occasion'));
                d.append('bk_date',     ctx.val('bk_date'));
                d.append('bk_guests',   ctx.val('bk_guests'));
                d.append('bk_location', ctx.val('bk_location'));
                d.append('bk_notes',    ctx.val('bk_notes'));
                ctx.form.querySelectorAll('[name="bk_cane[]"]:checked').forEach(function (c)    { d.append('bk_cane[]',    c.value); });
                ctx.form.querySelectorAll('[name="bk_flavour[]"]:checked').forEach(function (c) { d.append('bk_flavour[]', c.value); });
                return d;
            }
        });
    }

    // ── Franchise Enquiry Wizard ─────────────────────────────────────────────────
    function initFranchiseWizard() {
        if (typeof chStepModal !== 'function') return;
        chStepModal({
            prefix:         'frn',
            formId:         'nt-frn-form',
            action:         'NT_franchise_submit',
            successTitle:   'Enquiry Sent!',
            successMessage: "Thank you! We'll be in touch within 24 hours. 🌿",

            validateStep: function (ctx, step) {
                ctx.clearErrors();
                if (step === 1) {
                    var ok = true;
                    var city = ctx.form.querySelector('[name="frn_city"]');
                    var type = ctx.form.querySelector('[name="frn_type"]');
                    var time = ctx.form.querySelector('[name="frn_timeline"]');
                    if (!city.value.trim()) { ctx.fieldError(city.closest('.nt-bk-field'), 'Please enter a city or area.'); ok = false; }
                    if (!type.value.trim()) { ctx.fieldError(type.closest('.nt-bk-field'), 'Please select a franchise type.'); ok = false; }
                    if (!time.value.trim()) { ctx.fieldError(time.closest('.nt-bk-field'), 'Please select a timeline.'); ok = false; }
                    if (!ok) { ctx.showMsg('Please fill in all required fields.', 'error'); return false; }
                }
                if (step === 2) {
                    var inv = ctx.form.querySelector('[name="frn_investment"]');
                    if (!inv.value.trim()) { ctx.fieldError(inv.closest('.nt-bk-field'), 'Please select an investment range.'); ctx.showMsg('Please select your investment range.', 'error'); return false; }
                }
                return true;
            },

            buildSummary: function (ctx) {
                var rows = [
                    ['📍 City / Area',    ctx.val('frn_city')],
                    ['🏪 Franchise Type', ctx.val('frn_type')],
                    ['⏱ Timeline',        ctx.val('frn_timeline')],
                    ['💼 Investment',     ctx.val('frn_investment')]
                ];
                var html = '<div class="nt-bk-summary-title">Your Enquiry Summary</div><div class="nt-bk-summary-grid">';
                rows.forEach(function (r) {
                    if (!r[1]) return;
                    html += '<div class="nt-bk-summary-row"><span class="nt-bk-summary-label">' + r[0] + '</span><span class="nt-bk-summary-val">' + ctx.escHtml(r[1]) + '</span></div>';
                });
                return html + '</div>';
            },

            collectData: function (ctx) {
                var d = new FormData();
                d.append('frn_name',       ctx.val('frn_name'));
                d.append('frn_email',      ctx.val('frn_email'));
                d.append('frn_phone',      ctx.val('frn_phone'));
                d.append('frn_city',       ctx.val('frn_city'));
                d.append('frn_type',       ctx.val('frn_type'));
                d.append('frn_timeline',   ctx.val('frn_timeline'));
                d.append('frn_investment', ctx.val('frn_investment'));
                d.append('frn_experience', ctx.val('frn_experience'));
                d.append('frn_message',    ctx.val('frn_message'));
                return d;
            }
        });
    }

    // ── Order-to-Deliver Wizard ──────────────────────────────────────────────────
    function initOrderWizard() {
        if (typeof chStepModal !== 'function') return;
        chStepModal({
            prefix:         'otd',
            formId:         'nt-otd-form',
            action:         'NT_order_submit',
            successTitle:   'Order Request Sent!',
            successMessage: "Thanks! We'll review your order and contact you shortly. 🌿",
            // payload = all named fields (default new FormData(form))

            onInit: function (ctx) {
                // Quantity +/- buttons (otd-specific)
                ctx.form.querySelectorAll('.nt-otd-qty-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var input = btn.closest('.nt-otd-qty-wrap').querySelector('.nt-otd-qty-input');
                        var v = parseInt(input.value, 10) || 1;
                        input.value = btn.classList.contains('nt-otd-qty-plus') ? v + 1 : Math.max(1, v - 1);
                    });
                });
            },

            validateStep: function (ctx, step) {
                ctx.clearErrors();
                if (step === 1) {
                    var items  = ctx.form.querySelectorAll('[name="otd_items[]"]:checked').length;
                    var custom = ctx.form.querySelector('[name="otd_custom_item"]');
                    if (items === 0 && (!custom || !custom.value.trim())) { ctx.showMsg('Please select at least one item. 🥤', 'error'); return false; }
                }
                if (step === 2) {
                    var ok = true;
                    var addr = ctx.form.querySelector('[name="otd_address"]');
                    var area = ctx.form.querySelector('[name="otd_area"]');
                    if (!addr.value.trim()) { ctx.fieldError(addr.closest('.nt-bk-field'), 'Please enter your delivery address.'); ok = false; }
                    if (!area.value.trim()) { ctx.fieldError(area.closest('.nt-bk-field'), 'Please enter your area or city.'); ok = false; }
                    if (!ok) { ctx.showMsg('Please fill in your delivery details. 📦', 'error'); return false; }
                }
                return true;
            },

            buildSummary: function (ctx) {
                var items = Array.prototype.map.call(ctx.form.querySelectorAll('[name="otd_items[]"]:checked'), function (c) { return c.value; });
                var custom = (ctx.form.querySelector('[name="otd_custom_item"]') || {}).value || '';
                var itemLines = items.map(function (name) {
                    var qtyEl = ctx.form.querySelector('[name="otd_qty[' + name + ']"]');
                    return name + ' &times;' + (qtyEl ? qtyEl.value : '1');
                });
                if (custom) {
                    var cqty = (ctx.form.querySelector('[name="otd_custom_qty"]') || {}).value || '1';
                    itemLines.push(custom + ' &times;' + cqty);
                }
                var rows = [
                    ['🥤 Items',   itemLines.join('<br>')],
                    ['📦 Address', ctx.escHtml(ctx.val('otd_address'))],
                    ['📍 Area',    ctx.escHtml(ctx.val('otd_area'))],
                    ['📅 Date',    ctx.escHtml(ctx.val('otd_date'))],
                    ['🕐 Time',    ctx.escHtml(ctx.val('otd_time'))]
                ];
                var html = '<div class="nt-bk-summary-title">Your Order Summary</div><div class="nt-bk-summary-grid">';
                rows.forEach(function (r) {
                    if (!r[1]) return;
                    html += '<div class="nt-bk-summary-row"><span class="nt-bk-summary-label">' + r[0] + '</span><span class="nt-bk-summary-val">' + r[1] + '</span></div>';
                });
                return html + '</div>';
            }
        });
    }

    // ── Init ───────────────────────────────────────────────────────────────────
    $(document).ready(function () {
        initContactForm();
        initBookingWizard();
        initFranchiseWizard();
        initOrderWizard();
        initNativeShare();
    });

}(jQuery));

/* --- history-info.js --- */
(function () {
    'use strict';

    if ( typeof TOTAL_HISTORY_INFO === 'undefined' || TOTAL_HISTORY_INFO <= 0 ) return; 
    let   current = 0;
    let   busy    = false;

    const pages   = Array.from( document.querySelectorAll('.nt-book-page') );
    const dots    = Array.from( document.querySelectorAll('.nt-hdot') );
    const ticks   = Array.from( document.querySelectorAll('.nt-hist-tick') );
    const pfill   = document.getElementById('nt-hist-pfill');
    const leaf    = document.getElementById('nt-turn-leaf');
    const prevBtn = document.getElementById('nt-hist-prev');
    const nextBtn = document.getElementById('nt-hist-next');
    const isMobile = () => window.innerWidth <= 640;

    /* ── Progress / dots / ticks ──────────────────────────────────── */
    function updateProgress( idx ) {
        const pct = Math.round( (idx + 1) / TOTAL_HISTORY_INFO * 100 );
        if ( pfill ) pfill.style.width = pct + '%';
        dots.forEach( (d, i) => {
            d.classList.toggle( 'active', i === idx );
            d.setAttribute( 'aria-selected', i === idx ? 'true' : 'false' );
        });
        ticks.forEach( (t, i) => {
            t.dataset.active = i <= idx ? 'true' : 'false';
        });
    }

    /* ── Reset all pages to clean state ──────────────────────────── */
    function resetAll( activeIdx ) {
        pages.forEach( (p, i) => {
            p.classList.remove('is-active','is-exiting','is-entering','is-rewind-fly');
            p.style.zIndex     = TOTAL_HISTORY_INFO - i;
            p.style.transform  = '';
            p.style.opacity    = '';
            p.style.transition = '';
            p.setAttribute('aria-hidden', i === activeIdx ? 'false' : 'true');
            p.querySelectorAll('.nt-page-turn-trigger').forEach( b =>
                b.setAttribute('tabindex', i === activeIdx ? '0' : '-1')
            );
        });
        pages[ activeIdx ].classList.add('is-active');
    }

    /* ── Peel leaf helper ─────────────────────────────────────────── */
    function fireLeaf( onDone ) {
        if ( isMobile() || !leaf ) { onDone && onDone(); return; }
        leaf.classList.add('is-turning');
        leaf.addEventListener('animationend', function onEnd() {
            leaf.classList.remove('is-turning');
            leaf.removeEventListener('animationend', onEnd);
            onDone && onDone();
        }, { once: true });
    }

    /* ── REWIND: cascade all pages back to idx=0 ─────────────────── */
    function rewindToStart( afterDone ) {
        /*
         * Visually: pages from current down to 1 fly off to the RIGHT
         * in quick succession (staggered), revealing page 0 underneath.
         * Each page fans out with a slight rotation then vanishes.
         */
        const stagger = 90;   /* ms between each page flip */
        const dur     = 320;  /* ms each page takes */
        let   count   = current; /* number of pages to flip back */

        if ( count === 0 ) { afterDone && afterDone(); return; }

        /* Lift z-indices so they stack correctly during cascade */
        for ( let i = current; i >= 1; i-- ) {
            pages[i].style.zIndex = TOTAL_HISTORY_INFO + (current - i) + 2;
        }
        /* Make sure page 0 peeks behind */
        pages[0].style.zIndex    = 1;
        pages[0].style.opacity   = '1';
        pages[0].style.transform = 'translateX(0) scale(1)';

        let completed = 0;

        for ( let i = current; i >= 1; i-- ) {
            const p     = pages[i];
            const delay = (current - i) * stagger;

            setTimeout( () => {
                /* Snap to visible first */
                p.style.transition = 'none';
                p.style.opacity    = '1';
                p.style.transform  = 'translateX(0) rotateY(0deg)';
                void p.offsetWidth; /* reflow */

                /* Then animate off to the right */
                p.style.transition = `opacity ${dur}ms cubic-bezier(.4,0,.2,1), transform ${dur}ms cubic-bezier(.4,0,.2,1)`;
                p.style.transform  = `translateX(55px) rotateY(18deg) scale(0.94)`;
                p.style.opacity    = '0';

                setTimeout( () => {
                    completed++;
                    p.classList.remove('is-active');
                    /* When last page has flown off, settle on page 0 */
                    if ( completed === count ) {
                        resetAll(0);
                        current = 0;
                        updateProgress(0);
                        busy = false;
                        afterDone && afterDone();
                    }
                }, dur + 20 );

            }, delay );
        }
    }

    /* ── WIND-FORWARD: cascade all pages from 0 to TOTAL_HISTORY_INFO-1 ──────── */
    function windToEnd( afterDone ) {
        /*
         * Pages 0 → TOTAL_HISTORY_INFO-2 flip to the LEFT in cascade,
         * revealing the last page underneath.
         */
        const stagger = 90;
        const dur     = 300;
        const count   = TOTAL_HISTORY_INFO - 1 - current;

        if ( count === 0 ) { afterDone && afterDone(); return; }

        /* Ensure last page is visible underneath */
        pages[ TOTAL_HISTORY_INFO - 1 ].style.zIndex  = 1;
        pages[ TOTAL_HISTORY_INFO - 1 ].style.opacity = '1';
        pages[ TOTAL_HISTORY_INFO - 1 ].style.transform = 'translateX(0) scale(1)';

        /* Stack pages we'll flip */
        for ( let i = current; i <= TOTAL_HISTORY_INFO - 2; i++ ) {
            pages[i].style.zIndex = TOTAL_HISTORY_INFO + (TOTAL_HISTORY_INFO - 2 - i) + 2;
        }

        let completed = 0;

        for ( let i = current; i <= TOTAL_HISTORY_INFO - 2; i++ ) {
            const p     = pages[i];
            const delay = (i - current) * stagger;

            setTimeout( () => {
                p.style.transition = 'none';
                p.style.opacity    = '1';
                p.style.transform  = 'translateX(0) rotateY(0deg)';
                void p.offsetWidth;

                p.style.transition = `opacity ${dur}ms cubic-bezier(.4,0,.2,1), transform ${dur}ms cubic-bezier(.4,0,.2,1)`;
                p.style.transform  = `translateX(-55px) rotateY(-18deg) scale(0.94)`;
                p.style.opacity    = '0';

                setTimeout( () => {
                    completed++;
                    p.classList.remove('is-active');
                    if ( completed === count ) {
                        resetAll( TOTAL_HISTORY_INFO - 1 );
                        current = TOTAL_HISTORY_INFO - 1;
                        updateProgress( TOTAL_HISTORY_INFO - 1 );
                        busy = false;
                        afterDone && afterDone();
                    }
                }, dur + 20 );
            }, delay );
        }
    }

    /* ── Normal single-page turn ─────────────────────────────────── */
    function goTo( rawNext ) {
        if ( busy ) return;

        /* Wrap detection */
        const wrapsToStart = rawNext >= TOTAL_HISTORY_INFO;  /* was on last, clicked Next  */
        const wrapsToEnd   = rawNext < 0;       /* was on first, clicked Prev */

        if ( rawNext === current ) return;

        /* Handle wrap-around with cascade */
        if ( wrapsToStart ) {
            busy = true;
            /* Fire a single peel leaf then cascade rewind */
            if ( !isMobile() && leaf ) {
                leaf.classList.add('is-turning');
                leaf.addEventListener('animationend', function onEnd(){
                    leaf.classList.remove('is-turning');
                    leaf.removeEventListener('animationend', onEnd);
                }, { once: true });
            }
            setTimeout( () => rewindToStart(), isMobile() ? 0 : 180 );
            return;
        }

        if ( wrapsToEnd ) {
            busy = true;
            setTimeout( () => windToEnd(), 20 );
            return;
        }

        /* ── Normal forward / backward ── */
        busy = true;
        const next      = rawNext;
        const isForward = next > current;
        const outPage   = pages[ current ];
        const inPage    = pages[ next ];

        if ( isForward && !isMobile() ) {
            fireLeaf( null );
        }

        outPage.classList.remove('is-active');
        outPage.classList.add('is-exiting');
        outPage.setAttribute('aria-hidden', 'true');
        outPage.querySelectorAll('.nt-page-turn-trigger').forEach( b => b.setAttribute('tabindex', '-1') );

        const delay = isForward && !isMobile() ? 200 : 20;
        setTimeout( () => {
            outPage.classList.remove('is-exiting');

            inPage.classList.add('is-entering');
            inPage.style.zIndex = TOTAL_HISTORY_INFO + 10;
            void inPage.offsetWidth;

            inPage.classList.remove('is-entering');
            inPage.classList.add('is-active');
            inPage.setAttribute('aria-hidden', 'false');
            inPage.querySelectorAll('.nt-page-turn-trigger').forEach( b => b.setAttribute('tabindex', '0') );

            pages.forEach( (p, i) => { p.style.zIndex = TOTAL_HISTORY_INFO - i; });
            inPage.style.zIndex = TOTAL_HISTORY_INFO + 1;

            current = next;
            updateProgress( current );
            setTimeout( () => { busy = false; }, 60 );
        }, delay );
    }

    /* ── Event bindings ──────────────────────────────────────────── */
    dots.forEach( (d, i) => d.addEventListener('click', () => goTo(i)) );

    if ( prevBtn ) prevBtn.addEventListener('click', () => goTo( current - 1 ));
    if ( nextBtn ) nextBtn.addEventListener('click', () => goTo( current + 1 ));

    pages.forEach( (p, i) => {
        const trigger = p.querySelector('.nt-page-turn-next');
        if ( trigger ) trigger.addEventListener('click', () => goTo( i + 1 ));
    });

    document.addEventListener('keydown', e => {
        if ( e.key === 'ArrowRight' || e.key === 'ArrowDown' ) goTo( current + 1 );
        if ( e.key === 'ArrowLeft'  || e.key === 'ArrowUp'   ) goTo( current - 1 );
    });

    let touchStartX = 0;
    const book = document.getElementById('nt-book');
    if ( book ) {
        book.addEventListener('touchstart', e => {
            touchStartX = e.touches[0].clientX;
        }, { passive: true });
        book.addEventListener('touchend', e => {
            const dx = e.changedTouches[0].clientX - touchStartX;
            if ( Math.abs(dx) > 45 ) goTo( dx < 0 ? current + 1 : current - 1 );
        }, { passive: true });
    }

    /* ── Init ────────────────────────────────────────────────────── */
    updateProgress(0);

})();
/* --- main.js --- */
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
        var hamburger = document.getElementById('nt-hamburger');
        var mobileNav = document.getElementById('nt-mobile-nav');
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
            if (!e.target.closest('#nt-nav') && !e.target.closest('#nt-mobile-nav')) {
                closeNav();
            }
        });
    }

    // ── Nav Scroll Effect ──────────────────────────────────────────────────────
    function initNavScroll() {
        var nav = document.getElementById('nt-nav');
        if (!nav) return;
        window.addEventListener('scroll', function () {
            nav.classList.toggle('scrolled', window.scrollY > 60);
        }, { passive: true });
    }

    // ── Adaptive Nav (collapse to hamburger when items don't fit) ─────────────
    function initNavPriority() {
        var nav = document.getElementById('nt-nav');
        if (!nav) return;

        var mobileNav = document.getElementById('nt-mobile-nav');
        var hamburger = document.getElementById('nt-hamburger');
        var links     = document.getElementById('nt-nav-links');

        function adjust() {
            var body = document.body;
            // Reset to desktop so we can measure
            body.classList.remove('nav--collapsed');
            if (links) links.style.display = '';
            void nav.offsetWidth;

            var inner = nav.querySelector('.nt-nav__inner');
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
        var toggleBtn = document.getElementById('nt-searnt-toggle');
        var panel     = document.getElementById('nt-searnt-panel');
        var closeBtn  = document.getElementById('nt-searnt-close');
        var input     = document.getElementById('nt-searnt-input');
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
        var track = document.getElementById('nt-reviews-track');
        var prev  = document.getElementById('nt-rev-prev');
        var next  = document.getElementById('nt-rev-next');
        if (!track) return;

        var cards   = track.querySelectorAll('.nt-review-card');
        var current = 0;
        var timer;

        function getDots() {
            return document.querySelectorAll('#nt-nav-dots .nt-dot');
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
        var track = document.getElementById('nt-showcase-track');
        var prev  = document.getElementById('nt-showcase-prev');
        var next  = document.getElementById('nt-showcase-next');
        if (!track) return;

        var cards   = track.querySelectorAll('.nt-showcase-card');
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
        var track = document.getElementById('nt-certs-track');
        var prev  = document.getElementById('nt-certs-prev');
        var next  = document.getElementById('nt-certs-next');
        if (!track) return;

        var cards   = track.querySelectorAll('.nt-cert-card');
        var current = 0;

        function getDots() {
            return document.querySelectorAll('#nt-certs-dots .nt-dot');
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
        var track = document.getElementById('nt-pkg-track');
        var prev  = document.getElementById('nt-pkg-prev');
        var next  = document.getElementById('nt-pkg-next');
        if (!track) return;

        var cards   = track.querySelectorAll('.nt-package-card');
        var current = 0;

        function getDots() { return document.querySelectorAll('#nt-pkg-dots .nt-dot'); }

        function show(idx) {
            // Determine visible count based on viewport width
            const isDesktop = window.innerWidth > 767;
            const visibleCount = isDesktop ? (window.innerWidth > 1200 ? 4 : 3) : 1;
            // Toggle active class for cards within the visible range
            cards.forEach(function (c, i) {
                const inRange = isDesktop ? (i >= idx * visibleCount && i < (idx + 1) * visibleCount) : (i === idx);
                c.classList.toggle('active', inRange);
            });
            // Update navigation dots
            getDots().forEach(function (d, i) {
                d.classList.toggle('active', i === idx);
                d.setAttribute('aria-selected', i === idx ? 'true' : 'false');
            });
            current = idx;
            // Scroll track to show the correct group of cards on desktop
            if (isDesktop) {
                const cardWidth = cards[0].offsetWidth + 32; // include margin
                const targetScroll = idx * visibleCount * cardWidth;
                track.scrollTo({ left: targetScroll, behavior: 'smooth' });
            } else {
                // Mobile: single card scroll into view
                cards[idx].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
            }
        }
        var raf;
        track.addEventListener('scroll', function() {
            const isDesktop = window.innerWidth > 767;
            if (!isDesktop) return; // only for desktop multi-card view
            if (raf) cancelAnimationFrame(raf);
            raf = requestAnimationFrame(function() {
                const cardWidth = cards[0].offsetWidth + 32;
                const visibleCount = window.innerWidth > 1200 ? 4 : 3;
                const idx = Math.round(track.scrollLeft / (cardWidth * visibleCount));
                if (idx !== current && idx >= 0 && idx < Math.ceil(cards.length / visibleCount)) {
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
        var track = document.getElementById('nt-epc-track');
        var prev  = document.getElementById('nt-epc-prev');
        var next  = document.getElementById('nt-epc-next');
        if (!track) return;

        var cards   = track.querySelectorAll('.nt-epc');
        var current = 0;

        function getDots() { return document.querySelectorAll('#nt-epc-dots .nt-dot'); }

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
        var cards   = Array.from(track.querySelectorAll(cardSel));
        var current = 0;

        function getVisibleCount() {
            if (window.innerWidth > 767) {
                return window.innerWidth > 1100 ? 3 : 2;
            }
            return 1;
        }

        function getTotalPages() {
            return Math.ceil(cards.length / getVisibleCount());
        }

        function getDots() { return document.querySelectorAll('#' + dotsId + ' .nt-dot'); }

        function show(pageIdx) {
            var vc = getVisibleCount();
            var total = getTotalPages();
            pageIdx = Math.max(0, Math.min(pageIdx, total - 1));
            current = pageIdx;

            cards.forEach(function (c, i) {
                var inPage = (i >= pageIdx * vc && i < (pageIdx + 1) * vc);
                c.classList.toggle('active', inPage);
            });

            getDots().forEach(function (d, i) {
                d.classList.toggle('active', i === pageIdx);
                d.setAttribute('aria-selected', i === pageIdx ? 'true' : 'false');
            });

            // Scroll track on desktop
            if (window.innerWidth > 767 && cards[pageIdx * vc]) {
                cards[pageIdx * vc].scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'start' });
            }
        }

        function rebuildDots() {
            var dotsContainer = document.getElementById(dotsId);
            if (!dotsContainer) return;
            var total = getTotalPages();
            dotsContainer.innerHTML = '';
            for (var i = 0; i < total; i++) {
                var btn = document.createElement('button');
                btn.className = 'nt-dot' + (i === 0 ? ' active' : '');
                btn.setAttribute('role', 'tab');
                btn.setAttribute('aria-selected', i === 0 ? 'true' : 'false');
                btn.setAttribute('aria-label', 'Page ' + (i + 1));
                (function(idx) { btn.addEventListener('click', function() { show(idx); }); })(i);
                dotsContainer.appendChild(btn);
            }
        }

        function advance() { show(current + 1); }
        function retreat() { show(current - 1); }

        if (next) next.addEventListener('click', advance);
        if (prev) prev.addEventListener('click', retreat);
        addSwipe(track, advance, retreat);

        // Re-init on resize
        var resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                rebuildDots();
                show(0);
            }, 200);
        });

        rebuildDots();
        show(0);
    }
    function initFranchiseCarousels() {
        makeFranchiseCarousel('nt-fwhy-track',  'nt-fwhy-prev',  'nt-fwhy-next',  'nt-fwhy-dots',  '.nt-fw-card');
        makeFranchiseCarousel('nt-fstep-track', 'nt-fstep-prev', 'nt-fstep-next', 'nt-fstep-dots', '.nt-step-card');
        makeFranchiseCarousel('nt-rfr-track',   'nt-rfr-prev',   'nt-rfr-next',   'nt-rfr-dots',   '.nt-rfr-card');
    }

    // ── Gallery Strip Carousels (mobile) ─────────────────────────────────────
    function initGalleryStrips() {
        document.querySelectorAll('.nt-gstrip').forEach(function (gstrip) {
            var id    = gstrip.getAttribute('data-id');
            var track = document.getElementById(id + '-track');
            var prev  = document.getElementById(id + '-prev');
            var next  = document.getElementById(id + '-next');
            if (!track) return;

            var cards   = track.querySelectorAll('.nt-gstrip__card');
            var current = 0;

            function getDots() { return document.querySelectorAll('#' + id + '-dots .nt-dot'); }

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
        var track = document.getElementById('nt-rev-ev-track');
        var prev  = document.getElementById('nt-rev-ev-prev');
        var next  = document.getElementById('nt-rev-ev-next');
        if (!track) return;

        var cards   = track.querySelectorAll('.nt-rev-ev-card');
        var current = 0;

        function getDots() { return document.querySelectorAll('#nt-rev-ev-dots .nt-dot'); }

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
        var items = document.querySelectorAll('.nt-faq-item');
        items.forEach(function (item) {
            var btn = item.querySelector('.nt-faq-question');
            if (!btn) return;
            btn.addEventListener('click', function () {
                var isOpen = item.classList.contains('active');
                items.forEach(function (it) {
                    it.classList.remove('active');
                    var q = it.querySelector('.nt-faq-question');
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
        var toggles = document.querySelectorAll('.nt-footer__acc-toggle');
        toggles.forEach(function (toggle) {
            var body = toggle.parentElement.querySelector('.nt-footer__acc-body');
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
                    var navH = document.getElementById('nt-nav') ? document.getElementById('nt-nav').offsetHeight : 72;
                    window.scrollTo({ top: el.offsetTop - navH - 8, behavior: 'smooth' });
                }
            });
        });
    }

    // ── Story Cards (tabbed reveal) ────────────────────────────────────────────
    function initStoryCards() {
        var section = document.getElementById('story-cards');
        if (!section) return;

        var tabs   = Array.from(section.querySelectorAll('.nt-sc-tab'));
        var panels = Array.from(section.querySelectorAll('.nt-sc-panel'));
        var dots   = Array.from(section.querySelectorAll('.nt-sc-progress-dot'));
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
        section.querySelectorAll('.nt-sc-panel').forEach(function(panel) {
            var content = panel.querySelector('.nt-sc-panel-content');
            var visual  = panel.querySelector('.nt-sc-panel-visual');
            var gallery = visual && visual.querySelector('.nt-sc-gallery');

            if (content) addSwipe(content, nextCard, prevCard);
            if (visual && !gallery) addSwipe(visual, nextCard, prevCard);
        });

        startTimer();
    }

    // ── Story Card Galleries (multi-image crossfade) ───────────────────────────
    function initStoryGalleries() {
        document.querySelectorAll('.nt-sc-gallery').forEach(function (gallery) {
            var imgs = Array.prototype.slice.call(gallery.querySelectorAll('.nt-sc-gallery-img'));
            var dots = Array.prototype.slice.call(gallery.querySelectorAll('.nt-sc-gallery-dot'));
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
        var btn = document.getElementById('nt-scroll-to-top');
        var fill = document.querySelector('.nt-scroll-glass__fill');
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
        document.querySelectorAll('.nt-post-gallery').forEach(function (gallery) {
            var id     = gallery.getAttribute('id');
            var track  = gallery.querySelector('.nt-post-gallery__track');
            var slides = gallery.querySelectorAll('.nt-post-gallery__slide');
            var dots   = gallery.querySelectorAll('.nt-post-gallery__dot');
            var arrows = gallery.querySelectorAll('.nt-post-gallery__arrow');

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
                var isNext = arrow.classList.contains('nt-post-gallery__arrow--next');
                arrow.addEventListener('click', isNext ? advance : retreat);
            });

            // Swipe support
            addSwipe(track, advance, retreat);
        });
    }

    // ── Privacy Policy Modal ───────────────────────────────────────────────────
    function initPrivacyModal() {
        var modal   = document.getElementById('nt-pp-modal');
        var overlay = document.getElementById('nt-pp-overlay');
        if (!modal) return;

        var lastTrigger = null;

        function openModal(triggerEl) {
            lastTrigger = triggerEl || null;
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            var firstClose = modal.querySelector('.nt-pp-close');
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
            var t = e.target.closest('#nt-pp-trigger, .nt-pp-trigger');
            if (t) { e.preventDefault(); openModal(t); }
        });

        if (overlay) overlay.addEventListener('click', closeModal);
        modal.querySelectorAll('.nt-pp-close').forEach(function (btn) {
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
/* --- important-notice.js --- */
/**
 * Important Notice Dialog Component
 * Handles visibility, content-based tracking, and user interactions
 */

(function() {
	'use strict';

	const dialog = document.getElementById('nt-notice');
	const overlay = document.getElementById('nt-notice-overlay');

	if (!dialog || !overlay) return;

	/**
	 * Generate hash from notice content
	 * If content changes, hash changes → shows again even same day
	 */
	function hashNotice() {
		const titleEl = dialog.querySelector('.nt-notice-title');
		const msgEl = dialog.querySelector('.nt-notice-message');
		const btnEl = dialog.querySelector('.nt-notice-btn');
		const imgEl = dialog.querySelector('.nt-notice-image img');

		const content = [
			titleEl ? titleEl.textContent : '',
			msgEl ? msgEl.textContent : '',
			btnEl ? btnEl.textContent : '',
			imgEl ? imgEl.src : ''
		].join('|');

		// Simple djb2-like hash function
		let hash = 5381;
		for (let i = 0; i < content.length; i++) {
			hash = ((hash << 5) + hash) + content.charCodeAt(i);
		}
		return Math.abs(hash).toString(36).substring(0, 12);
	}

	/**
	 * Close dialog with animation
	 */
	function closeDialog() {
		dialog.close();
		overlay.classList.remove('show');
	}

	/**
	 * Initialize notice display
	 */
	function init() {
		// Get unique hash of current notice content
		const noticeHash = hashNotice();
		const today = new Date().toISOString().split('T')[0];
		const storageKey = 'NT_notice_' + noticeHash + '_' + today;

		// Check if this exact notice was already shown today (across all tabs)
		if (localStorage.getItem(storageKey)) {
			return;
		}

		// Show dialog after short delay (let page load first)
		setTimeout(() => {
			dialog.showModal();
			overlay.classList.add('show');
			// Store in localStorage so all tabs share the same "shown today" state
			localStorage.setItem(storageKey, '1');
		}, 500);

		// Attach close handlers
		attachCloseHandlers(closeDialog);
	}

	/**
	 * Attach event listeners for closing dialog
	 */
	function attachCloseHandlers(callback) {
		// Close button click
		const closeBtn = dialog.querySelector('.nt-notice-close');
		if (closeBtn) {
			closeBtn.addEventListener('click', callback);
		}

		// Overlay click
		overlay.addEventListener('click', callback);

		// Escape key
		dialog.addEventListener('cancel', callback);
	}

	// Initialize when DOM is ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();

