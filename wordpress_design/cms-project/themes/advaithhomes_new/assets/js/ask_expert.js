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
        var q             = query.toLowerCase();
        var any           = false;
        var gridEl        = document.getElementById( 'expertGrid' );
        var lockedCard    = gridEl ? gridEl.querySelector( '.expert-card--locked-placeholder' ) : null;

        document.querySelectorAll( '.expert-card' ).forEach( function ( card ) {
            // Permanent card and locked placeholder are always visible — never filtered.
            if ( card.getAttribute( 'data-permanent' ) ) { return; }
            if ( card.classList.contains( 'expert-card--locked-placeholder' ) ) { return; }
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

        var noRes    = document.getElementById( 'expertNoResults' );
        var permCard = gridEl ? gridEl.querySelector( '[data-permanent]' ) : null;

        if ( any ) {
            if ( noRes )     { noRes.setAttribute( 'hidden', '' ); }
            if ( gridEl )    { gridEl.style.display = ''; }
            if ( permCard )  { permCard.style.display = ''; }
        } else {
            if ( noRes )     { noRes.removeAttribute( 'hidden' ); }
            // Keep the grid visible if the locked placeholder is present.
            if ( gridEl )    { gridEl.style.display = lockedCard ? '' : 'none'; }
            if ( permCard )  { permCard.style.display = 'none'; }
        }
        // Locked placeholder is always visible regardless of filter state.
        if ( lockedCard ) { lockedCard.style.display = ''; lockedCard.removeAttribute( 'hidden' ); }
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

    /* ── Card click → profile URL ─────────────────────────────── */

    function initCardClick() {
        document.addEventListener( 'click', function ( e ) {
            var card = e.target.closest( '.expert-card[data-profile-url]' );
            if ( ! card ) return;
            // Don't intercept clicks on buttons, links, or the contact modal trigger.
            if ( e.target.closest( 'a, button, input, textarea' ) ) return;
            window.location.href = card.dataset.profileUrl;
        } );
        document.addEventListener( 'keydown', function ( e ) {
            if ( e.key !== 'Enter' && e.key !== ' ' ) return;
            var card = document.activeElement;
            if ( card && card.classList.contains( 'expert-card' ) && card.dataset.profileUrl ) {
                e.preventDefault();
                window.location.href = card.dataset.profileUrl;
            }
        } );
    }

    /* ── 4. Expert Profile Unlock ───────────────────────────────── */

    function initUnlock() {
        var bar   = document.getElementById( 'expertUnlockBar' );          // listing page
        var input = document.getElementById( 'expertUnlockPw' );           // both pages
        var btn   = document.getElementById( 'expertUnlockBtn' );          // both pages
        var errEl = document.getElementById( 'expertUnlockError' );        // both pages
        if ( ! input || ! btn ) { return; }

        btn.addEventListener( 'click', function () { doUnlock(); } );
        input.addEventListener( 'keydown', function ( e ) {
            if ( e.key === 'Enter' ) { doUnlock(); }
        } );

        function doUnlock() {
            var pw      = input.value;
            var ajaxUrl = ( typeof adnExpert !== 'undefined' && adnExpert.ajaxUrl ) ? adnExpert.ajaxUrl : '';
            var nonce   = ( typeof adnExpert !== 'undefined' && adnExpert.unlockNonce ) ? adnExpert.unlockNonce : '';
            if ( ! pw || ! ajaxUrl ) { return; }

            btn.disabled    = true;
            btn.textContent = 'Checking…';
            if ( errEl ) { errEl.hidden = true; errEl.textContent = ''; }

            var data = 'action=adn_expert_unlock'
                + '&nonce='           + encodeURIComponent( nonce )
                + '&unlock_password=' + encodeURIComponent( pw );

            var xhr = new XMLHttpRequest();
            xhr.open( 'POST', ajaxUrl, true );
            xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8' );
            xhr.onreadystatechange = function () {
                if ( xhr.readyState !== 4 ) { return; }
                btn.disabled    = false;
                btn.innerHTML   = '<i class="fa-solid fa-unlock" aria-hidden="true"></i> Unlock';

                var res;
                try { res = JSON.parse( xhr.responseText ); } catch ( err ) { res = null; }

                if ( res && res.success && res.data && res.data.token ) {
                    // Set cookie for 7 days.
                    var expires = new Date( Date.now() + 7 * 24 * 60 * 60 * 1000 ).toUTCString();
                    document.cookie = 'adn_experts_unlocked=' + encodeURIComponent( res.data.token )
                        + '; path=/; expires=' + expires + '; SameSite=Lax';

                    // On the profile page (locked screen), reload to show full profile.
                    if ( document.getElementById( 'expertProfileLockedScreen' ) ) {
                        window.location.reload();
                        return;
                    }

                    // On the listing page, reveal all locked cards in-place.
                    document.querySelectorAll( '.expert-card--locked' ).forEach( function ( card ) {
                        card.classList.remove( 'expert-card--locked' );
                        var url = card.getAttribute( 'data-profile-url' );
                        if ( url ) {
                            card.setAttribute( 'role', 'link' );
                            card.setAttribute( 'tabindex', '0' );
                        }
                    } );

                    // Hide the unlock bar.
                    if ( bar ) { bar.classList.add( 'is-unlocked' ); }
                } else {
                    var msg = ( res && res.data && res.data.message ) ? res.data.message : 'Incorrect password. Please try again.';
                    if ( errEl ) {
                        errEl.textContent = msg;
                        errEl.hidden      = false;
                    }
                    input.value = '';
                    input.focus();
                }
            };
            xhr.send( data );
        }
    }

    /* ── Init ───────────────────────────────────────────────────── */

    function init() {
        initSearch();
        initCategoryTabs();
        initContactModals();
        initContactForms();
        initCardClick();
        initUnlock();
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
