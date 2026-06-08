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
        this.track    = el.querySelector( '.ch-carousel__track' );
        this.items    = Array.from( el.querySelectorAll( '.ch-carousel__item' ) );
        this.viewport = el.querySelector( '.ch-carousel__viewport' );
        this.navEl    = el.querySelector( '.ch-carousel__nav' );
        this.dots     = Array.from( el.querySelectorAll( '.ch-carousel__dot' ) );
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
            var card = item.querySelector( '.ch-card--selector' );
            if ( ! card ) return;
            card.addEventListener( 'click', function () {
                self.el.querySelectorAll( '.ch-card--selector' )
                    .forEach( function ( c ) { c.classList.remove( 'is-active' ); } );
                card.classList.add( 'is-active' );
                if ( typeof self.opts.onChange === 'function' ) {
                    self.opts.onChange( i, card.dataset.value || i );
                }
            } );
        } );
        var first = this.el.querySelector( '.ch-card--selector' );
        if ( first ) first.classList.add( 'is-active' );
    };

    /* ── init ───────────────────────────────────────────────── */
    CHCarousel.init = function ( scope ) {
        ( scope || document )
            .querySelectorAll( '.ch-carousel:not([data-carousel-init])' )
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