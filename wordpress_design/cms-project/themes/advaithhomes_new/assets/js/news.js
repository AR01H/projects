/**
 * news.js - News & Insights page interactions.
 *
 * Features:
 *  - Category filtering: top strip tabs + sidebar category buttons
 *  - Live search: sidebar search box filters visible items by title text
 *  - Load More: reveal additional hidden items on each click
 *
 * All filtering is client-side on the server-rendered DOM.
 * Each news-card and news-list-item carries data-cat="<key>".
 */

( function () {
    'use strict';

    const BATCH_SIZE = 6;

    var activeCategory = 'all';
    var searchQuery    = '';

    /** All filterable items across sections */
    var allItems = [];

    function init() {
        cacheItems();
        bindCategoryTabs();
        bindSidebarCats();
        bindSearch();
        bindLoadMore();
        applyFilter();
    }

    function cacheItems() {
        allItems = Array.prototype.slice.call(
            document.querySelectorAll( '.news-card, .news-list-item' )
        );
    }

    /* ── Category Tabs ─────────────────────────────────────── */

    function bindCategoryTabs() {
        var tabs = document.querySelectorAll( '.news-cat-tab' );
        tabs.forEach( function ( tab ) {
            tab.addEventListener( 'click', function () {
                var cat = tab.getAttribute( 'data-cat' ) || 'all';
                setCategory( cat );
            } );
        } );
    }

    function bindSidebarCats() {
        var btns = document.querySelectorAll( '.sb-cat-btn' );
        btns.forEach( function ( btn ) {
            btn.addEventListener( 'click', function () {
                var cat = btn.getAttribute( 'data-cat' ) || 'all';
                setCategory( cat );
            } );
        } );
    }

    function setCategory( cat ) {
        activeCategory = cat;
        syncTabUI( cat );
        syncSidebarUI( cat );
        resetLoadMore();
        applyFilter();
    }

    function syncTabUI( cat ) {
        document.querySelectorAll( '.news-cat-tab' ).forEach( function ( tab ) {
            var isCurrent = tab.getAttribute( 'data-cat' ) === cat;
            tab.classList.toggle( 'active', isCurrent );
            tab.setAttribute( 'aria-pressed', isCurrent ? 'true' : 'false' );
        } );
    }

    function syncSidebarUI( cat ) {
        document.querySelectorAll( '.sb-cat-item' ).forEach( function ( item ) {
            var btn = item.querySelector( '.sb-cat-btn' );
            if ( btn ) {
                item.classList.toggle( 'active', btn.getAttribute( 'data-cat' ) === cat );
            }
        } );
    }

    /* ── Search ─────────────────────────────────────────────── */

    function bindSearch() {
        var input = document.getElementById( 'newsSearchInput' );
        if ( ! input ) return;

        input.addEventListener( 'input', function () {
            searchQuery = input.value.trim().toLowerCase();
            resetLoadMore();
            applyFilter();
        } );
    }

    /* ── Load More ──────────────────────────────────────────── */

    var visibleCount = 0;
    var matchedItems = [];

    function bindLoadMore() {
        var btn = document.getElementById( 'loadMoreBtn' );
        if ( ! btn ) return;

        btn.addEventListener( 'click', function () {
            revealNextBatch();
        } );
    }

    function resetLoadMore() {
        visibleCount = 0;
    }

    function updateLoadMoreBtn( total ) {
        var btn = document.getElementById( 'loadMoreBtn' );
        if ( ! btn ) return;
        var wrap = btn.parentElement;

        if ( visibleCount >= total ) {
            if ( wrap ) wrap.style.display = 'none';
            btn.disabled = true;
        } else {
            if ( wrap ) wrap.style.display = '';
            btn.disabled = false;
        }
    }

    function revealNextBatch() {
        var end = Math.min( visibleCount + BATCH_SIZE, matchedItems.length );
        for ( var i = visibleCount; i < end; i++ ) {
            matchedItems[ i ].removeAttribute( 'hidden' );
        }
        visibleCount = end;
        updateLoadMoreBtn( matchedItems.length );
        toggleEmptySections();
    }

    /* ── Core Filter Logic ──────────────────────────────────── */

    function applyFilter() {
        /* Hide everything first */
        allItems.forEach( function ( item ) {
            item.setAttribute( 'hidden', '' );
        } );

        /* Build matched set */
        matchedItems = allItems.filter( function ( item ) {
            return matchesCat( item ) && matchesSearch( item );
        } );

        /* Show initial batch */
        var end = Math.min( BATCH_SIZE, matchedItems.length );
        for ( var i = 0; i < end; i++ ) {
            matchedItems[ i ].removeAttribute( 'hidden' );
        }
        visibleCount = end;

        updateLoadMoreBtn( matchedItems.length );
        toggleEmptySections();

        /* Show/hide whole sections based on whether they have visible items */
        toggleSectionVisibility();
    }

    function matchesCat( item ) {
        if ( activeCategory === 'all' ) return true;
        return item.getAttribute( 'data-cat' ) === activeCategory;
    }

    function matchesSearch( item ) {
        if ( ! searchQuery ) return true;
        var titleEl = item.querySelector( 'h3, .fa-title' );
        var text = titleEl ? titleEl.textContent.toLowerCase() : '';
        return text.indexOf( searchQuery ) !== -1;
    }

    function toggleEmptySections() {
        document.querySelectorAll( '.news-section' ).forEach( function ( section ) {
            var hasVisible = section.querySelector( '.news-card:not([hidden]), .news-list-item:not([hidden])' );
            section.toggleAttribute( 'data-hidden', ! hasVisible );
        } );
    }

    function toggleSectionVisibility() {
        toggleEmptySections();
    }

    /* ── Bootstrap ──────────────────────────────────────────── */

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
