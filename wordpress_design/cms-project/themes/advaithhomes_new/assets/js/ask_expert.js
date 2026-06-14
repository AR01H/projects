/**
 * ask_expert.js - Ask an Expert directory + Expert profile page interactions.
 *
 * Features:
 *  1. Category filter tabs  : show/hide expert cards by data-cat
 *  2. Contact modal         : open/close per-expert modal (.expert-contact-btn / .expert-contact-modal)
 *  3. AJAX contact form     : submit to admin-ajax.php adn_expert_contact
 *                             (works for both the modal on the listing page
 *                              and the inline form on the single profile page)
 */

( function () {
    'use strict';

    /* ── Helpers ────────────────────────────────────────────────── */

    function getAjaxUrl() {
        return ( typeof adnExpert !== 'undefined' && adnExpert.ajaxUrl ) ? adnExpert.ajaxUrl : '';
    }

    function getNonce() {
        return ( typeof adnExpert !== 'undefined' && adnExpert.nonce ) ? adnExpert.nonce : '';
    }

    /* ── 1. Category Filter ─────────────────────────────────────── */

    var activeCategory = 'all';

    function initCategoryTabs() {
        document.querySelectorAll( '.expert-cat-tab' ).forEach( function ( tab ) {
            tab.addEventListener( 'click', function () {
                var cat = tab.getAttribute( 'data-cat' ) || 'all';
                setCategory( cat );
            } );
        } );
    }

    function setCategory( cat ) {
        activeCategory = cat;
        syncTabUI( cat );
        filterCards( cat );
    }

    function syncTabUI( cat ) {
        document.querySelectorAll( '.expert-cat-tab' ).forEach( function ( tab ) {
            var isCurrent = tab.getAttribute( 'data-cat' ) === cat;
            tab.classList.toggle( 'active', isCurrent );
            tab.setAttribute( 'aria-selected', isCurrent ? 'true' : 'false' );
        } );
    }

    function filterCards( cat ) {
        var input = document.getElementById( 'expertSearch' );
        var q     = input ? input.value.trim() : '';
        applyFilter( cat, q );
    }

    function applyFilter( cat, query ) {
        showLoader( false );
        var q   = query.toLowerCase();
        var any = false;
        document.querySelectorAll( '.expert-card' ).forEach( function ( card ) {
            // Permanent placeholder card — always stays visible, never filtered.
            if ( card.getAttribute( 'data-permanent' ) ) { return; }
            var cardCat   = ( card.getAttribute( 'data-cat' ) || '' );
            var catMatch  = cat === 'all' || cardCat === cat;
            var textMatch = q === '' || ( card.textContent || '' ).toLowerCase().indexOf( q ) !== -1;
            var show      = catMatch && textMatch;
            if ( show ) {
                card.removeAttribute( 'hidden' );
                any = true;
            } else {
                card.setAttribute( 'hidden', '' );
            }
        } );
        var noRes = document.getElementById( 'expertNoResults' );
        if ( noRes ) {
            if ( any ) { noRes.setAttribute( 'hidden', '' ); }
            else        { noRes.removeAttribute( 'hidden' ); }
        }
    }

    function showLoader( on ) {
        var loader = document.getElementById( 'expertGridLoader' );
        var grid   = document.getElementById( 'expertGrid' );
        if ( ! loader ) { return; }
        if ( on ) {
            loader.removeAttribute( 'hidden' );
            loader.setAttribute( 'aria-hidden', 'false' );
            if ( grid ) { grid.classList.add( 'is-loading' ); }
        } else {
            loader.setAttribute( 'hidden', '' );
            loader.setAttribute( 'aria-hidden', 'true' );
            if ( grid ) { grid.classList.remove( 'is-loading' ); }
        }
    }

    /* ── 2. Text Search ─────────────────────────────────────────── */

    var searchTimer = null;

    function initSearch() {
        var input = document.getElementById( 'expertSearch' );
        var clear = document.getElementById( 'expertSearchClear' );
        var reset = document.getElementById( 'expertSearchReset' );
        if ( ! input ) { return; }

        input.addEventListener( 'input', function () {
            var q = input.value;
            if ( clear ) {
                clear.hidden = q === '';
                clear.classList.toggle( 'is-searching', q !== '' );
            }
            clearTimeout( searchTimer );
            showLoader( true );
            searchTimer = window.setTimeout( function () {
                if ( clear ) { clear.classList.remove( 'is-searching' ); }
                applyFilter( activeCategory, q.trim() );
            }, 250 );
        } );

        if ( clear ) {
            clear.addEventListener( 'click', function () {
                clearTimeout( searchTimer );
                input.value = '';
                clear.hidden = true;
                clear.classList.remove( 'is-searching' );
                input.focus();
                applyFilter( activeCategory, '' );
            } );
        }

        if ( reset ) {
            reset.addEventListener( 'click', function () {
                if ( input ) { input.value = ''; }
                if ( clear ) { clear.hidden = true; }
                applyFilter( activeCategory, '' );
            } );
        }
    }

    /* ── 3. Contact Modal ───────────────────────────────────────── */

    var openModal = null; // currently open modal element

    function initContactModals() {
        // Open on "Contact" button click.
        document.addEventListener( 'click', function ( e ) {
            var btn = e.target.closest( '.expert-contact-btn' );
            if ( ! btn ) { return; }
            e.preventDefault();
            var slug  = btn.getAttribute( 'data-slug' ) || '';
            var modal = document.querySelector( '.expert-contact-modal[data-slug="' + slug + '"]' );
            if ( modal ) { openContactModal( modal ); }
        } );

        // Close on backdrop click.
        document.addEventListener( 'click', function ( e ) {
            if ( e.target.classList.contains( 'ecm-backdrop' ) ) {
                closeOpenModal();
            }
        } );

        // Close on ✕ button.
        document.addEventListener( 'click', function ( e ) {
            if ( e.target.closest( '.ecm-close' ) ) {
                closeOpenModal();
            }
        } );

        // Close on Escape key.
        document.addEventListener( 'keydown', function ( e ) {
            if ( ( e.key === 'Escape' || e.keyCode === 27 ) && openModal ) {
                closeOpenModal();
            }
        } );
    }

    function openContactModal( modal ) {
        if ( openModal ) { closeOpenModal(); }
        modal.removeAttribute( 'hidden' );
        modal.setAttribute( 'aria-hidden', 'false' );
        document.body.style.overflow = 'hidden';
        openModal = modal;
        // Focus first input.
        var firstInput = modal.querySelector( 'input, textarea' );
        if ( firstInput ) { firstInput.focus(); }
    }

    function closeOpenModal() {
        if ( ! openModal ) { return; }
        openModal.setAttribute( 'hidden', '' );
        openModal.setAttribute( 'aria-hidden', 'true' );
        document.body.style.overflow = '';
        openModal = null;
    }

    /* ── 3. AJAX Contact Form Submission ────────────────────────── */

    function initContactForms() {
        // Handles both modal forms (.ecm-form) and the inline single-page form.
        document.addEventListener( 'submit', function ( e ) {
            var form = e.target.closest( '.expert-contact-form' );
            if ( ! form ) { return; }
            e.preventDefault();
            submitContactForm( form );
        } );
    }

    function submitContactForm( form ) {
        var ajaxUrl = getAjaxUrl();
        var nonce   = getNonce();
        if ( ! ajaxUrl || ! nonce ) { return; }

        var submitBtn  = form.querySelector( '.ecf-submit' );
        var feedback   = form.querySelector( '.ecf-feedback' );
        var slug       = form.querySelector( '[name="expert_slug"]' );
        var nameField  = form.querySelector( '[name="sender_name"]' );
        var emailField = form.querySelector( '[name="sender_email"]' );
        var phoneField = form.querySelector( '[name="sender_phone"]' );
        var msgField   = form.querySelector( '[name="message"]' );

        // Clear previous feedback.
        if ( feedback ) {
            feedback.className = 'ecf-feedback';
            feedback.textContent = '';
        }

        // Loading state.
        if ( submitBtn ) {
            submitBtn.disabled    = true;
            submitBtn.textContent = 'Sending…';
        }

        // Build form data.
        var data = 'action=adn_expert_contact'
            + '&nonce='        + encodeURIComponent( nonce )
            + '&expert_slug='  + encodeURIComponent( slug       ? slug.value       : '' )
            + '&sender_name='  + encodeURIComponent( nameField  ? nameField.value  : '' )
            + '&sender_email=' + encodeURIComponent( emailField ? emailField.value : '' )
            + '&sender_phone=' + encodeURIComponent( phoneField ? phoneField.value : '' )
            + '&message='      + encodeURIComponent( msgField   ? msgField.value   : '' );

        var xhr = new XMLHttpRequest();
        xhr.open( 'POST', ajaxUrl, true );
        xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8' );
        xhr.onreadystatechange = function () {
            if ( xhr.readyState !== 4 ) { return; }

            if ( submitBtn ) {
                submitBtn.disabled    = false;
                submitBtn.textContent = 'Send Message';
            }

            var res;
            try { res = JSON.parse( xhr.responseText ); } catch ( err ) { res = null; }

            var ok  = res && res.success;
            var msg = ( res && res.data && res.data.message ) ? res.data.message : ( ok ? 'Message sent.' : 'Something went wrong.' );

            if ( feedback ) {
                feedback.className   = 'ecf-feedback ' + ( ok ? 'ecf-success' : 'ecf-error' );
                feedback.textContent = msg;
            }

            if ( ok ) {
                form.reset();
                // Close any open modal after a short delay.
                if ( openModal ) {
                    window.setTimeout( function () { closeOpenModal(); }, 2000 );
                }
            }
        };
        xhr.send( data );
    }

    /* ── Init ───────────────────────────────────────────────────── */

    function init() {
        initSearch();
        initCategoryTabs();
        initContactModals();
        initContactForms();
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
