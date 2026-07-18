/**
 * calculators.js - Calculators page interactions.
 *
 * Features:
 *  - Category sidebar filter: clicking a sidebar button filters the list
 *    and syncs the filter tabs above the list (and vice-versa)
 *  - Filter tabs: same filter logic, syncs the sidebar
 *  - Search: live filtering by title/description text
 *
 * Each .calc-list-item carries data-category="buying tax" (space-separated).
 * Filtering shows/hides items; popular cards are not filtered.
 */

( function () {
    'use strict';

    var activeCategory = 'all';
    var heroRow;
    var emptyMsg;
    var items;

    function checkHash() {
        var hash = window.location.hash;
        if ( hash ) {
            var cat = decodeURIComponent( hash.substring( 1 ) ).trim().toLowerCase();
            if ( cat ) {
                var pill = document.querySelector( '.calc-pill[data-filter="' + cat + '"]' );
                if ( pill ) {
                    setCategory( cat );
                    var target = document.getElementById( 'calcFilterPills' ) || pill;
                    if ( target ) {
                        target.scrollIntoView( { behavior: 'smooth', block: 'start' } );
                    }
                }
            }
        }
    }

    function init() {
        heroRow = document.querySelector( '.tc-all-hero-row' );
        emptyMsg = document.getElementById( 'calcEmptyState' );
        items = document.querySelectorAll( '.calc-grid .calc-list-item, .calc-grid .calc-card' );

        bindSidebarCats();
        bindFilterTabs();
        bindCalcPills();
        bindSearch();

        /* Delay slightly to let the page settle and render before scrolling */
        setTimeout( checkHash, 100 );
        window.addEventListener( 'hashchange', checkHash );
    }

    /* ── Sidebar category buttons ───────────────────────────── */

    function bindSidebarCats() {
        document.querySelectorAll( '.tools-cat-item' ).forEach( function ( btn ) {
            btn.addEventListener( 'click', function () {
                var cat = btn.getAttribute( 'data-category' ) || 'all';
                setCategory( cat );
            } );
        } );
    }

    /* ── Filter tabs ────────────────────────────────────────── */

    function bindFilterTabs() {
        document.querySelectorAll( '.calc-tab' ).forEach( function ( tab ) {
            tab.addEventListener( 'click', function () {
                var cat = tab.getAttribute( 'data-tab' ) || 'all';
                setCategory( cat );
            } );
        } );
    }

    function bindCalcPills() {
        document.querySelectorAll( '.calc-pill' ).forEach( function ( pill ) {
            pill.addEventListener( 'click', function () {
                var cat = pill.getAttribute( 'data-filter' ) || 'all';
                setCategory( cat );
            } );
        } );
    }

    /* ── Shared filter state ────────────────────────────────── */

    function setCategory( cat ) {
        activeCategory = cat;
        syncSidebarUI( cat );
        syncTabUI( cat );
        syncPillUI( cat );
        filterList( cat );
    }

    function syncSidebarUI( cat ) {
        document.querySelectorAll( '.tools-cat-item' ).forEach( function ( btn ) {
            btn.classList.toggle( 'active', btn.getAttribute( 'data-category' ) === cat );
        } );
    }

    function syncTabUI( cat ) {
        document.querySelectorAll( '.calc-tab' ).forEach( function ( tab ) {
            var isCurrent = tab.getAttribute( 'data-tab' ) === cat;
            tab.classList.toggle( 'active', isCurrent );
            tab.setAttribute( 'aria-selected', isCurrent ? 'true' : 'false' );
        } );
    }

    function syncPillUI( cat ) {
        document.querySelectorAll( '.calc-pill' ).forEach( function ( pill ) {
            var isCurrent = pill.getAttribute( 'data-filter' ) === cat;
            pill.classList.toggle( 'active', isCurrent );
            pill.setAttribute( 'aria-selected', isCurrent ? 'true' : 'false' );
        } );
    }

    function filterList( cat ) {
        if ( heroRow ) {
            heroRow.style.display = cat === 'all' ? '' : 'none';
        }

        var visible = 0;
        items.forEach( function ( item ) {
            var cats = ( item.getAttribute( 'data-category' ) || '' ).split( ' ' ).map( function ( s ) { return s.trim(); } );
            var idx = parseInt( item.getAttribute( 'data-index' ) || '0', 10 );

            var matchesCat = cat === 'all' || cats.indexOf( cat ) !== -1;
            var isDuplicateInHero = cat === 'all' && idx < 4;

            if ( matchesCat && ! isDuplicateInHero ) {
                item.classList.remove( 'calc-filtered-out' );
                item.removeAttribute( 'hidden' );
                visible++;
            } else {
                item.classList.add( 'calc-filtered-out' );
                item.setAttribute( 'hidden', '' );
            }
        } );

        if ( emptyMsg ) {
            var heroVisible = cat === 'all' && heroRow && heroRow.style.display !== 'none';
            emptyMsg.style.display = ( visible === 0 && ! heroVisible ) ? 'flex' : 'none';
        }
    }

    /* ── Search ─────────────────────────────────────────────── */

    function bindSearch() {
        var input = document.getElementById( 'toolSearchInput' );
        if ( ! input ) return;

        input.addEventListener( 'input', function () {
            var q = input.value.trim().toLowerCase();

            if ( ! q ) {
                /* Restore category filter when search is cleared */
                filterList( activeCategory );
                return;
            }

            /* Search overrides category filter */
            document.querySelectorAll( '.calc-list-item, .calc-card' ).forEach( function ( item ) {
                var text = item.textContent.toLowerCase();
                if ( text.indexOf( q ) !== -1 ) {
                    item.removeAttribute( 'hidden' );
                } else {
                    item.setAttribute( 'hidden', '' );
                }
            } );
        } );
    }

    /* ── Bootstrap ──────────────────────────────────────────── */

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();

