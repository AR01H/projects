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
        bindLiveValidation();
    }

    /* Live validation: red border on a required field once it's been left
       empty/invalid, cleared as soon as it becomes valid. The submit button
       stays disabled until every required field is valid - not just consent. */
    function bindLiveValidation() {
        var form = document.getElementById( 'contactEnquiryForm' );
        if ( ! form ) { return; }

        var watched = [
            form.querySelector( '[name="name"]' ),
            form.querySelector( '[name="email"]' ),
            form.querySelector( '[name="message"]' )
        ];

        watched.forEach( function ( el ) {
            if ( ! el ) { return; }
            el.addEventListener( 'blur', function () { markFieldValidity( el, isFieldValid( el ) ); } );
            el.addEventListener( 'input', function () {
                if ( isFieldValid( el ) ) { markFieldValidity( el, true ); }
                updateSubmitButtonState( form );
            } );
        } );

        var consentInput = form.querySelector( '[name="consent"]' );
        if ( consentInput ) {
            consentInput.addEventListener( 'change', function () { updateSubmitButtonState( form ); } );
        }

        updateSubmitButtonState( form );
    }

    function isFieldValid( el ) {
        if ( ! el ) { return true; }
        if ( 'email' === el.type ) { return el.value.trim() !== '' && isValidContactEmail( el.value.trim() ); }
        if ( 'checkbox' === el.type ) { return el.checked; }
        return el.value.trim() !== '';
    }

    function markFieldValidity( el, isValid ) {
        if ( ! el ) { return; }
        el.classList.toggle( 'field-invalid', ! isValid );
    }

    /* Re-checks every required field (name, email, message, consent, and the
       enquiry-type grid when present) and only enables Submit when all of
       them are valid - previously this only checked the consent toggle. */
    function updateSubmitButtonState( form ) {
        var btn = form.querySelector( '.contact-submit-btn' );
        if ( ! btn ) { return; }

        var ok = isFieldValid( form.querySelector( '[name="name"]' ) )
            && isFieldValid( form.querySelector( '[name="email"]' ) )
            && isFieldValid( form.querySelector( '[name="message"]' ) )
            && isFieldValid( form.querySelector( '[name="consent"]' ) );

        var typeGrid = document.getElementById( 'enquiryTypeGrid' );
        if ( typeGrid ) {
            var typeInput = form.querySelector( '[name="enquiry_type"]' );
            ok = ok && !! ( typeInput && typeInput.value.trim() !== '' );
        }

        btn.disabled = ! ok;
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
                grid.classList.remove( 'field-invalid' );

                var form = grid.closest( 'form' );
                if ( form ) { updateSubmitButtonState( form ); }
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
            typeGrid.classList.add( 'field-invalid' );
            typeGrid.scrollIntoView( { behavior: 'smooth', block: 'center' } );
            return;
        }

        if ( ! nameInput || ! nameInput.value.trim() ) {
            markFieldValidity( nameInput, false );
            return showContactMsg( form, 'Your name is required.', true );
        }
        if ( ! emailInput || ! isValidContactEmail( emailInput.value.trim() ) ) {
            markFieldValidity( emailInput, false );
            return showContactMsg( form, 'Please enter a valid email address.', true );
        }
        if ( ! msgInput || ! msgInput.value.trim() ) {
            markFieldValidity( msgInput, false );
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
            .then( function ( r ) { return r.text(); } )
            .then( function ( text ) {
                var jsonStr = text.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
                if ( jsonStr.endsWith('0') ) {
                    jsonStr = jsonStr.slice(0, -1).trim();
                }
                var res = JSON.parse( jsonStr );

                if ( submitBtn ) { submitBtn.disabled = false; submitBtn.innerHTML = origHTML; }
                if ( res.success ) {
                    form.reset();
                    showContactMsg( form, res.data && res.data.message ? res.data.message : "Thank you! We'll be in touch shortly.", false );
                    resetContactFormState( form ); // stays open so a follow-up can be typed straight away
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

    /* After a successful send the form stays fully editable so a visitor can send a
       follow-up straight away. Double submits are already prevented by the submit
       button disabling itself for the duration of the request, and by Submit going
       back to disabled here until the empty fields are filled in again. */
    function resetContactFormState( form ) {
        /* form.reset() (already run on success) clears the hidden enquiry_type
           value but not the button grid's own active/aria-pressed state. */
        var grid = document.getElementById( 'enquiryTypeGrid' );
        if ( grid ) {
            grid.querySelectorAll( '.enquiry-type-btn' ).forEach( function ( b ) {
                b.classList.remove( 'active' );
                b.setAttribute( 'aria-pressed', 'false' );
            } );
        }

        /* Drop any leftover invalid styling from the submission that just succeeded. */
        form.querySelectorAll( '.field-invalid' ).forEach( function ( el ) {
            el.classList.remove( 'field-invalid' );
        } );

        updateSubmitButtonState( form ); // fields are empty again - Submit disabled until refilled
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
