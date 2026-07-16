/**
 * premium.js - Site-wide premium micro-interactions. All additive & safe:
 *
 *  1. Reading progress bar  - thin teal→gold bar at the very top of long
 *     articles (.article-body / .news-single-content). Injected only when
 *     such content exists; pointer-events:none so it can never block clicks.
 *  2. Count-up stats        - .acard-num / .nhs-value numbers animate from 0
 *     when they scroll into view. Skips ranges ("4-5.5%") and respects
 *     prefers-reduced-motion (numbers simply stay as rendered).
 *  3. Hover prefetch        - on link hover/touchstart, prefetch the target
 *     page so navigation feels instant. Same-origin GET pages only; skips
 *     wp-admin, files, and users on data-saver connections.
 *
 * No layout, padding, or DOM reflow changes - overlay + text-only effects.
 */

( function () {
    'use strict';

    var reduceMotion = false;
    try {
        reduceMotion = window.matchMedia && matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
    } catch ( e ) {}

    /* ── 1. Reading progress bar ─────────────────────────────── */

    function initProgressBar() {
        var content = document.querySelector( '.article-body, .news-single-content' );
        if ( ! content ) { return; }

        var bar  = document.createElement( 'div' );
        bar.className = 'adn-progress';
        bar.setAttribute( 'aria-hidden', 'true' );
        var fill = document.createElement( 'span' );
        bar.appendChild( fill );
        document.body.appendChild( bar );

        var ticking = false;
        function update() {
            ticking = false;
            var doc = document.documentElement;
            var max = doc.scrollHeight - window.innerHeight;
            var pct = max > 0 ? Math.min( 100, Math.max( 0, ( window.pageYOffset / max ) * 100 ) ) : 0;
            fill.style.width = pct + '%';
        }
        window.addEventListener( 'scroll', function () {
            if ( ! ticking ) {
                ticking = true;
                window.requestAnimationFrame( update );
            }
        }, { passive: true } );
        update();
    }

    /* ── 2. Count-up stats ───────────────────────────────────── */

    function initCountUp() {
        if ( reduceMotion || ! ( 'IntersectionObserver' in window ) ) { return; }

        var els = document.querySelectorAll( '.acard-num, .nhs-value' );
        if ( ! els.length ) { return; }

        /* Exactly one number, with digit-free prefix/suffix (e.g. "£2,700",
           "33%", "12 weeks"). Ranges like "4-5.5%" fail the match and are
           left untouched. */
        var RE = /^([^0-9]*)([\d,]+(?:\.\d+)?)([^0-9]*)$/;

        function animate( el, prefix, target, suffix, hasComma, decimals ) {
            var start    = null;
            var duration = 900;
            function frame( ts ) {
                if ( null === start ) { start = ts; }
                var p    = Math.min( 1, ( ts - start ) / duration );
                var ease = 1 - Math.pow( 1 - p, 3 );
                var val  = target * ease;
                var txt  = decimals > 0 ? val.toFixed( decimals ) : String( Math.round( val ) );
                if ( hasComma ) {
                    txt = Number( txt ).toLocaleString( 'en-GB', {
                        minimumFractionDigits: decimals,
                        maximumFractionDigits: decimals
                    } );
                }
                el.textContent = prefix + txt + suffix;
                if ( p < 1 ) {
                    window.requestAnimationFrame( frame );
                }
            }
            window.requestAnimationFrame( frame );
        }

        var io = new IntersectionObserver( function ( entries ) {
            entries.forEach( function ( en ) {
                if ( ! en.isIntersecting ) { return; }
                io.unobserve( en.target );
                /* Grow the stat card's accent bar alongside the count-up */
                var statCard = en.target.closest ? en.target.closest( '.acard-stat' ) : null;
                if ( statCard ) { statCard.classList.add( 'adn-stat-live' ); }
                var m = ( en.target.textContent || '' ).trim().match( RE );
                if ( ! m ) { return; }
                var numRaw   = m[2];
                var hasComma = numRaw.indexOf( ',' ) !== -1;
                var decIdx   = numRaw.indexOf( '.' );
                var decimals = decIdx === -1 ? 0 : numRaw.length - decIdx - 1;
                var target   = parseFloat( numRaw.replace( /,/g, '' ) );
                if ( isNaN( target ) || target <= 0 ) { return; }
                animate( en.target, m[1], target, m[3], hasComma, decimals );
            } );
        }, { threshold: 0.4 } );

        els.forEach( function ( el ) { io.observe( el ); } );
    }

    /* ── 3. Hover prefetch ───────────────────────────────────── */

    function initPrefetch() {
        try {
            var conn = navigator.connection;
            if ( conn && ( conn.saveData || /2g/.test( conn.effectiveType || '' ) ) ) { return; }
        } catch ( e ) {}

        var seen = {};

        function prefetchable( a ) {
            if ( ! a || ! a.href || a.hasAttribute( 'download' ) ) { return false; }
            if ( 'true' === a.getAttribute( 'data-no-prefetch' ) ) { return false; }
            if ( a.origin !== window.location.origin ) { return false; }
            var href = a.href.split( '#' )[0];
            if ( ! href || href === window.location.href.split( '#' )[0] ) { return false; }
            if ( /\/wp-admin|\/wp-login|\.(pdf|zip|jpe?g|png|webp|svg|mp4)$/i.test( href ) ) { return false; }
            if ( seen[ href ] ) { return false; }
            return href;
        }

        function add( href ) {
            seen[ href ] = true;
            var l  = document.createElement( 'link' );
            l.rel  = 'prefetch';
            l.href = href;
            l.as   = 'document';
            document.head.appendChild( l );
        }

        document.addEventListener( 'mouseover', function ( e ) {
            var a = e.target && e.target.closest ? e.target.closest( 'a[href]' ) : null;
            var href = prefetchable( a );
            if ( href ) { add( href ); }
        }, { passive: true } );

        document.addEventListener( 'touchstart', function ( e ) {
            var a = e.target && e.target.closest ? e.target.closest( 'a[href]' ) : null;
            var href = prefetchable( a );
            if ( href ) { add( href ); }
        }, { passive: true } );
    }

    /* ── 4. Lazy-image fade-in ───────────────────────────────── */

    function initImageFade() {
        if ( reduceMotion ) { return; }
        document.querySelectorAll( 'img[loading="lazy"]' ).forEach( function ( img ) {
            if ( img.complete ) { return; } // cached — show instantly
            img.classList.add( 'adn-imgfade' );
            function show() { img.classList.add( 'adn-imgloaded' ); }
            img.addEventListener( 'load', show, { once: true } );
            img.addEventListener( 'error', show, { once: true } ); // never leave hidden
        } );
    }

    /* ── 5. Lazy AJAX fragments (home page deferred sections) ── */

    function initFragments() {
        var els = document.querySelectorAll( '.adn-defer[data-fragment]' );
        if ( ! els.length ) { return; }

        function execScripts( scripts ) {
            scripts.forEach( function ( old ) {
                var s = document.createElement( 'script' );
                if ( old.src ) { s.src = old.src; } else { s.textContent = old.textContent; }
                document.body.appendChild( s );
            } );
        }

        function load( el ) {
            if ( el.dataset.adnLoading ) { return; }
            el.dataset.adnLoading = '1';
            var url = el.getAttribute( 'data-endpoint' );
            if ( ! url ) { el.remove(); return; }

            fetch( url, { credentials: 'same-origin' } )
                .then( function ( r ) {
                    if ( ! r.ok ) { throw new Error( 'HTTP ' + r.status ); }
                    return r.json();
                } )
                .then( function ( j ) {
                    var html = ( j && j.html ) ? j.html : '';
                    if ( ! html ) { el.remove(); return; } // section disabled/empty

                    var tpl = document.createElement( 'template' );
                    tpl.innerHTML = html;

                    /* Inline scripts don't execute via innerHTML — pull them
                       out and re-create after insertion. */
                    var scripts = [].slice.call( tpl.content.querySelectorAll( 'script' ) );
                    scripts.forEach( function ( s ) { s.parentNode.removeChild( s ); } );

                    var nodes = [].slice.call( tpl.content.childNodes );
                    el.replaceWith( tpl.content );
                    execScripts( scripts );

                    /* Entrance animation + hook injected content into the
                       scroll-reveal system so nothing stays hidden. */
                    nodes.forEach( function ( n ) {
                        if ( 1 === n.nodeType ) { n.classList.add( 'adn-frag-in' ); }
                    } );
                    if ( window.adnRevealScan ) {
                        window.adnRevealScan();
                    } else {
                        nodes.forEach( function ( n ) {
                            if ( n.querySelectorAll ) {
                                [].forEach.call(
                                    n.querySelectorAll( '.guide-card,.jny-card,.calc-card,.contact-resource-card,.glc,.spotlight-card,.expert-card,.featured-article,.section-header-wrap' ),
                                    function ( x ) { x.classList.add( 'adn-in' ); }
                                );
                            }
                        } );
                    }
                } )
                .catch( function () {
                    el.remove(); // graceful: no endless skeleton
                } );
        }

        [].forEach.call( els, load );
    }

    /* ── Bootstrap ───────────────────────────────────────────── */

    function init() {
        initProgressBar();
        initCountUp();
        initPrefetch();
        initImageFade();
        initFragments();
    }

    if ( 'loading' === document.readyState ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
