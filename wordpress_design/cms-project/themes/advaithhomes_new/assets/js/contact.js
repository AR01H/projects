/**
 * contact.js - Contact page interactions.
 * - Enquiry type button grid: single-select toggle
 * - Syncs hidden input value with selected type
 */

( function () {
    'use strict';

    function init() {
        bindEnquiryTypes();
        bindContactSubmit();
    }

    function bindEnquiryTypes() {
        var grid   = document.getElementById( 'enquiryTypeGrid' );
        var hidden = document.getElementById( 'selectedEnquiryType' );
        if ( ! grid ) return;

        grid.querySelectorAll( '.enquiry-type-btn' ).forEach( function ( btn ) {
            btn.addEventListener( 'click', function () {
                var wasActive = btn.classList.contains( 'active' );

                /* Deselect all */
                grid.querySelectorAll( '.enquiry-type-btn' ).forEach( function ( b ) {
                    b.classList.remove( 'active' );
                    b.setAttribute( 'aria-pressed', 'false' );
                } );

                /* Toggle selected */
                if ( ! wasActive ) {
                    btn.classList.add( 'active' );
                    btn.setAttribute( 'aria-pressed', 'true' );
                    if ( hidden ) {
                        hidden.value = btn.getAttribute( 'data-type' ) || '';
                    }
                } else if ( hidden ) {
                    hidden.value = '';
                }
            } );
        } );
    }

    function bindContactSubmit() {
        var form = document.getElementById( 'contactEnquiryForm' );
        if ( ! form ) { return; }

        form.addEventListener( 'submit', function ( e ) {
            e.preventDefault();
            submitContactForm( form );
        } );
    }

    function submitContactForm( form ) {
        if ( typeof adnEnquiry === 'undefined' ) { return; }

        var nameInput    = form.querySelector( '[name="name"]' );
        var emailInput   = form.querySelector( '[name="email"]' );
        var msgInput     = form.querySelector( '[name="message"]' );
        var consentInput = form.querySelector( '[name="consent"]' );
        var submitBtn    = form.querySelector( '.contact-submit-btn' );

        clearContactMsg( form );

        if ( ! nameInput || ! nameInput.value.trim() ) {
            return showContactMsg( form, 'Your name is required.', true );
        }
        if ( ! emailInput || ! isValidContactEmail( emailInput.value.trim() ) ) {
            return showContactMsg( form, 'Please enter a valid email address.', true );
        }
        if ( ! msgInput || ! msgInput.value.trim() ) {
            return showContactMsg( form, 'Please tell us how we can help.', true );
        }
        if ( ! consentInput || ! consentInput.checked ) {
            return showContactMsg( form, 'Please agree to the privacy policy to continue.', true );
        }

        var origHTML = submitBtn ? submitBtn.innerHTML : '';
        if ( submitBtn ) {
            submitBtn.disabled  = true;
            submitBtn.innerHTML = 'Sending&hellip;';
        }

        var fd = new FormData( form );
        fd.append( 'action', 'ah_contact_submit' );
        fd.append( 'nonce',  adnEnquiry.nonce );

        fetch( adnEnquiry.ajaxUrl, { method: 'POST', body: fd } )
            .then( function ( r ) { return r.json(); } )
            .then( function ( res ) {
                if ( submitBtn ) { submitBtn.disabled = false; submitBtn.innerHTML = origHTML; }
                if ( res.success ) {
                    form.reset();
                    showContactMsg( form, res.data && res.data.message ? res.data.message : "Thank you! We'll be in touch shortly.", false );
                } else {
                    var errMsg = res.data && res.data.message ? res.data.message : 'Something went wrong. Please try again.';
                    showContactMsg( form, errMsg, true );
                }
            } )
            .catch( function () {
                if ( submitBtn ) { submitBtn.disabled = false; submitBtn.innerHTML = origHTML; }
                showContactMsg( form, 'Could not connect. Please try again.', true );
            } );
    }

    function showContactMsg( form, text, isError ) {
        clearContactMsg( form );
        var el       = document.createElement( 'div' );
        el.className = 'contact-form-msg ' + ( isError ? 'is-error' : 'is-success' );
        el.textContent = text;
        form.insertBefore( el, form.firstChild );
        el.scrollIntoView( { behavior: 'smooth', block: 'nearest' } );
    }

    function clearContactMsg( form ) {
        var el = form.querySelector( '.contact-form-msg' );
        if ( el ) { el.remove(); }
    }

    function isValidContactEmail( v ) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( v );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
