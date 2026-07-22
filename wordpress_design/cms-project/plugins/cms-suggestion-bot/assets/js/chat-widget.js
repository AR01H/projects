/**
 * assets/js/chat-widget.js - front-end chat widget behaviour. No jQuery
 * dependency (unlike the admin JS, which runs inside wp-admin where jQuery
 * is always present) - the front end shouldn't force-load it just for this.
 */
( function () {
	'use strict';

	if ( typeof window.csbChat === 'undefined' ) {
		return;
	}

	var cfg = window.csbChat;
	var SESSION_STORAGE_KEY = 'csb_session_id';
	var hasChatted = false;

	function uuid() {
		if ( window.crypto && window.crypto.randomUUID ) {
			return window.crypto.randomUUID();
		}
		return 'csb-' + Date.now() + '-' + Math.random().toString( 16 ).slice( 2 );
	}

	function getSessionId() {
		try {
			var existing = window.localStorage.getItem( SESSION_STORAGE_KEY );
			if ( existing ) {
				return existing;
			}
			var fresh = uuid();
			window.localStorage.setItem( SESSION_STORAGE_KEY, fresh );
			return fresh;
		} catch ( e ) {
			return uuid();
		}
	}

	function isMobile() {
		return window.innerWidth <= 480;
	}

	function init() {
		var widget = document.getElementById( 'csb-chat-widget' );
		if ( ! widget ) {
			return;
		}

		widget.style.setProperty( '--csb-theme', cfg.themeColor );
		widget.style.setProperty( '--csb-bg', cfg.backgroundColor );
		widget.style.setProperty( '--csb-text', cfg.textColor );

		document.getElementById( 'csb-toggle-icon' ).textContent = cfg.botIcon;
		document.getElementById( 'csb-header-icon' ).textContent = cfg.botIcon;
		document.getElementById( 'csb-header-name' ).textContent = cfg.botName;
		document.getElementById( 'csb-identity-badge' ).textContent = cfg.identityLabel;
		document.getElementById( 'csb-thinking' ).textContent = cfg.thinkingMessage;

		// Handle description visibility based on setting
		var descEl = document.getElementById( 'csb-description' );
		if ( cfg.showDescription && cfg.botDescription ) {
			descEl.textContent = cfg.botDescription;
			descEl.classList.remove( 'csb-description--hidden' );
		} else {
			descEl.classList.add( 'csb-description--hidden' );
		}

		// Handle post context banner
		var postContextEl = document.getElementById( 'csb-post-context' );
		var postContextActive = false;
		if ( cfg.postContext && cfg.postContext.id ) {
			postContextEl.classList.add( 'csb-post-context--active' );
			document.getElementById( 'csb-post-context-title' ).textContent = cfg.postContext.title;
			postContextActive = true;
		}

		// Dismiss post context
		document.getElementById( 'csb-post-context-close' ).addEventListener( 'click', function() {
			postContextEl.classList.remove( 'csb-post-context--active' );
			postContextActive = false;
		} );

		var toggle   = document.getElementById( 'csb-chat-toggle' );
		var panel    = document.getElementById( 'csb-chat-panel' );
		var closeBtn = document.getElementById( 'csb-chat-close' );
		var form     = document.getElementById( 'csb-chat-form' );
		var input    = document.getElementById( 'csb-chat-input' );
		var toggleIcon = document.getElementById( 'csb-toggle-icon' );

		var sessionId = getSessionId();
		var welcomed  = false;
		var currentAbortController = null;
		var isWaitingForResponse = false;

		function openPanel() {
			panel.classList.add( 'csb-panel--open' );

			toggle.setAttribute( 'aria-expanded', 'true' );
			toggle.setAttribute( 'aria-label', 'Minimize chat' );
			toggleIcon.textContent = '✕';

			// Prevent body scroll on mobile when panel is open
			if ( isMobile() ) {
				document.body.style.overflow = 'hidden';
			}

			if ( ! welcomed && cfg.welcomeMessage ) {
				appendMessage( 'bot', cfg.welcomeMessage );
				welcomed = true;
			}
			input.focus();
		}

		function closePanel() {
			panel.classList.remove( 'csb-panel--open' );

			toggle.setAttribute( 'aria-expanded', 'false' );
			toggle.setAttribute( 'aria-label', 'Open chat' );
			toggleIcon.textContent = cfg.botIcon;

			// Restore body scroll
			document.body.style.overflow = '';

			if ( hasChatted && cfg.goodbyeMessage ) {
				showGoodbyeToast();
			}
		}

		toggle.addEventListener( 'click', function () {
			if ( panel.classList.contains( 'csb-panel--open' ) ) {
				closePanel();
			} else {
				openPanel();
			}
		} );

		closeBtn.addEventListener( 'click', closePanel );

		// Close on Escape key
		document.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Escape' && panel.classList.contains( 'csb-panel--open' ) ) {
				closePanel();
			}
		} );

		// Close on outside click (desktop only)
		document.addEventListener( 'click', function ( e ) {
			if ( ! isMobile() && panel.classList.contains( 'csb-panel--open' ) ) {
				if ( ! widget.contains( e.target ) ) {
					closePanel();
				}
			}
		} );

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			var question = input.value.trim();
			if ( '' === question ) {
				return;
			}
			input.value = '';
			appendMessage( 'user', question );
			hasChatted = true;
			ask( question );
		} );

		function ask( question ) {
			// Cancel any previous request
			if ( currentAbortController ) {
				currentAbortController.abort();
			}

			// Create new abort controller for this request
			currentAbortController = new AbortController();
			isWaitingForResponse = true;

			setThinking( true );
			setSendButtonState( false ); // Disable send button

			var body = new URLSearchParams();
			body.set( 'action', 'csb_ask' );
			body.set( 'nonce', cfg.nonce );
			body.set( 'question', question );
			body.set( 'session_id', sessionId );

			// Include post context if active
			if ( postContextActive && cfg.postContext && cfg.postContext.id ) {
				body.set( 'post_id', cfg.postContext.id );
			}

			fetch( cfg.ajaxUrl, {
				method: 'POST',
				body: body,
				credentials: 'same-origin',
				signal: currentAbortController.signal
			} )
				.then( function ( res ) { return res.json(); } )
				.then( function ( res ) {
					setThinking( false );
					setSendButtonState( true ); // Re-enable send button
					isWaitingForResponse = false;
					currentAbortController = null;
					if ( res && res.success && res.data ) {
						appendMessage( 'bot', res.data.answer, res.data.suggestion, res.data.suggestions );
					} else {
						appendMessage( 'bot', ( res && res.data && res.data.message ) || 'Something went wrong.' );
					}
				} )
				.catch( function ( err ) {
					setThinking( false );
					setSendButtonState( true ); // Re-enable send button
					isWaitingForResponse = false;
					currentAbortController = null;
					// Don't show error if request was cancelled
					if ( err && err.name !== 'AbortError' ) {
						appendMessage( 'bot', 'Something went wrong. Please try again.' );
					}
				} );
		}

		function setSendButtonState( enabled ) {
			var sendBtn = document.querySelector( '#csb-chat-widget .csb-send' );
			if ( sendBtn ) {
				sendBtn.disabled = ! enabled;
				sendBtn.classList.toggle( 'csb-send--disabled', ! enabled );
			}
		}

		function setThinking( on ) {
			document.getElementById( 'csb-thinking' ).hidden = ! on;
		}

		function scrollToBottom( el ) {
			requestAnimationFrame( function () {
				el.scrollTop = el.scrollHeight;
			} );
		}

		function appendMessage( role, text, suggestion, suggestions ) {
			var list = document.getElementById( 'csb-messages' );
			var bubble = document.createElement( 'div' );
			bubble.className = 'csb-msg csb-msg--' + role;
			list.appendChild( bubble );
			scrollToBottom( list );

			if ( 'user' === role || ! cfg.typingSpeedMs || cfg.typingSpeedMs <= 0 ) {
				bubble.textContent = text;
				appendSuggestions( bubble, suggestion, suggestions );
				scrollToBottom( list );
				return;
			}

			var i = 0;
			var timer = window.setInterval( function () {
				bubble.textContent = text.slice( 0, i + 1 );
				scrollToBottom( list );
				i++;
				if ( i >= text.length ) {
					window.clearInterval( timer );
					appendSuggestions( bubble, suggestion, suggestions );
					scrollToBottom( list );
				}
			}, cfg.typingSpeedMs );
		}

		function appendSuggestions( bubble, primarySuggestion, relatedSuggestions ) {
			var all = [];

			if ( primarySuggestion && primarySuggestion.url ) {
				all.push( primarySuggestion );
			}

			if ( relatedSuggestions && relatedSuggestions.length ) {
				for ( var j = 0; j < relatedSuggestions.length; j++ ) {
					var s = relatedSuggestions[j];
					if ( s && s.url && ! isDuplicate( all, s ) ) {
						all.push( s );
					}
				}
			}

			if ( all.length === 0 ) {
				return;
			}

			if ( all.length === 1 ) {
				var link = document.createElement( 'a' );
				link.className = 'csb-suggestion';
				link.href = all[0].url;
				link.textContent = '→ ' + all[0].title;
				bubble.appendChild( link );
				return;
			}

			var wrapper = document.createElement( 'div' );
			wrapper.className = 'csb-suggestions';

			var header = document.createElement( 'span' );
			header.className = 'csb-suggestions-header';
			header.textContent = 'Related articles:';
			wrapper.appendChild( header );

			var list = document.createElement( 'ul' );
			list.className = 'csb-suggestions-list';

			for ( var k = 0; k < all.length; k++ ) {
				var item = document.createElement( 'li' );
				item.className = 'csb-suggestions-item';

				var a = document.createElement( 'a' );
				a.className = 'csb-suggestion';
				a.href = all[k].url;
				a.textContent = all[k].title;
				item.appendChild( a );

				list.appendChild( item );
			}

			wrapper.appendChild( list );
			bubble.appendChild( wrapper );
		}

		function isDuplicate( arr, item ) {
			for ( var i = 0; i < arr.length; i++ ) {
				if ( arr[i].url === item.url ) {
					return true;
				}
			}
			return false;
		}

		function showGoodbyeToast() {
			var toast = document.createElement( 'div' );
			toast.className = 'csb-toast';
			toast.textContent = cfg.goodbyeMessage;
			document.body.appendChild( toast );
			requestAnimationFrame( function () { toast.classList.add( 'csb-toast--visible' ); } );
			window.setTimeout( function () {
				toast.classList.remove( 'csb-toast--visible' );
				window.setTimeout( function () { toast.remove(); }, 300 );
			}, 3000 );
		}
	}

	// Initialize when DOM is ready
	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
