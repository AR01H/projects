/**
 * cookie-consent.js — Site-wide cookie consent banner.
 *
 * One cookie: 'adn_cookie_consent'
 *   'accepted:<v>'            → expires in 365 days
 *   'rejected:<v>:<timestamp>' → expires in 24 hours (banner shows again after that automatically)
 *
 * <v> is an admin-controlled version number (window.adnConsentCfg.acceptVersion /
 * .rejectVersion). If the stored cookie's version doesn't match the current one,
 * it's treated as undecided and the banner shows again - this is how
 * "re-ask everyone" / "re-ask people who rejected" (Theme → Admin Actions →
 * Cache) forces the banner back without the server being able to touch
 * visitors' browsers directly.
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
    var REJECT_DAYS  = 1;
    var REJECT_MS    = REJECT_DAYS * 86400000;

    function acceptVersion() {
        return ( window.adnConsentCfg && window.adnConsentCfg.acceptVersion ) || '1';
    }
    function rejectVersion() {
        return ( window.adnConsentCfg && window.adnConsentCfg.rejectVersion ) || '1';
    }

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

    function ckErase( name ) {
        document.cookie = name + '=; path=/; expires=Thu, 01 Jan 1970 00:00:00 GMT; SameSite=Lax';
    }

    /* ── Status ───────────────────────────────────────────────── */

    function getStatus() {
        var raw = ckRead( CK_KEY ); /* 'accepted:<v>' | 'rejected:<v>:<timestamp>' | '' */

        /* Legacy cookie from before versioning existed - plain 'accepted', no version
           suffix. Honor it as-is instead of erasing it (that was the bug: new code
           misread old-format values as a version mismatch and re-showed the banner
           on every load). */
        if ( raw === 'accepted' ) { return 'accepted'; }

        if ( raw.indexOf( 'accepted:' ) === 0 ) {
            var av = raw.slice( 9 );
            if ( av === acceptVersion() ) { return 'accepted'; }
            /* Admin re-asked everyone since this visitor last accepted. */
            ckErase( CK_KEY );
            return '';
        }

        /* Rejection value is stored as "rejected:<v>:<unix-ms>" so we can verify the
           reject window in JS — independent of whether the browser respects cookie
           expiry. Legacy format (pre-versioning) was "rejected:<unix-ms>" with no
           version segment - detect that by part count and treat it as the current
           version rather than misreading the timestamp as a version string. */
        if ( raw.indexOf( 'rejected:' ) === 0 ) {
            var parts = raw.slice( 9 ).split( ':' );
            var rv, ts;
            if ( parts.length >= 2 ) {
                rv = parts[ 0 ];
                ts = parseInt( parts[ 1 ], 10 );
            } else {
                rv = rejectVersion();
                ts = parseInt( parts[ 0 ], 10 );
            }
            if ( rv !== rejectVersion() ) {
                /* Admin re-asked people who rejected. */
                ckErase( CK_KEY );
                return '';
            }
            var elapsed = Date.now() - ts;
            if ( ! isNaN( ts ) && elapsed < REJECT_MS ) {
                return 'rejected';
            }
            /* Timestamp missing, corrupt, or the reject window has passed — erase and start fresh. */
            ckErase( CK_KEY );
            return '';
        }

        return '';
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
          +     '<p class="acb-msg">We use cookies to remember your preferences and improve your experience on our site. '
          +     'Read our <a href="' + policyUrl() + '" target="_blank" rel="noopener noreferrer" class="acb-link">Cookie Policy</a> to learn more.</p>'
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
        ckWrite( CK_KEY, 'accepted:' + acceptVersion(), ACCEPT_DAYS );
        hide();
        var cbs = _onAccept.splice( 0 );
        cbs.forEach( function ( fn ) { try { fn(); } catch ( e ) {} } );
        _onReject = [];
    }

    function doReject() {
        /* Store rejection version + timestamp so JS can verify the 10-day window and version. */
        ckWrite( CK_KEY, 'rejected:' + rejectVersion() + ':' + Date.now(), REJECT_DAYS );
        hide();
        var cbs = _onReject.splice( 0 );
        cbs.forEach( function ( fn ) { try { fn(); } catch ( e ) {} } );
        _onAccept = [];
    }

    /* ── Auto-show on page load ───────────────────────────────── */

    function init() {
        var s = getStatus();

        /* Already decided (either way) — never show again, on any page, including
           the cookie-policy page itself (clicking the banner's own "Cookie Policy"
           link used to re-trigger it there, which looked like rejecting "didn't stick"). */
        if ( s === 'accepted' || s === 'rejected' ) { return; }

        /* Undecided → show banner. */
        show();
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
