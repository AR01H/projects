/**
 * analytics-consent.js — Loads Google Tag Manager / GA4 / Google Ads / AdSense
 * only after the matching cookie-preference category is granted, using
 * Google's Consent Mode v2 signals so nothing fires - not even a denied-state
 * ping - before that. Reacts in real time (no page reload) to Manage
 * Preferences via .onCategoryChange():
 *   - GA4                → gated on the "analytics" category (analytics_storage)
 *   - Google Ads/AdSense → gated on the "advertising" category (ad_storage / ad_user_data / ad_personalization)
 *   - GTM                → loaded once EITHER category is granted; the tags
 *     inside the container still respect whichever consent type each one is
 *     configured for in GTM itself.
 *
 * IDs come from Theme Admin → Admin Actions → Tracking & Analytics
 * (window.adnTrackingCfg, localized server-side). Nothing loads if unconfigured.
 */
( function () {
    'use strict';

    var cfg          = window.adnTrackingCfg || {};
    var hasGtm        = !! cfg.gtmId;
    var hasGa4        = !! cfg.ga4Id;
    var hasAds        = !! cfg.adsId;
    var hasAdSense    = !! cfg.adsenseId;

    if ( ! hasGtm && ! hasGa4 && ! hasAds && ! hasAdSense ) { return; }

    window.dataLayer = window.dataLayer || [];
    function gtag() { window.dataLayer.push( arguments ); }
    window.gtag = window.gtag || gtag;

    /* Default-deny every signal until the visitor decides - Google's Consent
       Mode v2 baseline, applied for every visitor regardless of region. */
    gtag( 'consent', 'default', {
        ad_storage:          'denied',
        ad_user_data:        'denied',
        ad_personalization:  'denied',
        analytics_storage:   'denied',
        wait_for_update:     500
    } );

    var gtmLoaded     = false;
    var gtagLibLoaded = false;
    var adsenseLoaded = false;

    function loadScript( src, crossOrigin ) {
        var s   = document.createElement( 'script' );
        s.async = true;
        s.src   = src;
        if ( crossOrigin ) { s.crossOrigin = 'anonymous'; }
        document.head.appendChild( s );
    }

    function loadGtm() {
        if ( ! hasGtm || gtmLoaded ) { return; }
        gtmLoaded = true;
        window.dataLayer.push( { 'gtm.start': new Date().getTime(), event: 'gtm.js' } );
        loadScript( 'https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent( cfg.gtmId ) );
    }

    function ensureGtagLib( bootstrapId ) {
        if ( gtagLibLoaded ) { return; }
        gtagLibLoaded = true;
        loadScript( 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent( bootstrapId ) );
        gtag( 'js', new Date() );
    }

    function loadAdSense() {
        if ( ! hasAdSense || adsenseLoaded ) { return; }
        adsenseLoaded = true;
        loadScript( 'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' + encodeURIComponent( cfg.adsenseId ), true );
    }

    function activateAnalytics() {
        if ( hasGtm ) { loadGtm(); return; } /* GTM owns any GA4 tag configured inside its container. */
        if ( hasGa4 ) { ensureGtagLib( cfg.ga4Id ); gtag( 'config', cfg.ga4Id ); }
    }

    function activateAdvertising() {
        if ( hasGtm ) { loadGtm(); } /* GTM owns any Ads/remarketing tag configured inside its container. */
        else if ( hasAds ) { ensureGtagLib( cfg.adsId ); gtag( 'config', cfg.adsId ); }
        loadAdSense();
    }

    function grantAnalytics() {
        gtag( 'consent', 'update', { analytics_storage: 'granted' } );
        activateAnalytics();
    }
    function denyAnalytics() {
        gtag( 'consent', 'update', { analytics_storage: 'denied' } );
    }

    function grantAdvertising() {
        gtag( 'consent', 'update', {
            ad_storage:         'granted',
            ad_user_data:       'granted',
            ad_personalization: 'granted'
        } );
        activateAdvertising();
    }
    function denyAdvertising() {
        gtag( 'consent', 'update', {
            ad_storage:         'denied',
            ad_user_data:       'denied',
            ad_personalization: 'denied'
        } );
        /* Nothing to unload - a denied visitor never triggered activateAdvertising(),
           so no script was fetched for them in the first place. */
    }

    function ready() {
        var consent = window.adnCookieConsent;
        if ( ! consent || typeof consent.onCategoryChange !== 'function' ) { return; }
        consent.onCategoryChange( 'analytics', grantAnalytics, denyAnalytics );
        consent.onCategoryChange( 'advertising', grantAdvertising, denyAdvertising );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', ready );
    } else {
        ready();
    }

} )();
