/**
 * guides_listing.js - Buying Guides listing page interactions.
 *
 * Features:
 *  - Category filter: left sidebar category buttons
 *  - Search: toolbar search input filters cards by title text
 *  - Sort: latest / most popular / A–Z (client-side reorder)
 *
 * Cards carry data-category (set by PHP from item.category) for filtering.
 * The category filter matches by text label, not a slug key.
 */

( function () {
    'use strict';

    var activeCategory = '';
    var searchQuery    = '';

    var grid      = null;
    var allCards  = [];

    function init() {
        grid     = document.getElementById( 'guidesGrid' );
        allCards = grid ? Array.prototype.slice.call( grid.querySelectorAll( '.guide-listing-card' ) ) : [];

        bindSidebarCats();
        bindSearch();
        bindSort();
        applyFilter();
    }

    /* ── Sidebar category filter ────────────────────────────── */

    function bindSidebarCats() {
        document.querySelectorAll( '.sidebar-cat-item' ).forEach( function ( btn ) {
            btn.addEventListener( 'click', function () {
                var label = btn.getAttribute( 'data-cat' ) || '';
                activeCategory = ( label === activeCategory ) ? '' : label;
                syncCatUI( activeCategory );
                applyFilter();
            } );
        } );
    }

    function syncCatUI( cat ) {
        document.querySelectorAll( '.sidebar-cat-item' ).forEach( function ( btn ) {
            btn.classList.toggle( 'active', btn.getAttribute( 'data-cat' ) === cat );
        } );
    }

    /* ── Search ─────────────────────────────────────────────── */

    function bindSearch() {
        var input = document.getElementById( 'guidesSearchInput' );
        if ( ! input ) return;

        input.addEventListener( 'input', function () {
            searchQuery = input.value.trim().toLowerCase();
            applyFilter();
        } );
    }

    /* ── Sort ───────────────────────────────────────────────── */

    function bindSort() {
        var select = document.getElementById( 'guidesSortSelect' );
        if ( ! select ) return;

        select.addEventListener( 'change', function () {
            sortCards( select.value );
        } );
    }

    function sortCards( mode ) {
        if ( ! grid ) return;

        var cards = Array.prototype.slice.call( grid.querySelectorAll( '.guide-listing-card' ) );

        if ( mode === 'A–5' || mode === 'A–Z' || mode.toLowerCase() === 'a–z' || mode === 'A-Z' ) {
            cards.sort( function ( a, b ) {
                return getText( a ).localeCompare( getText( b ) );
            } );
        }
        /* Latest / Most Popular: restore original DOM order */
        if ( mode === 'Latest' || mode === 'Most Popular' ) {
            cards = allCards.slice();
        }

        cards.forEach( function ( card ) {
            grid.appendChild( card );
        } );

        applyFilter();
    }

    function getText( card ) {
        var el = card.querySelector( '.guide-listing-title' );
        return el ? el.textContent.toLowerCase() : '';
    }

    /* ── Core filter ─────────────────────────────────────────── */

    function applyFilter() {
        if ( ! grid ) return;

        var visibleCount = 0;

        Array.prototype.slice.call( grid.querySelectorAll( '.guide-listing-card' ) ).forEach( function ( card ) {
            var show = matchesCat( card ) && matchesSearch( card );
            if ( show ) {
                card.removeAttribute( 'hidden' );
                visibleCount++;
            } else {
                card.setAttribute( 'hidden', '' );
            }
        } );

        toggleEmpty( visibleCount );
    }

    function matchesCat( card ) {
        if ( ! activeCategory || activeCategory.indexOf( 'All' ) === 0 ) return true;
        var catEl = card.querySelector( '.guide-listing-category' );
        if ( ! catEl ) return false;
        return catEl.textContent.trim().toLowerCase() === activeCategory.toLowerCase();
    }

    function matchesSearch( card ) {
        if ( ! searchQuery ) return true;
        return getText( card ).indexOf( searchQuery ) !== -1;
    }

    function toggleEmpty( count ) {
        var wrap = document.getElementById( 'guidesGrid' );
        var emptyEl = document.getElementById( 'guidesEmpty' );
        if ( ! emptyEl ) return;
        emptyEl.style.display = count === 0 ? '' : 'none';
        if ( wrap ) wrap.style.display = count === 0 ? 'none' : '';
    }

    /* ── Bootstrap ──────────────────────────────────────────── */

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
