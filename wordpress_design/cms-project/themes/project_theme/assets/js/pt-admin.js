/**
 * pt-admin.js - AJAX + UI logic for Project Theme admin pages.
 * Loaded only on pt-* admin pages via wp_enqueue_script.
 * Depends on: jQuery, PT_Admin (localized by wp_localize_script).
 *
 * PT_Admin = {
 *   ajaxUrl : '/wp-admin/admin-ajax.php',
 *   nonce   : '...',
 *   apiBase : '/wp-json/pt/v1',
 *   apiNonce: '...'
 * }
 */

/* ── Utility ─────────────────────────────────────────────────────── */

var PT = PT || {};

/**
 * Post an admin-ajax action and return a Promise resolving to the JSON data.
 * @param {string} action   wp_ajax_{action} hook suffix
 * @param {Object} extra    extra POST params
 */
PT.ajax = function( action, extra ) {
	var params = jQuery.extend( { action: action, nonce: PT_Admin.nonce }, extra || {} );
	return jQuery.ajax( {
		url    : PT_Admin.ajaxUrl,
		method : 'POST',
		data   : params,
	} ).then( function( res ) {
		if ( res && res.success ) return res.data;
		return jQuery.Deferred().reject( ( res && res.data && res.data.message ) || 'Unknown error' );
	} );
};

/**
 * Call the REST API (fetch-based).
 * @param {string} path    e.g. '/stories'
 * @param {string} method  GET | POST | PUT | DELETE
 * @param {Object} body    JSON body (optional)
 */
PT.api = function( path, method, body ) {
	method = method || 'GET';
	var opts = {
		method  : method,
		headers : {
			'Content-Type'    : 'application/json',
			'X-WP-Nonce'      : PT_Admin.apiNonce,
		},
	};
	if ( body && method !== 'GET' ) {
		opts.body = JSON.stringify( body );
	}
	return fetch( PT_Admin.apiBase + path, opts ).then( function( r ) {
		return r.json().then( function( data ) {
			if ( ! r.ok ) {
				return Promise.reject( data.message || r.statusText );
			}
			return data;
		} );
	} );
};

/* ── Button helper ───────────────────────────────────────────────── */

/**
 * Set a button into loading/done/error state.
 * @param {HTMLElement} btn
 * @param {'idle'|'loading'|'done'|'error'} state
 */
PT.btnState = function( btn, state ) {
	var $b     = jQuery( btn );
	var orig   = $b.data( 'original-text' ) || $b.text();
	$b.data( 'original-text', orig );

	$b.removeClass( 'pt-btn-loading pt-btn-done pt-btn-error' );
	$b.prop( 'disabled', false );

	if ( state === 'loading' ) {
		$b.addClass( 'pt-btn-loading' ).prop( 'disabled', true ).text( '...' );
	} else if ( state === 'done' ) {
		$b.addClass( 'pt-btn-done' ).text( '✓ Done' );
		setTimeout( function() { PT.btnState( btn, 'idle' ); }, 2200 );
	} else if ( state === 'error' ) {
		$b.addClass( 'pt-btn-error' ).text( '✗ Error' );
		setTimeout( function() { PT.btnState( btn, 'idle' ); }, 3000 );
	} else {
		$b.text( orig );
	}
};

/* ── Inline notice ───────────────────────────────────────────────── */

PT.notice = function( selector, text, type ) {
	type = type || 'ok';
	var $el = jQuery( selector );
	if ( ! $el.length ) return;
	$el.html( '<div class="pt-notice pt-notice--' + type + '">' + jQuery( '<span>' ).text( text ).html() + '</div>' );
};

/* ── Status cards updater ────────────────────────────────────────── */

PT.updateStatusCards = function( counts, schema ) {
	/* counts: {stories: N}  schema: {stories:{exists:bool,...}} */

	if ( counts ) {
		jQuery.each( counts, function( key, val ) {
			jQuery( '[data-count="' + key + '"]' ).text( val );
		} );
	}

	if ( schema ) {
		jQuery.each( schema, function( key, info ) {
			var $badge = jQuery( '[data-schema-badge="' + key + '"]' );
			if ( ! $badge.length ) return;
			if ( info.exists ) {
				$badge.text( 'EXISTS' )
				      .removeClass( 'pt-badge--no' )
				      .addClass( 'pt-badge--yes' );
			} else {
				$badge.text( 'MISSING' )
				      .removeClass( 'pt-badge--yes' )
				      .addClass( 'pt-badge--no' );
			}
		} );
	}
};

