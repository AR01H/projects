/**
 * news.js - News & Insights page (API-driven).
 *
 * Cards are NOT server-rendered. This script fetches them from the theme REST
 * endpoint (/api/v1/news) and renders normal news cards, newest-first.
 *
 * Features:
 *  - Category filter: top strip tabs (data-cat="<label-key>") re-query the API
 *  - Live search: #newsSearchInput (debounced) passes ?q= to the API
 *  - Load More: fetches the next page and appends cards
 *  - Graceful loading / empty / error states
 *
 * Config comes from the localized `adnNews` object:
 *   { apiBase, restNonce, perPage, i18n:{ empty, error, loading, loadMore, readMore } }
 */

( function () {
    'use strict';

    var CFG = window.adnNews || {};
    var API = CFG.apiBase || '';
    var PER_PAGE = parseInt( CFG.perPage, 10 ) || 9;
    var DEFAULT_IMG = CFG.defaultImg || '';
    var I18N = CFG.i18n || {};

    // DOM
    var grid, loadingEl, emptyEl, loadMoreWrap, loadMoreBtn, searchInput;

    // State
    var activeCategory = 'all';
    var searchQuery    = '';
    var currentPage    = 0;
    var totalPages     = 1;
    var isLoading      = false;
    var searchTimer    = null;

    function init() {
        grid         = document.getElementById( 'newsGrid' );
        loadingEl    = document.getElementById( 'newsLoading' );
        emptyEl      = document.getElementById( 'newsEmpty' );
        loadMoreWrap = document.getElementById( 'loadMoreWrap' );
        loadMoreBtn  = document.getElementById( 'loadMoreBtn' );
        searchInput  = document.getElementById( 'newsSearchInput' );

        if ( ! grid || ! API ) { return; }

        bindCategoryTabs();
        bindSearch();
        bindLoadMore();

        loadPage( 1, true );
    }

    /* ── Category tabs ──────────────────────────────────────── */

    function bindCategoryTabs() {
        document.querySelectorAll( '.news-cat-tab' ).forEach( function ( tab ) {
            tab.addEventListener( 'click', function () {
                var cat = tab.getAttribute( 'data-cat' ) || 'all';
                if ( cat === activeCategory ) { return; }
                activeCategory = cat;
                syncTabUI( cat );
                loadPage( 1, true );
            } );
        } );
    }

    function syncTabUI( cat ) {
        document.querySelectorAll( '.news-cat-tab' ).forEach( function ( tab ) {
            var on = tab.getAttribute( 'data-cat' ) === cat;
            tab.classList.toggle( 'active', on );
            tab.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
        } );
    }

    /* ── Search (debounced) ─────────────────────────────────── */

    function bindSearch() {
        if ( ! searchInput ) { return; }
        searchInput.addEventListener( 'input', function () {
            clearTimeout( searchTimer );
            searchTimer = setTimeout( function () {
                var q = searchInput.value.trim();
                if ( q === searchQuery ) { return; }
                searchQuery = q;
                loadPage( 1, true );
            }, 280 );
        } );
    }

    /* ── Load more ──────────────────────────────────────────── */

    function bindLoadMore() {
        if ( ! loadMoreBtn ) { return; }
        loadMoreBtn.addEventListener( 'click', function () {
            if ( isLoading || currentPage >= totalPages ) { return; }
            loadPage( currentPage + 1, false );
        } );
    }

    /* ── Skeleton shimmer placeholders ──────────────────────── */

    function addSkeletons( count ) {
        for ( var i = 0; i < count; i++ ) {
            var sk = el( 'div', 'news-skel' );
            sk.innerHTML = '<div class="news-skel-img"></div>'
                + '<div class="news-skel-body">'
                + '<span class="news-skel-line news-skel-line--pill"></span>'
                + '<span class="news-skel-line"></span>'
                + '<span class="news-skel-line news-skel-line--short"></span>'
                + '</div>';
            grid.appendChild( sk );
        }
    }

    function removeSkeletons() {
        grid.querySelectorAll( '.news-skel' ).forEach( function ( sk ) {
            sk.parentNode.removeChild( sk );
        } );
    }

    /* ── Fetch + render ─────────────────────────────────────── */

    function loadPage( page, replace ) {
        if ( isLoading ) { return; }
        isLoading = true;

        if ( replace ) {
            grid.innerHTML = '';
            grid.setAttribute( 'aria-busy', 'true' );
            hide( emptyEl );
            hide( loadMoreWrap );
            hide( loadingEl );
            addSkeletons( Math.min( PER_PAGE, 8 ) );
        } else {
            show( loadingEl );
        }
        if ( loadMoreBtn ) { loadMoreBtn.disabled = true; }

        var url = API
            + '?page=' + encodeURIComponent( page )
            + '&per_page=' + encodeURIComponent( PER_PAGE );
        if ( activeCategory && activeCategory !== 'all' ) {
            url += '&label=' + encodeURIComponent( activeCategory );
        }
        if ( searchQuery ) {
            url += '&q=' + encodeURIComponent( searchQuery );
        }

        var headers = {};
        if ( CFG.restNonce ) { headers['X-WP-Nonce'] = CFG.restNonce; }

        fetch( url, { headers: headers, credentials: 'same-origin' } )
            .then( function ( res ) {
                if ( ! res.ok ) { throw new Error( 'HTTP ' + res.status ); }
                return res.json();
            } )
            .then( function ( json ) {
                var items = ( json && json.data ) || [];
                var meta  = ( json && json.meta ) || {};

                currentPage = parseInt( meta.page, 10 ) || page;
                totalPages  = parseInt( meta.total_pages, 10 ) || 1;

                removeSkeletons();
                items.forEach( function ( item ) {
                    grid.appendChild( buildCard( item ) );
                } );

                hide( loadingEl );
                grid.setAttribute( 'aria-busy', 'false' );

                if ( grid.children.length === 0 ) {
                    show( emptyEl );
                    hide( loadMoreWrap );
                } else {
                    hide( emptyEl );
                    if ( currentPage < totalPages ) {
                        show( loadMoreWrap );
                        if ( loadMoreBtn ) { loadMoreBtn.disabled = false; }
                    } else {
                        hide( loadMoreWrap );
                    }
                }
                isLoading = false;
            } )
            .catch( function () {
                hide( loadingEl );
                removeSkeletons();
                grid.setAttribute( 'aria-busy', 'false' );
                if ( replace ) {
                    grid.innerHTML = '';
                    showError();
                } else if ( loadMoreBtn ) {
                    loadMoreBtn.disabled = false;
                }
                isLoading = false;
            } );
    }

    /* ── Card builder (safe DOM, no innerHTML for user data) ── */

    function buildCard( item ) {
        var url     = item.url || '#';
        var title   = item.title || '';
        var excerpt = item.excerpt || '';
        var label   = item.label || 'News';
        var catKey  = item.cat_key || 'news';
        var date    = item.date || '';
        var read    = item.read_time || '';
        var image   = item.image || DEFAULT_IMG;

        var card = el( 'div', 'news-card' );
        card.setAttribute( 'data-cat', catKey );

        // Image / icon
        var imgLink = el( 'a', 'news-card-img' );
        imgLink.setAttribute( 'href', url );
        imgLink.setAttribute( 'tabindex', '-1' );
        imgLink.setAttribute( 'aria-hidden', 'true' );
        if ( image ) {
            var img = document.createElement( 'img' );
            img.src = image;
            img.alt = title;
            img.loading = 'lazy';
            img.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block;';
            img.onerror = function() {
                this.onerror = null;
                this.src = DEFAULT_IMG;
            };
            imgLink.appendChild( img );
        } else {
            var icon = el( 'span', 'news-card-icon' );
            icon.innerHTML = '<i class="fa-solid fa-newspaper" aria-hidden="true"></i>';
            imgLink.appendChild( icon );
        }
        card.appendChild( imgLink );

        // Body
        var body = el( 'div', 'news-card-body' );

        var pill = el( 'span', 'news-card-cat-pill pill-news-label' );
        pill.textContent = label;
        body.appendChild( pill );

        var h3 = el( 'h3', 'news-card-title' );
        var tLink = el( 'a' );
        tLink.setAttribute( 'href', url );
        tLink.textContent = title;
        h3.appendChild( tLink );
        body.appendChild( h3 );

        if ( excerpt ) {
            var p = el( 'p', 'news-card-excerpt' );
            p.textContent = excerpt;
            body.appendChild( p );
        }

        if ( date || read ) {
            var meta = el( 'div', 'news-card-meta' );
            if ( date ) {
                var d = document.createElement( 'span' );
                d.textContent = date;
                meta.appendChild( d );
            }
            if ( read ) {
                var r = document.createElement( 'span' );
                r.textContent = read;
                meta.appendChild( r );
            }
            body.appendChild( meta );
        }

        card.appendChild( body );
        return card;
    }

    /* ── Helpers ────────────────────────────────────────────── */

    function el( tag, cls ) {
        var n = document.createElement( tag );
        if ( cls ) { n.className = cls; }
        return n;
    }

    function show( node ) { if ( node ) { node.hidden = false; } }
    function hide( node ) { if ( node ) { node.hidden = true; } }

    function showError() {
        if ( ! emptyEl ) { return; }
        var msg = emptyEl.querySelector( 'p' );
        if ( msg ) { msg.textContent = I18N.error || 'Could not load news.'; }
        show( emptyEl );
    }

    /* ── Bootstrap ──────────────────────────────────────────── */

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
