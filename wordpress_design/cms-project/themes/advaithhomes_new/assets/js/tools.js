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

    function init() {
        bindSidebarCats();
        bindFilterTabs();
        bindSearch();
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

    /* ── Shared filter state ────────────────────────────────── */

    function setCategory( cat ) {
        activeCategory = cat;
        syncSidebarUI( cat );
        syncTabUI( cat );
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

    function filterList( cat ) {
        document.querySelectorAll( '.calc-list-item' ).forEach( function ( item ) {
            var cats = item.getAttribute( 'data-category' ) || '';
            var show = cat === 'all' || cats.split( ' ' ).indexOf( cat ) !== -1;

            if ( show ) {
                item.removeAttribute( 'hidden' );
            } else {
                item.setAttribute( 'hidden', '' );
            }
        } );
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
            document.querySelectorAll( '.calc-list-item' ).forEach( function ( item ) {
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