/* ── Dashboard AJAX buttons ──────────────────────────────────────── */

jQuery( function( $ ) {

	/* Schema install */
	$( document ).on( 'click', '[data-pt-action="schema-install"]', function( e ) {
		e.preventDefault();
		var btn = this;
		PT.btnState( btn, 'loading' );
		PT.ajax( 'pt_schema_install' )
			.done( function( data ) {
				PT.btnState( btn, 'done' );
				PT.notice( '#pt-ajax-notice', data.message, 'ok' );
				PT.updateStatusCards( data.counts, data.schema );
			} )
			.fail( function( msg ) {
				PT.btnState( btn, 'error' );
				PT.notice( '#pt-ajax-notice', msg || 'Schema install failed.', 'err' );
			} );
	} );

	/* Schema drop */
	$( document ).on( 'click', '[data-pt-action="schema-drop"]', function( e ) {
		e.preventDefault();
		if ( ! window.confirm( 'Drop ALL theme tables? All data will be lost.' ) ) return;
		var btn = this;
		PT.btnState( btn, 'loading' );
		PT.ajax( 'pt_schema_drop' )
			.done( function( data ) {
				PT.btnState( btn, 'done' );
				PT.notice( '#pt-ajax-notice', data.message, 'ok' );
				PT.updateStatusCards( data.counts, data.schema );
			} )
			.fail( function( msg ) {
				PT.btnState( btn, 'error' );
				PT.notice( '#pt-ajax-notice', msg || 'Schema drop failed.', 'err' );
			} );
	} );

	/* Seed mock data */
	$( document ).on( 'click', '[data-pt-action="seed-mock"]', function( e ) {
		e.preventDefault();
		var btn = this;
		PT.btnState( btn, 'loading' );
		PT.ajax( 'pt_seed_mock' )
			.done( function( data ) {
				PT.btnState( btn, 'done' );
				PT.notice( '#pt-ajax-notice', data.message, 'ok' );
				PT.updateStatusCards( data.counts );
			} )
			.fail( function( msg ) {
				PT.btnState( btn, 'error' );
				PT.notice( '#pt-ajax-notice', msg || 'Seeding failed.', 'err' );
			} );
	} );

	/* Cleanup */
	$( document ).on( 'click', '[data-pt-action="cleanup"]', function( e ) {
		e.preventDefault();
		if ( ! window.confirm( 'Delete all rows from all theme tables? Cannot be undone.' ) ) return;
		var btn = this;
		PT.btnState( btn, 'loading' );
		PT.ajax( 'pt_cleanup' )
			.done( function( data ) {
				PT.btnState( btn, 'done' );
				PT.notice( '#pt-ajax-notice', data.message, 'ok' );
				PT.updateStatusCards( data.counts );
			} )
			.fail( function( msg ) {
				PT.btnState( btn, 'error' );
				PT.notice( '#pt-ajax-notice', msg || 'Cleanup failed.', 'err' );
			} );
	} );

	/* Refresh status (poll counts + schema) */
	$( document ).on( 'click', '[data-pt-action="refresh-status"]', function( e ) {
		e.preventDefault();
		var btn = this;
		PT.btnState( btn, 'loading' );
		PT.ajax( 'pt_get_status' )
			.done( function( data ) {
				PT.btnState( btn, 'idle' );
				PT.updateStatusCards( data.counts, data.schema );
			} )
			.fail( function() {
				PT.btnState( btn, 'error' );
			} );
	} );

} );

/* ── API test helper (console) ───────────────────────────────────── */

/**
 * Available in browser console for quick API testing:
 *
 *   PT.api('/stories').then(console.log)
 *   PT.api('/stories', 'POST', {id:'test', title:'Test Story', ...}).then(console.log)
 *   PT.api('/stories/test', 'DELETE').then(console.log)
 *   PT.api('/status').then(console.log)
 */
