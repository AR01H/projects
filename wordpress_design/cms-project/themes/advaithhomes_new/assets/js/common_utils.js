/**
 * assets/js/common_utils.js
 *
 * All shared JS utilities — vanilla JS, no dependencies.
 * Loaded site-wide. Exposes window.ADN with the modules below.
 *
 * ADN.Alert    — toast notifications
 * ADN.Dialog   — modal dialogs + confirm
 * ADN.Loader   — loading spinners
 * ADN.ViewAll  — load-more via fetch
 * ADN.Form     — serialize, setErrors
 * ADN.Validate — email / phone / URL / required / full-form check + field markup
 * ADN.Dom      — query, show/hide, aria, delegation, btnLoading
 * ADN.Ajax     — fetch wrapper for admin-ajax.php
 * ADN.Tabs     — data-tab / data-panel strip
 * ADN.Storage  — localStorage / sessionStorage with JSON + safe fallback
 * ADN.Clip     — copy-to-clipboard with visual feedback
 * ADN.Scroll   — smooth scroll-to, body scroll-lock/unlock
 * ADN.Param    — URL query-string read/write without page reload
 * ADN.Utils    — debounce, throttle, ready
 */

( function ( window ) {
	'use strict';


	/* ══════════════════════════════════════════════════════════════
	   ALERT — toast notifications
	   ══════════════════════════════════════════════════════════════ */
	var Alert = {
		show: function ( message, type, duration ) {
			type     = type     || 'info';
			duration = duration !== undefined ? duration : 3000;
			var alert = document.createElement( 'div' );
			alert.className = 'adn-alert adn-alert-' + type;
			alert.setAttribute( 'role', 'status' );
			alert.setAttribute( 'aria-live', 'polite' );
			alert.innerHTML =
				'<div class="adn-alert__content">' +
					'<span class="adn-alert__message">' + this.escapeHtml( message ) + '</span>' +
					'<button class="adn-alert__close" aria-label="Close alert">&times;</button>' +
				'</div>';
			document.body.appendChild( alert );
			setTimeout( function () { alert.classList.add( 'adn-alert--visible' ); }, 10 );
			var self = this;
			alert.querySelector( '.adn-alert__close' ).addEventListener( 'click', function () { self.hide( alert ); } );
			if ( duration > 0 ) { setTimeout( function () { self.hide( alert ); }, duration ); }
			return alert;
		},
		hide: function ( alert ) {
			alert.classList.remove( 'adn-alert--visible' );
			setTimeout( function () { alert.remove(); }, 300 );
		},
		success: function ( msg, dur ) { return this.show( msg, 'success', dur !== undefined ? dur : 3000 ); },
		error:   function ( msg, dur ) { return this.show( msg, 'error',   dur !== undefined ? dur : 5000 ); },
		warning: function ( msg, dur ) { return this.show( msg, 'warning', dur !== undefined ? dur : 4000 ); },
		info:    function ( msg, dur ) { return this.show( msg, 'info',    dur !== undefined ? dur : 3000 ); },
		escapeHtml: function ( text ) {
			var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
			return String( text ).replace( /[&<>"']/g, function ( m ) { return map[ m ]; } );
		}
	};


	/* ══════════════════════════════════════════════════════════════
	   DIALOG — modal dialogs + confirm shortcut
	   ══════════════════════════════════════════════════════════════ */
	var Dialog = {
		create: function ( config ) {
			var title     = config.title     || '';
			var content   = config.content   || '';
			var buttons   = config.buttons   || [];
			var onClose   = config.onClose   || null;
			var className = config.className || '';
			var size      = config.size      || 'medium';
			var self      = this;

			var dialog = document.createElement( 'div' );
			dialog.className = 'adn-modal adn-modal--' + size + ( className ? ' ' + className : '' );
			dialog.setAttribute( 'role', 'dialog' );
			dialog.setAttribute( 'aria-modal', 'true' );
			var uid = 'dt-' + Date.now();
			if ( title ) { dialog.setAttribute( 'aria-labelledby', uid ); }

			var btnsHtml = '';
			if ( buttons.length ) {
				btnsHtml = '<div class="adn-modal__footer">';
				buttons.forEach( function ( btn ) {
					btnsHtml += '<button class="' + ( btn.primary ? 'btn btn-primary' : 'btn btn-secondary' ) + '" data-action="' + btn.action + '">' + self.escapeHtml( btn.label ) + '</button>';
				} );
				btnsHtml += '</div>';
			}

			dialog.innerHTML =
				'<div class="adn-modal__overlay"></div>' +
				'<div class="adn-modal__content">' +
					'<div class="adn-modal__header">' +
						( title ? '<h2 id="' + uid + '" class="adn-modal__title">' + self.escapeHtml( title ) + '</h2>' : '' ) +
						'<button class="adn-modal__close" aria-label="Close dialog">&times;</button>' +
					'</div>' +
					'<div class="adn-modal__body">' + content + '</div>' +
					btnsHtml +
				'</div>';

			dialog.querySelector( '.adn-modal__close' ).addEventListener( 'click', function () { self.close( dialog, onClose ); } );
			dialog.querySelector( '.adn-modal__overlay' ).addEventListener( 'click', function () { self.close( dialog, onClose ); } );
			dialog.querySelectorAll( '[data-action]' ).forEach( function ( btn ) {
				btn.addEventListener( 'click', function () {
					var action = btn.getAttribute( 'data-action' );
					var def    = buttons.find( function ( b ) { return b.action === action; } );
					if ( def && def.onClick ) { def.onClick( dialog ); }
				} );
			} );
			var escHandler = function ( e ) {
				if ( e.key === 'Escape' ) { self.close( dialog, onClose ); document.removeEventListener( 'keydown', escHandler ); }
			};
			document.addEventListener( 'keydown', escHandler );
			return dialog;
		},
		show: function ( dialog ) {
			document.body.appendChild( dialog );
			setTimeout( function () { dialog.classList.add( 'adn-modal--visible' ); }, 10 );
			return dialog;
		},
		close: function ( dialog, callback ) {
			dialog.classList.remove( 'adn-modal--visible' );
			setTimeout( function () { dialog.remove(); if ( callback ) { callback(); } }, 300 );
		},
		confirm: function ( config ) {
			var self = this;
			return this.create( {
				title:   config.title   || 'Confirm',
				content: '<p>' + this.escapeHtml( config.message || '' ) + '</p>',
				buttons: [
					{ label: config.cancelLabel  || 'Cancel',  action: 'cancel',  onClick: function ( d ) { self.close( d, config.onCancel  ); } },
					{ label: config.confirmLabel || 'Confirm', action: 'confirm', primary: true, onClick: function ( d ) { self.close( d, config.onConfirm ); } }
				]
			} );
		},
		escapeHtml: function ( text ) {
			var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
			return String( text ).replace( /[&<>"']/g, function ( m ) { return map[ m ]; } );
		}
	};


	/* ══════════════════════════════════════════════════════════════
	   LOADER — in-container loading spinners
	   ══════════════════════════════════════════════════════════════ */
	var Loader = {
		show: function ( container, message ) {
			message = message || 'Loading...';
			var loader = document.createElement( 'div' );
			loader.className = 'adn-loader';
			loader.innerHTML = '<div class="adn-loader__spinner"></div><p class="adn-loader__text">' + this.escapeHtml( message ) + '</p>';
			container.appendChild( loader );
			return loader;
		},
		hide: function ( loader ) {
			if ( loader && loader.parentNode ) { loader.remove(); }
		},
		escapeHtml: function ( text ) {
			var map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
			return String( text ).replace( /[&<>"']/g, function ( m ) { return map[ m ]; } );
		}
	};


	/* ══════════════════════════════════════════════════════════════
	   VIEW ALL — load-more via fetch
	   ══════════════════════════════════════════════════════════════ */
	var ViewAll = {
		init: function ( config ) {
			var button    = config.button    || null;
			var container = config.container || null;
			var url       = config.url       || '';
			var onSuccess = config.onSuccess || null;
			var onError   = config.onError   || null;
			if ( ! button || ! container || ! url ) { return; }
			button.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				ViewAll.loadMore( button, container, url, onSuccess, onError );
			} );
		},
		loadMore: function ( button, container, url, onSuccess, onError ) {
			var loader = Loader.show( container, 'Loading...' );
			button.disabled = true;
			fetch( url )
				.then( function ( res ) {
					if ( ! res.ok ) { throw new Error( 'HTTP ' + res.status ); }
					return res.text();
				} )
				.then( function ( html ) {
					container.innerHTML += html;
					Loader.hide( loader );
					button.disabled = false;
					if ( onSuccess ) { onSuccess(); }
				} )
				.catch( function ( err ) {
					Loader.hide( loader );
					button.disabled = false;
					Alert.error( 'Failed to load more items' );
					if ( onError ) { onError( err ); }
				} );
		}
	};


	/* ══════════════════════════════════════════════════════════════
	   FORM — serialize + setErrors (simple helpers)
	   ══════════════════════════════════════════════════════════════ */
	var Form = {
		serialize: function ( form ) {
			return Object.fromEntries( new FormData( form ) );
		},
		setErrors: function ( form, errors ) {
			form.querySelectorAll( '.adn-form-error' ).forEach( function ( el ) { el.remove(); } );
			errors.forEach( function ( error ) {
				var el = document.createElement( 'div' );
				el.className   = 'adn-form-error';
				el.textContent = error;
				form.appendChild( el );
			} );
		}
	};


	/* ══════════════════════════════════════════════════════════════
	   VALIDATE — email / phone / URL / required + full-form check
	   ══════════════════════════════════════════════════════════════ */
	var Validate = {
		email: function ( v ) {
			return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test( ( v || '' ).trim() );
		},
		phone: function ( v ) {
			return /^[\d\s+\-().]{7,20}$/.test( ( v || '' ).trim() );
		},
		url: function ( v ) {
			try { new URL( v ); return true; } catch ( _ ) { return false; }
		},
		required: function ( v ) {
			return v !== null && v !== undefined && String( v ).trim() !== '';
		},

		/**
		 * Validate values against rules.
		 * values : plain object { field: value } OR a <form> element
		 * rules  : { field: ['required','email'] }
		 * Returns { valid: bool, errors: { field: 'message' } }
		 */
		form: function ( values, rules ) {
			var isForm = values instanceof HTMLElement;
			var errors = {};
			Object.keys( rules ).forEach( function ( field ) {
				var val = isForm
					? ( ( values.querySelector( '[name="' + field + '"]' ) || {} ).value || '' )
					: values[ field ];
				var checks = rules[ field ];
				if ( checks.indexOf( 'required' ) !== -1 && ! Validate.required( val ) ) {
					errors[ field ] = field.replace( /_/g, ' ' ) + ' is required';
				} else if ( val && checks.indexOf( 'email' ) !== -1 && ! Validate.email( val ) ) {
					errors[ field ] = 'Please enter a valid email address';
				} else if ( val && checks.indexOf( 'phone' ) !== -1 && ! Validate.phone( val ) ) {
					errors[ field ] = 'Please enter a valid phone number';
				} else if ( val && checks.indexOf( 'url' ) !== -1 && ! Validate.url( val ) ) {
					errors[ field ] = 'Please enter a valid URL';
				}
			} );
			return { valid: Object.keys( errors ).length === 0, errors: errors };
		},

		/** Add .is-error class + message span below the input. */
		markError: function ( input, message ) {
			Validate.clearError( input );
			if ( ! input ) { return; }
			input.classList.add( 'is-error' );
			var msg = document.createElement( 'span' );
			msg.className   = 'field-error-msg';
			msg.textContent = message;
			input.parentNode.appendChild( msg );
		},

		/** Remove .is-error + message span from a single input. */
		clearError: function ( input ) {
			if ( ! input || ! input.parentNode ) { return; }
			input.classList.remove( 'is-error' );
			var existing = input.parentNode.querySelector( '.field-error-msg' );
			if ( existing ) { existing.remove(); }
		},

		/** Clear all validation markup inside a form. */
		clearAll: function ( form ) {
			form.querySelectorAll( '.is-error' ).forEach( function ( el ) { el.classList.remove( 'is-error' ); } );
			form.querySelectorAll( '.field-error-msg' ).forEach( function ( el ) { el.remove(); } );
		}
	};


	/* ══════════════════════════════════════════════════════════════
	   DOM — query, show/hide, aria, delegation, helpers
	   ══════════════════════════════════════════════════════════════ */
	var Dom = {

		/** Single element (returns null if missing). */
		qs: function ( sel, ctx ) {
			return ( ctx || document ).querySelector( sel );
		},

		/** All matching elements as a plain Array. */
		qsa: function ( sel, ctx ) {
			return [].slice.call( ( ctx || document ).querySelectorAll( sel ) );
		},

		/**
		 * Add event listener. Returns a remover function.
		 *   var off = ADN.Dom.on(el, 'click', handler);
		 *   off(); // removes it
		 */
		on: function ( el, event, handler, opts ) {
			if ( ! el ) { return function () {}; }
			el.addEventListener( event, handler, opts || false );
			return function () { el.removeEventListener( event, handler, opts || false ); };
		},

		/**
		 * Delegated listener — fires when a child matching `selector` triggers the event.
		 *   ADN.Dom.delegate(list, 'click', '.item-btn', handler);
		 */
		delegate: function ( parent, event, selector, handler ) {
			return Dom.on( parent || document, event, function ( e ) {
				var target = e.target.closest( selector );
				if ( target && ( parent || document ).contains( target ) ) {
					handler.call( target, e );
				}
			} );
		},

		/** Show — removes hidden attr + sets aria-hidden="false". */
		show: function ( el ) {
			if ( ! el ) { return; }
			el.removeAttribute( 'hidden' );
			el.setAttribute( 'aria-hidden', 'false' );
		},

		/** Hide — sets hidden attr + aria-hidden="true". */
		hide: function ( el ) {
			if ( ! el ) { return; }
			el.setAttribute( 'hidden', '' );
			el.setAttribute( 'aria-hidden', 'true' );
		},

		/** Toggle. Pass force=true to show, false to hide. */
		toggle: function ( el, force ) {
			if ( ! el ) { return; }
			var shouldHide = ( typeof force !== 'undefined' ) ? ! force : ! el.hasAttribute( 'hidden' );
			shouldHide ? Dom.hide( el ) : Dom.show( el );
		},

		/** Create element with optional className and attribute map. */
		make: function ( tag, className, attrs ) {
			var el = document.createElement( tag );
			if ( className ) { el.className = className; }
			if ( attrs ) {
				Object.keys( attrs ).forEach( function ( k ) { el.setAttribute( k, attrs[ k ] ); } );
			}
			return el;
		},

		/**
		 * Put a button into loading state. Returns a restore function.
		 *   var restore = ADN.Dom.btnLoading(btn, 'Saving…');
		 *   fetch(...).finally(restore);
		 */
		btnLoading: function ( btn, loadingText ) {
			if ( ! btn ) { return function () {}; }
			var origHTML     = btn.innerHTML;
			var origDisabled = btn.disabled;
			btn.disabled  = true;
			btn.innerHTML = loadingText || 'Loading&hellip;';
			return function () { btn.disabled = origDisabled; btn.innerHTML = origHTML; };
		},

		/** Add/remove a class after `delay` ms — handy for CSS transitions. */
		classAfter: function ( el, cls, delay, remove ) {
			if ( ! el ) { return; }
			setTimeout( function () {
				if ( remove ) { el.classList.remove( cls ); } else { el.classList.add( cls ); }
			}, delay || 0 );
		}
	};


	/* ══════════════════════════════════════════════════════════════
	   AJAX — fetch wrapper for WP admin-ajax.php
	   ══════════════════════════════════════════════════════════════ */
	var Ajax = {

		/**
		 * POST to admin-ajax.php.
		 * opts { action, nonce, data, ajaxUrl, btn, btnText, onSuccess, onError }
		 */
		post: function ( opts ) {
			var url = opts.ajaxUrl
				|| ( window.adnVars  && window.adnVars.ajaxUrl )
				|| window.ajaxurl
				|| '';
			if ( ! url ) {
				console.warn( 'ADN.Ajax.post: no ajaxUrl' );
				if ( opts.onError ) { opts.onError( new Error( 'No ajaxUrl' ) ); }
				return;
			}
			var restore = opts.btn ? Dom.btnLoading( opts.btn, opts.btnText ) : function () {};
			var fd = new FormData();
			fd.append( 'action', opts.action || '' );
			if ( opts.nonce ) { fd.append( 'nonce', opts.nonce ); }
			var payload = opts.data || {};
			Object.keys( payload ).forEach( function ( k ) { fd.append( k, payload[ k ] ); } );

			fetch( url, { method: 'POST', body: fd } )
				.then( function ( res ) {
					if ( ! res.ok ) { throw new Error( 'HTTP ' + res.status ); }
					return res.json();
				} )
				.then( function ( json ) { restore(); if ( opts.onSuccess ) { opts.onSuccess( json ); } } )
				.catch( function ( err ) { restore(); if ( opts.onError ) { opts.onError( err ); } } );
		},

		/** GET a URL → Promise resolving to parsed JSON. */
		get: function ( url ) {
			return fetch( url, { headers: { 'Accept': 'application/json' } } )
				.then( function ( res ) {
					if ( ! res.ok ) { throw new Error( 'HTTP ' + res.status ); }
					return res.json();
				} );
		}
	};


	/* ══════════════════════════════════════════════════════════════
	   TABS — data-tab / data-panel strip
	   ══════════════════════════════════════════════════════════════ */
	var Tabs = {

		/**
		 * Wire a tab strip inside a container.
		 *
		 * Markup:
		 *   <button data-tab="key">Label</button>
		 *   <div    data-panel="key">...</div>
		 *
		 * opts { container, onChange, initial }
		 * Returns { activate(key) }
		 */
		init: function ( opts ) {
			opts = opts || {};
			var container = opts.container || document;
			var onChange  = opts.onChange  || null;
			var tabs      = Dom.qsa( '[data-tab]',   container );
			var panels    = Dom.qsa( '[data-panel]', container );

			if ( ! tabs.length ) { return { activate: function () {} }; }

			function activate( key ) {
				tabs.forEach( function ( t ) {
					var active = t.getAttribute( 'data-tab' ) === key;
					t.classList.toggle( 'active', active );
					t.setAttribute( 'aria-selected', active ? 'true' : 'false' );
				} );
				panels.forEach( function ( p ) {
					Dom.toggle( p, p.getAttribute( 'data-panel' ) === key );
				} );
				if ( onChange ) { onChange( key ); }
			}

			tabs.forEach( function ( t ) {
				Dom.on( t, 'click', function () { activate( t.getAttribute( 'data-tab' ) ); } );
			} );

			var firstActive = tabs.filter( function ( t ) { return t.classList.contains( 'active' ); } )[ 0 ];
			activate( opts.initial || ( firstActive ? firstActive.getAttribute( 'data-tab' ) : tabs[ 0 ].getAttribute( 'data-tab' ) ) );

			return { activate: activate };
		}
	};


	/* ══════════════════════════════════════════════════════════════
	   STORAGE — localStorage / sessionStorage with JSON + safe fallback
	   ══════════════════════════════════════════════════════════════ */
	var Storage = {
		_store: function ( type ) {
			try { return window[ type ]; } catch ( _ ) { return null; }
		},
		get: function ( key, defaultVal, type ) {
			var store = Storage._store( type || 'localStorage' );
			if ( ! store ) { return defaultVal !== undefined ? defaultVal : null; }
			try {
				var raw = store.getItem( key );
				if ( raw === null ) { return defaultVal !== undefined ? defaultVal : null; }
				return JSON.parse( raw );
			} catch ( _ ) { return defaultVal !== undefined ? defaultVal : null; }
		},
		set: function ( key, value, type ) {
			var store = Storage._store( type || 'localStorage' );
			if ( ! store ) { return false; }
			try { store.setItem( key, JSON.stringify( value ) ); return true; } catch ( _ ) { return false; }
		},
		remove: function ( key, type ) {
			var store = Storage._store( type || 'localStorage' );
			if ( ! store ) { return; }
			try { store.removeItem( key ); } catch ( _ ) {}
		},
		session: {
			get:    function ( k, d ) { return Storage.get( k, d, 'sessionStorage' ); },
			set:    function ( k, v ) { return Storage.set( k, v, 'sessionStorage' ); },
			remove: function ( k )    { return Storage.remove( k, 'sessionStorage' ); }
		}
	};


	/* ══════════════════════════════════════════════════════════════
	   CLIP — copy-to-clipboard with visual feedback
	   ══════════════════════════════════════════════════════════════ */
	var Clip = {

		/**
		 * opts { text, trigger, successText, errorText, duration, onSuccess, onError }
		 *
		 *   ADN.Clip.copy({ text: url, trigger: copyBtn });
		 */
		copy: function ( opts ) {
			var text        = opts.text        || '';
			var trigger     = opts.trigger     || null;
			var successText = opts.successText || 'Copied!';
			var errorText   = opts.errorText   || 'Error';
			var duration    = opts.duration    || 1800;

			function feedback( ok ) {
				if ( trigger ) {
					var orig = trigger.textContent;
					trigger.textContent = ok ? successText : errorText;
					trigger.classList.toggle( 'is-copied', ok );
					setTimeout( function () { trigger.textContent = orig; trigger.classList.remove( 'is-copied' ); }, duration );
				}
				if ( ok  && opts.onSuccess ) { opts.onSuccess(); }
				if ( ! ok && opts.onError )  { opts.onError(); }
			}

			if ( navigator.clipboard && navigator.clipboard.writeText ) {
				navigator.clipboard.writeText( text ).then(
					function () { feedback( true ); },
					function () { feedback( false ); }
				);
			} else {
				var ta = document.createElement( 'textarea' );
				ta.value = text;
				ta.style.cssText = 'position:fixed;top:-9999px;left:-9999px;opacity:0';
				document.body.appendChild( ta );
				ta.select();
				var ok = false;
				try { ok = document.execCommand( 'copy' ); } catch ( _ ) {}
				document.body.removeChild( ta );
				feedback( ok );
			}
		}
	};


	/* ══════════════════════════════════════════════════════════════
	   SCROLL — smooth scroll-to, body lock/unlock, in-view check
	   ══════════════════════════════════════════════════════════════ */
	var Scroll = {

		/**
		 * Smooth-scroll window to an element.
		 * offset: px subtracted from top (accounts for sticky header; default 80).
		 */
		to: function ( el, offset ) {
			if ( ! el ) { return; }
			var top = el.getBoundingClientRect().top + window.scrollY - ( offset !== undefined ? offset : 80 );
			window.scrollTo( { top: top, behavior: 'smooth' } );
		},

		/** Scroll to an absolute y position. */
		toY: function ( y ) {
			window.scrollTo( { top: y, behavior: 'smooth' } );
		},

		/** Lock body scroll (e.g. while a modal is open). Compensates for scrollbar width. */
		lock: function () {
			var sb = window.innerWidth - document.documentElement.clientWidth;
			document.body.style.overflow     = 'hidden';
			document.body.style.paddingRight = sb + 'px';
		},

		/** Restore body scroll. */
		unlock: function () {
			document.body.style.overflow     = '';
			document.body.style.paddingRight = '';
		},

		/** True if an element is currently in the viewport. */
		inView: function ( el, threshold ) {
			if ( ! el ) { return false; }
			var rect = el.getBoundingClientRect();
			var lim  = threshold || 0;
			return rect.top < ( window.innerHeight - lim ) && rect.bottom > lim;
		}
	};


	/* ══════════════════════════════════════════════════════════════
	   PARAM — URL query-string read/write without page reload
	   ══════════════════════════════════════════════════════════════ */
	var Param = {

		/** Get a single param value, or null. */
		get: function ( key ) {
			return new URLSearchParams( window.location.search ).get( key );
		},

		/** All params as a plain { key: value } object. */
		all: function () {
			var result = {};
			new URLSearchParams( window.location.search ).forEach( function ( v, k ) { result[ k ] = v; } );
			return result;
		},

		/**
		 * Set/update params via history.replaceState (no reload).
		 * Pass null or '' as value to remove that param.
		 *
		 *   ADN.Param.set({ page: 2, sort: 'name' });
		 *   ADN.Param.set({ filter: null }); // removes filter
		 */
		set: function ( params ) {
			var sp = new URLSearchParams( window.location.search );
			Object.keys( params ).forEach( function ( k ) {
				if ( params[ k ] === null || params[ k ] === '' ) { sp.delete( k ); }
				else { sp.set( k, params[ k ] ); }
			} );
			var q   = sp.toString();
			var url = window.location.pathname + ( q ? '?' + q : '' ) + window.location.hash;
			window.history.replaceState( null, '', url );
		},

		/** Remove a single param from the URL. */
		remove: function ( key ) {
			var p = {};
			p[ key ] = null;
			Param.set( p );
		}
	};


	/* ══════════════════════════════════════════════════════════════
	   UTILS — debounce, throttle, ready
	   ══════════════════════════════════════════════════════════════ */
	var Utils = {
		debounce: function ( func, wait ) {
			var timeout;
			return function () {
				var ctx  = this;
				var args = arguments;
				clearTimeout( timeout );
				timeout = setTimeout( function () { func.apply( ctx, args ); }, wait );
			};
		},
		throttle: function ( func, limit ) {
			var inThrottle;
			return function () {
				var ctx  = this;
				var args = arguments;
				if ( ! inThrottle ) {
					func.apply( ctx, args );
					inThrottle = true;
					setTimeout( function () { inThrottle = false; }, limit );
				}
			};
		},
		ready: function ( callback ) {
			if ( document.readyState !== 'loading' ) { callback(); }
			else { document.addEventListener( 'DOMContentLoaded', callback ); }
		}
	};


	/* ══════════════════════════════════════════════════════════════
	   FULL SCREEN DIALOG — iframe overlay that covers the whole screen
	   ══════════════════════════════════════════════════════════════ */
	var FullScreenDialog = {

		_current: null,

		/**
		 * Open a full-screen iframe dialog.
		 *
		 * opts {
		 *   url      : string  — URL to load in the iframe (required)
		 *   title    : string  — text shown in the top bar (optional)
		 *   onClose  : fn      — called when the dialog is closed
		 *   onLoad   : fn      — called when the iframe finishes loading
		 *   closable : bool    — show close button / allow ESC (default true)
		 * }
		 *
		 * Returns the dialog element.
		 *
		 * Example:
		 *   ADN.FullScreenDialog.open({
		 *       url:   '/guides/buying-a-home/',
		 *       title: 'Buying a Home',
		 *       onClose: function() { console.log('closed'); }
		 *   });
		 */
		open: function ( opts ) {
			opts = opts || {};
			if ( ! opts.url ) { console.warn( 'ADN.FullScreenDialog.open: url is required' ); return null; }

			/* Close any existing dialog first */
			if ( FullScreenDialog._current ) { FullScreenDialog.close(); }

			var closable = opts.closable !== false;
			var title    = opts.title   || '';

			/* ── Wrapper ── */
			var wrap = Dom.make( 'div', 'adn-fsd', {
				role:          'dialog',
				'aria-modal':  'true',
				'aria-label':  title || 'Full screen view'
			} );

			/* ── Top bar ── */
			var bar = Dom.make( 'div', 'adn-fsd__bar' );

			var titleEl = Dom.make( 'span', 'adn-fsd__title' );
			titleEl.textContent = title;
			bar.appendChild( titleEl );

			if ( closable ) {
				var closeBtn = Dom.make( 'button', 'adn-fsd__close', { 'aria-label': 'Close' } );
				closeBtn.innerHTML = '&#x2715;';
				closeBtn.addEventListener( 'click', function () { FullScreenDialog.close( opts.onClose ); } );
				bar.appendChild( closeBtn );
			}

			wrap.appendChild( bar );

			/* ── Loading spinner (shown until iframe fires load) ── */
			var spinner = Dom.make( 'div', 'adn-fsd__spinner' );
			spinner.innerHTML =
				'<div class="adn-fsd__spin-ring"></div>' +
				'<p class="adn-fsd__spin-label">Loading&hellip;</p>';
			wrap.appendChild( spinner );

			/* ── iframe ── */
			var frame = Dom.make( 'iframe', 'adn-fsd__frame', {
				src:             opts.url,
				frameborder:     '0',
				allowfullscreen: 'true',
				'aria-hidden':   'true'          /* hidden until loaded */
			} );

			frame.addEventListener( 'load', function () {
				Dom.hide( spinner );
				frame.removeAttribute( 'aria-hidden' );
				frame.focus();
				if ( opts.onLoad ) { opts.onLoad( frame ); }
			} );

			wrap.appendChild( frame );

			/* ── Mount ── */
			document.body.appendChild( wrap );
			Scroll.lock();

			/* Trigger CSS transition */
			setTimeout( function () { wrap.classList.add( 'adn-fsd--open' ); }, 10 );

			/* ESC to close */
			var escHandler = function ( e ) {
				if ( ( e.key === 'Escape' || e.keyCode === 27 ) && closable ) {
					FullScreenDialog.close( opts.onClose );
					document.removeEventListener( 'keydown', escHandler );
				}
			};
			document.addEventListener( 'keydown', escHandler );
			wrap._escHandler = escHandler;

			FullScreenDialog._current = wrap;
			return wrap;
		},

		/**
		 * Update the title shown in the top bar of the currently open dialog.
		 *
		 *   ADN.FullScreenDialog.setTitle('New Page Title');
		 */
		setTitle: function ( title ) {
			var wrap = FullScreenDialog._current;
			if ( ! wrap ) { return; }
			var el = wrap.querySelector( '.adn-fsd__title' );
			if ( el ) { el.textContent = title || ''; }
		},

		/**
		 * Navigate the iframe to a new URL without closing the dialog.
		 *
		 *   ADN.FullScreenDialog.navigate('/guides/selling/');
		 */
		navigate: function ( url, newTitle ) {
			var wrap = FullScreenDialog._current;
			if ( ! wrap ) { return; }
			var frame   = wrap.querySelector( '.adn-fsd__frame' );
			var spinner = wrap.querySelector( '.adn-fsd__spinner' );
			if ( ! frame ) { return; }

			Dom.show( spinner );
			frame.setAttribute( 'aria-hidden', 'true' );
			frame.src = url;
			if ( newTitle !== undefined ) { FullScreenDialog.setTitle( newTitle ); }
		},

		/**
		 * Close the current full-screen dialog.
		 *
		 *   ADN.FullScreenDialog.close();
		 */
		close: function ( callback ) {
			var wrap = FullScreenDialog._current;
			if ( ! wrap ) { return; }

			wrap.classList.remove( 'adn-fsd--open' );

			if ( wrap._escHandler ) {
				document.removeEventListener( 'keydown', wrap._escHandler );
			}

			setTimeout( function () {
				if ( wrap.parentNode ) { wrap.parentNode.removeChild( wrap ); }
				Scroll.unlock();
				FullScreenDialog._current = null;
				if ( callback ) { callback(); }
			}, 280 );
		},

		/** True if a dialog is currently open. */
		isOpen: function () {
			return FullScreenDialog._current !== null;
		}
	};


	/* ══════════════════════════════════════════════════════════════
	   EXPORT
	   ══════════════════════════════════════════════════════════════ */
	window.ADN = {
		Alert:            Alert,
		Dialog:           Dialog,
		Loader:           Loader,
		ViewAll:          ViewAll,
		Form:             Form,
		Validate:         Validate,
		Dom:              Dom,
		Ajax:             Ajax,
		Tabs:             Tabs,
		Storage:          Storage,
		Clip:             Clip,
		Scroll:           Scroll,
		Param:            Param,
		Utils:            Utils,
		FullScreenDialog: FullScreenDialog
	};

} )( window );
