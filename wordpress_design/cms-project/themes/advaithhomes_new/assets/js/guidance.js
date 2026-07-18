/**
 * guidance.js - Guidance request form: AJAX submission.
 */

( function () {
	'use strict';

	function init() {
		bindGuidanceForm();
	}

	function bindGuidanceForm() {
		var form = document.getElementById( 'guidanceRequestForm' );
		if ( ! form ) { return; }

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			submitGuidanceForm( form );
		} );
	}

	function submitGuidanceForm( form ) {
		if ( typeof adnEnquiry === 'undefined' ) { return; }

		var helpWith   = form.querySelector( '[name="help_with"]' );
		var nameInput  = form.querySelector( '[name="name"]' );
		var emailInput = form.querySelector( '[name="email"]' );
		var reqInput   = form.querySelector( '[name="requirement"]' );
		var submitBtn  = form.querySelector( '.guidance-submit-btn' );

		clearMsg( form );

		if ( ! helpWith || ! helpWith.value.trim() ) {
			return showMsg( form, 'Please select what you need help with.', true );
		}
		if ( ! nameInput || ! nameInput.value.trim() ) {
			return showMsg( form, 'Your name is required.', true );
		}
		if ( ! emailInput || ! isValidEmail( emailInput.value.trim() ) ) {
			return showMsg( form, 'Please enter a valid email address.', true );
		}
		if ( ! reqInput || ! reqInput.value.trim() ) {
			return showMsg( form, 'Please describe your requirement.', true );
		}

		var origHTML = submitBtn ? submitBtn.innerHTML : '';
		if ( submitBtn ) {
			submitBtn.disabled  = true;
			submitBtn.innerHTML = 'Sending&hellip;';
		}

		var fd = new FormData( form );
		fd.append( 'action', 'ah_guidance_submit' );
		fd.append( 'nonce',  adnEnquiry.nonce );

		fetch( adnEnquiry.ajaxUrl, { method: 'POST', body: fd } )
			.then( function ( r ) { return r.text(); } )
			.then( function ( text ) {
				var jsonStr = text.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
				if ( jsonStr.endsWith('0') ) {
					jsonStr = jsonStr.slice(0, -1).trim();
				}
				var res = JSON.parse( jsonStr );

				if ( submitBtn ) {
					submitBtn.disabled  = false;
					submitBtn.innerHTML = origHTML;
				}
				if ( res.success ) {
					form.reset();
					showMsg( form, res.data && res.data.message ? res.data.message : "Thank you! We'll be in touch shortly.", false );
				} else {
					var errMsg = res.data && res.data.message ? res.data.message : 'Something went wrong. Please try again.';
					showMsg( form, errMsg, true );
				}
			} )
			.catch( function () {
				if ( submitBtn ) { submitBtn.disabled = false; submitBtn.innerHTML = origHTML; }
				showMsg( form, 'Could not connect. Please check your connection and try again.', true );
			} );
	}

	function showMsg( form, text, isError ) {
		clearMsg( form );
		var el       = document.createElement( 'div' );
		el.className = 'guidance-form-msg ' + ( isError ? 'is-error' : 'is-success' );
		el.textContent = text;
		form.insertBefore( el, form.firstChild );
		el.scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
	}

	function clearMsg( form ) {
		var el = form.querySelector( '.guidance-form-msg' );
		if ( el ) { el.remove(); }
	}

	function isValidEmail( v ) {
		return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( v );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}

} )();
