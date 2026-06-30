/**
 * cookie-consent.js — Site-wide cookie consent banner.
 *
 * One cookie: 'adn_cookie_consent'
 *   'accepted' → expires in 365 days
 *   'rejected' → expires in 10 days (banner shows again after that automatically)
 *
 * Public API (window.adnCookieConsent):
 *   .getStatus()              → 'accepted' | 'rejected' | '' (not yet decided)
 *   .show(onAccept, onReject) → shows banner; optional one-time callbacks
 *   .accept()                 → programmatically accept
 *   .reject()                 → programmatically reject
 */
( function () {
    'use strict';

    var CK_KEY       = 'adn_cookie_consent';
    var ACCEPT_DAYS  = 365;
    var REJECT_DAYS  = 10;

    var _onAccept = [];
    var _onReject = [];

    /* ── Cookie helpers ───────────────────────────────────────── */

    function ckRead( name ) {
        var pairs = document.cookie.split( ';' );
        for ( var i = 0; i < pairs.length; i++ ) {
            var pair = pairs[ i ].trim();
            var eq   = pair.indexOf( '=' );
            if ( eq === -1 ) { continue; }
            if ( pair.slice( 0, eq ).trim() === name ) {
                return decodeURIComponent( pair.slice( eq + 1 ) );
            }
        }
        return '';
    }

    function ckWrite( name, value, days ) {
        var exp = new Date( Date.now() + days * 86400000 ).toUTCString();
        document.cookie = name + '=' + encodeURIComponent( value )
            + '; path=/; expires=' + exp + '; SameSite=Lax';
    }

    /* ── Status ───────────────────────────────────────────────── */

    function getStatus() {
        return ckRead( CK_KEY ); /* 'accepted' | 'rejected' | '' */
    }

    /* ── Banner DOM ───────────────────────────────────────────── */

    var _banner = null;

    function policyUrl() {
        return ( window.adnConsentCfg && window.adnConsentCfg.policyUrl )
            ? window.adnConsentCfg.policyUrl
            : '/cookie-policy/';
    }

    function buildBanner() {
        var el       = document.createElement( 'div' );
        el.id        = 'adn-cookie-banner';
        el.className = 'adn-cookie-banner';
        el.setAttribute( 'role', 'region' );
        el.setAttribute( 'aria-label', 'Cookie consent' );
        el.innerHTML =
            '<div class="acb-inner">'
          +   '<div class="acb-text">'
          +     '<p class="acb-msg">We use cookies to remember your preferences (such as unlocked expert profiles) and improve your experience. '
          +     'Read our <a href="' + policyUrl() + '" target="_blank" rel="noopener noreferrer" class="acb-link">Cookie Policy</a>.</p>'
          +   '</div>'
          +   '<div class="acb-actions">'
          +     '<button type="button" id="adn-cookie-reject" class="acb-btn acb-btn--reject">Reject</button>'
          +     '<button type="button" id="adn-cookie-accept" class="acb-btn acb-btn--accept">Accept All</button>'
          +   '</div>'
          + '</div>';
        return el;
    }

    function show( onAcceptCb, onRejectCb ) {
        if ( onAcceptCb ) { _onAccept.push( onAcceptCb ); }
        if ( onRejectCb ) { _onReject.push( onRejectCb ); }
        if ( _banner && document.body.contains( _banner ) ) { return; }
        _banner = buildBanner();
        document.body.appendChild( _banner );
        requestAnimationFrame( function () {
            requestAnimationFrame( function () { _banner.classList.add( 'is-visible' ); } );
        } );
        _banner.querySelector( '#adn-cookie-accept' ).addEventListener( 'click', doAccept );
        _banner.querySelector( '#adn-cookie-reject' ).addEventListener( 'click', doReject );
    }

    function hide() {
        if ( ! _banner ) { return; }
        _banner.classList.remove( 'is-visible' );
        var el = _banner;
        _banner = null;
        setTimeout( function () {
            if ( el.parentNode ) { el.parentNode.removeChild( el ); }
        }, 420 );
    }

    /* ── Accept / Reject ──────────────────────────────────────── */

    function doAccept() {
        ckWrite( CK_KEY, 'accepted', ACCEPT_DAYS );
        hide();
        var cbs = _onAccept.splice( 0 );
        cbs.forEach( function ( fn ) { try { fn(); } catch ( e ) {} } );
        _onReject = [];
    }

    function doReject() {
        ckWrite( CK_KEY, 'rejected', REJECT_DAYS );
        hide();
        var cbs = _onReject.splice( 0 );
        cbs.forEach( function ( fn ) { try { fn(); } catch ( e ) {} } );
        _onAccept = [];
    }

    /* ── Auto-show on page load ───────────────────────────────── */

    function init() {
        var s                = getStatus();
        var isCookiePolicyPg = window.adnConsentCfg && !! window.adnConsentCfg.isCookiePolicyPage;

        /*
         * Cookie policy page: show unless accepted (lets rejected users change mind).
         * All other pages: show only when undecided (no cookie set yet).
         */
        var shouldShow = isCookiePolicyPg ? ( s !== 'accepted' ) : ( s === '' );
        if ( shouldShow ) { show(); }
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

    /* ── Public API ───────────────────────────────────────────── */

    window.adnCookieConsent = {
        getStatus : getStatus,
        show      : show,
        accept    : doAccept,
        reject    : doReject,
    };

} )();
