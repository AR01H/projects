/**
 * ask_expert.js — Ask an Expert directory interactions.
 * - Category filter tabs: show/hide expert cards by data-cat
 */

( function () {
    'use strict';

    var activeCategory = 'all';

    function init() {
        bindCategoryTabs();
    }

    function bindCategoryTabs() {
        document.querySelectorAll( '.expert-cat-tab' ).forEach( function ( tab ) {
            tab.addEventListener( 'click', function () {
                var cat = tab.getAttribute( 'data-cat' ) || 'all';
                setCategory( cat );
            } );
        } );
    }

    function setCategory( cat ) {
        activeCategory = cat;
        syncTabUI( cat );
        filterCards( cat );
    }

    function syncTabUI( cat ) {
        document.querySelectorAll( '.expert-cat-tab' ).forEach( function ( tab ) {
            var isCurrent = tab.getAttribute( 'data-cat' ) === cat;
            tab.classList.toggle( 'active', isCurrent );
            tab.setAttribute( 'aria-selected', isCurrent ? 'true' : 'false' );
        } );
    }

    function filterCards( cat ) {
        document.querySelectorAll( '.expert-card' ).forEach( function ( card ) {
            var cardCat = card.getAttribute( 'data-cat' ) || '';
            var show    = cat === 'all' || cardCat === cat;

            if ( show ) {
                card.removeAttribute( 'hidden' );
            } else {
                card.setAttribute( 'hidden', '' );
            }
        } );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
