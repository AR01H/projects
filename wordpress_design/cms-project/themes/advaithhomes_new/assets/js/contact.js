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
        bindSidebarFaqs();
    }

    function bindSidebarFaqs() {
        var items = Array.prototype.slice.call( document.querySelectorAll( '.contact-faq-item' ) );
        items.forEach( function ( d ) {
            d.addEventListener( 'toggle', function () {
                if ( ! d.open ) { return; }
                items.forEach( function ( other ) {
                    if ( other !== d && other.open ) { other.open = false; }
                } );
            } );
        } );
    }

    function bindEnquiryTypes() {
        var grid   = document.getElementById( 'enquiryTypeGrid' );
        var hidden = document.getElementById( 'selectedEnquiryType' );
        if ( ! grid ) return;

        grid.querySelectorAll( '.enquiry-type-btn' ).forEach( function ( btn ) {
            btn.addEventListener( 'click', function () {
                /* Deselect all */
                grid.querySelectorAll( '.enquiry-type-btn' ).forEach( function ( b ) {
                    b.classList.remove( 'active' );
                    b.setAttribute( 'aria-pressed', 'false' );
                } );

                /* Always select clicked button - mandatory selection */
                btn.classList.add( 'active' );
                btn.setAttribute( 'aria-pressed', 'true' );
                if ( hidden ) {
                    hidden.value = btn.getAttribute( 'data-type' ) || '';
                }
                /* Clear any validation error on the grid */
                var errEl = grid.parentElement.querySelector( '.enquiry-type-error' );
                if ( errEl ) { errEl.remove(); }
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

        /* Mandatory: enquiry type must be selected */
        var hiddenType = form.querySelector( '[name="enquiry_type"]' );
        var typeGrid   = document.getElementById( 'enquiryTypeGrid' );
        if ( typeGrid && ( ! hiddenType || ! hiddenType.value.trim() ) ) {
            var existingErr = typeGrid.parentElement.querySelector( '.enquiry-type-error' );
            if ( ! existingErr ) {
                var typeErr = document.createElement( 'p' );
                typeErr.className = 'enquiry-type-error';
                typeErr.textContent = 'Please select what best describes you.';
                typeGrid.parentElement.appendChild( typeErr );
            }
            typeGrid.scrollIntoView( { behavior: 'smooth', block: 'center' } );
            return;
        }

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
        fd.append( 'client_timestamp', new Date().toISOString() );

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
        form.appendChild( el );
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
