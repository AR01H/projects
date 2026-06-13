/**
 * single.js - WordPress single post interactions.
 *
 * Features:
 *  - TOC generation: scans .article-body h2 headings, assigns ids,
 *    injects anchor links into #tocNav; hides #tocBox when no headings exist
 *  - TOC scroll-spy: highlights the active section link as the user scrolls
 *  - Feedback buttons: toggle aria-pressed, show a brief thank-you state
 *  - Save guide button: toggles saved state (presentational, no persistence yet)
 */

( function () {
    'use strict';

    function init() {
        buildToc();
        bindFeedback();
        bindSaveGuide();
    }

    /* ── TOC generation ──────────────────────────────────────── */

    function buildToc() {
        var nav = document.getElementById( 'tocNav' );
        var box = document.getElementById( 'tocBox' );
        if ( ! nav || ! box ) return;

        var body     = document.querySelector( '.article-body' );
        if ( ! body ) {
            box.hidden = true;
            return;
        }

        var headings = body.querySelectorAll( 'h2' );
        if ( ! headings.length ) {
            box.hidden = true;
            return;
        }

        headings.forEach( function ( h, i ) {
            if ( ! h.id ) {
                h.id = 'article-section-' + ( i + 1 );
            }

            var a  = document.createElement( 'a' );
            a.href = '#' + h.id;
            a.textContent = h.textContent;
            a.className   = 'toc-link';
            nav.appendChild( a );
        } );

        initScrollSpy( headings, nav );
    }

    /* ── Scroll-spy ──────────────────────────────────────────── */

    function initScrollSpy( headings, nav ) {
        if ( ! ( 'IntersectionObserver' in window ) ) return;

        var links = nav.querySelectorAll( '.toc-link' );

        var observer = new IntersectionObserver(
            function ( entries ) {
                entries.forEach( function ( entry ) {
                    if ( ! entry.isIntersecting ) return;
                    var id = entry.target.id;
                    links.forEach( function ( l ) {
                        l.classList.toggle(
                            'toc-active',
                            l.getAttribute( 'href' ) === '#' + id
                        );
                    } );
                } );
            },
            {
                rootMargin: '-10% 0px -80% 0px',
                threshold:  0,
            }
        );

        headings.forEach( function ( h ) { observer.observe( h ); } );

        /* Activate first link immediately */
        if ( links.length ) {
            links[0].classList.add( 'toc-active' );
        }
    }

    /* ── Feedback buttons ────────────────────────────────────── */

    function bindFeedback() {
        var btns = document.querySelectorAll( '.feedback-btn' );
        if ( ! btns.length ) return;

        btns.forEach( function ( btn ) {
            btn.addEventListener( 'click', function () {
                /* Toggle off if already pressed */
                var nowPressed = btn.getAttribute( 'aria-pressed' ) === 'true';
                btns.forEach( function ( b ) { b.setAttribute( 'aria-pressed', 'false' ); } );
                if ( ! nowPressed ) {
                    btn.setAttribute( 'aria-pressed', 'true' );
                }
            } );
        } );
    }

    /* ── Save guide ──────────────────────────────────────────── */

    function bindSaveGuide() {
        var btn = document.querySelector( '.save-guide-btn' );
        if ( ! btn ) return;

        btn.addEventListener( 'click', function () {
            var saved = btn.getAttribute( 'aria-pressed' ) === 'true';
            btn.setAttribute( 'aria-pressed', saved ? 'false' : 'true' );
            btn.textContent = saved ? '🔖 Save Guide' : '✅ Saved';
        } );
    }

    /* ── Bootstrap ───────────────────────────────────────────── */

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
