/**
 * assets/js/google-services.js - Plugin-level Google services loader.
 *
 * Enqueued once (see inc/AssetLoader.php -> AH_Asset_Loader::frontend_assets())
 * on EVERY frontend page, from the plugin rather than any one theme, so these
 * functions are available anywhere a site built on this plugin needs them -
 * a page template, a theme's own JS, an admin-entered custom script, etc.
 *
 * Each `initGoogleX()` function:
 *   - takes one ID (string) OR several (array) - handy for multi-property
 *     setups, e.g. tracking a site under two different GA4 properties.
 *   - is idempotent: calling it again (or with overlapping IDs) never loads
 *     the same underlying Google script twice, and never re-configures the
 *     same ID twice.
 *   - shares one `window.dataLayer` / `window.gtag` across Analytics, Ads
 *     and Tag Manager, exactly as Google's own gtag.js does.
 *   - is consent-aware but not consent-DEPENDENT: if a page also loads a
 *     cookie-consent manager exposing `window.adnCookieConsent`, init is
 *     deferred until the relevant category (analytics/advertising) is
 *     granted, then re-checked on every consent change. If no such manager
 *     is present, it just initialises immediately - so this file works the
 *     same on any site, consent tooling or not.
 *
 * Usage (from anywhere, any time after this script has loaded):
 *   initGoogleAnalytics( [ 'G-LVYFMEWP01', 'G-866V46PGZ9', 'G-8RZM0FP5B8' ] );
 *   initGoogleTagManager( 'GTM-XXXXXXX' );
 *   initGoogleAdsConversion( 'AW-123456789' );
 *   initGoogleAdSense( 'ca-pub-1234567890123456' );
 *   initGoogleMaps( 'YOUR_MAPS_API_KEY' );
 *   initGoogleRecaptcha( 'YOUR_SITE_KEY' );
 */
( function ( window, document ) {
	'use strict';

	/** Normalise a single id or an array of ids into a deduped array of strings. */
	function toIdList( ids ) {
		var list = Array.isArray( ids ) ? ids : [ ids ];
		var seen = {};
		var out  = [];
		for ( var i = 0; i < list.length; i++ ) {
			var id = ( list[ i ] || '' ).toString().trim();
			if ( '' === id || seen[ id ] ) { continue; }
			seen[ id ] = true;
			out.push( id );
		}
		return out;
	}

	/** Run `fn` once consent for `category` is granted (immediately if no consent manager is present). */
	function whenConsented( category, fn ) {
		var mgr = window.adnCookieConsent;
		if ( ! mgr || typeof mgr.getCategory !== 'function' ) {
			fn();
			return;
		}
		if ( mgr.getCategory( category ) ) {
			fn();
		}
		if ( typeof mgr.onCategoryChange === 'function' ) {
			mgr.onCategoryChange( category, function ( granted ) {
				if ( granted ) { fn(); }
			} );
		}
	}

	function loadScriptOnce( src, handleKey ) {
		window.__ahGoogleScripts = window.__ahGoogleScripts || {};
		if ( window.__ahGoogleScripts[ handleKey ] ) { return; }
		window.__ahGoogleScripts[ handleKey ] = true;

		var s = document.createElement( 'script' );
		s.async = true;
		s.src   = src;
		document.head.appendChild( s );
	}

	/** Shared gtag.js bootstrap - used by Analytics, Ads and (standalone) Tag Manager config calls. */
	function ensureGtag( bootstrapId ) {
		window.dataLayer = window.dataLayer || [];
		if ( ! window.gtag ) {
			window.gtag = function () { window.dataLayer.push( arguments ); };
		}
		if ( ! window.__ahGtagBooted ) {
			window.__ahGtagBooted = true;
			window.gtag( 'js', new Date() );
		}
		loadScriptOnce( 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent( bootstrapId ), 'gtag' );
	}

	window.__ahGaConfigured = window.__ahGaConfigured || {};

	/**
	 * Google Analytics 4 (GA4). Configures one or more measurement IDs
	 * (e.g. "G-XXXXXXXXXX") against the shared gtag.js.
	 */
	window.initGoogleAnalytics = function ( measurementIds ) {
		var ids = toIdList( measurementIds );
		if ( ! ids.length ) { return; }

		whenConsented( 'analytics', function () {
			ensureGtag( ids[ 0 ] );
			ids.forEach( function ( id ) {
				if ( window.__ahGaConfigured[ id ] ) { return; }
				window.__ahGaConfigured[ id ] = true;
				window.gtag( 'config', id );
			} );
		} );
	};

	/**
	 * Google Ads conversion tracking (e.g. "AW-XXXXXXXXX", optionally with a
	 * "AW-XXXXXXXXX/label" conversion id for gtag('event','conversion',...)
	 * callers build themselves afterwards).
	 */
	window.initGoogleAdsConversion = function ( conversionIds ) {
		var ids = toIdList( conversionIds );
		if ( ! ids.length ) { return; }

		whenConsented( 'advertising', function () {
			ensureGtag( ids[ 0 ] );
			ids.forEach( function ( id ) {
				if ( window.__ahGaConfigured[ id ] ) { return; }
				window.__ahGaConfigured[ id ] = true;
				window.gtag( 'config', id );
			} );
		} );
	};

	window.__ahGtmLoaded = window.__ahGtmLoaded || {};

	/**
	 * Google Tag Manager. Injects the standard <script> loader for each
	 * container id (GTM itself has no consent-relevant network call until
	 * the tags it fires do, but this still respects the same gate for
	 * consistency with the other services here).
	 */
	window.initGoogleTagManager = function ( containerIds ) {
		var ids = toIdList( containerIds );
		if ( ! ids.length ) { return; }

		whenConsented( 'analytics', function () {
			window.dataLayer = window.dataLayer || [];
			ids.forEach( function ( id ) {
				if ( window.__ahGtmLoaded[ id ] ) { return; }
				window.__ahGtmLoaded[ id ] = true;
				window.dataLayer.push( { 'gtm.start': new Date().getTime(), event: 'gtm.js' } );
				loadScriptOnce( 'https://www.googletagmanager.com/gtm.js?id=' + encodeURIComponent( id ), 'gtm-' + id );
			} );
		} );
	};

	window.__ahAdSenseLoaded = window.__ahAdSenseLoaded || {};

	/** Google AdSense - loads the adsbygoogle.js library for one or more publisher client ids. */
	window.initGoogleAdSense = function ( clientIds ) {
		var ids = toIdList( clientIds );
		if ( ! ids.length ) { return; }

		whenConsented( 'advertising', function () {
			ids.forEach( function ( id ) {
				if ( window.__ahAdSenseLoaded[ id ] ) { return; }
				window.__ahAdSenseLoaded[ id ] = true;
				loadScriptOnce(
					'https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' + encodeURIComponent( id ),
					'adsense-' + id
				);
			} );
		} );
	};

	/**
	 * Google Maps JavaScript API. Not consent-gated (no tracking involved) -
	 * loads immediately. Pass a global callback function name if you need
	 * one invoked once the API is ready (matches the official `callback=`
	 * loader param); omit it to just load the library.
	 */
	window.initGoogleMaps = function ( apiKey, callbackFnName ) {
		if ( ! apiKey || window.__ahMapsLoaded ) { return; }
		window.__ahMapsLoaded = true;
		var src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent( apiKey );
		if ( callbackFnName ) { src += '&callback=' + encodeURIComponent( callbackFnName ); }
		loadScriptOnce( src, 'maps' );
	};

	/**
	 * Google reCAPTCHA v3. Not consent-gated (functional/security, not
	 * tracking) - loads immediately.
	 */
	window.initGoogleRecaptcha = function ( siteKey ) {
		if ( ! siteKey || window.__ahRecaptchaLoaded ) { return; }
		window.__ahRecaptchaLoaded = true;
		loadScriptOnce( 'https://www.google.com/recaptcha/api.js?render=' + encodeURIComponent( siteKey ), 'recaptcha' );
	};

}( window, document ) );
