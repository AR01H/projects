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

    function cardMatches( card, cat, q ) {
        var cardCat  = ( card.getAttribute( 'data-cat' ) || '' );
        var cardCats = cardCat.split( ',' );
        var hasCat   = false;
        for ( var i = 0; i < cardCats.length; i++ ) {
            if ( cardCats[ i ].trim() === cat ) { hasCat = true; }
        }
        var catMatch  = cat === 'all' || hasCat;
        var textMatch = q === '' || ( card.textContent || '' ).toLowerCase().indexOf( q ) !== -1;
        return catMatch && textMatch;
    }

    // One rule drives every state on the page: real matching experts show as
    // cards; locked experts never show individually — if any of them match,
    // the single "profiles are locked" card stands in for them instead; "no
    // results" only appears when neither real nor locked matches exist. The
    // permanent "more experts" contact card is never toggled — always visible.
    function applyFilter( cat, query ) {
        showLoader( false );
        var q             = query.toLowerCase();
        var gridEl        = document.getElementById( 'expertGrid' );
        var lockedCard    = gridEl ? gridEl.querySelector( '.expert-card--locked-placeholder' ) : null;
        var realMatches   = 0;
        var lockedMatches = 0;

        document.querySelectorAll( '.expert-card' ).forEach( function ( card ) {
            if ( card.getAttribute( 'data-permanent' ) ) { return; }
            if ( card.classList.contains( 'expert-card--locked-placeholder' ) ) { return; }

            if ( card.hasAttribute( 'data-unlockable' ) ) {
                // Locked card: content already exists in the DOM, but stays hidden
                // individually — it only counts toward showing the locked-profiles card.
                if ( cardMatches( card, cat, q ) ) { lockedMatches++; }
                return;
            }

            if ( cardMatches( card, cat, q ) ) {
                card.removeAttribute( 'hidden' );
                realMatches++;
            } else {
                card.setAttribute( 'hidden', '' );
            }
        } );

        var noRes     = document.getElementById( 'expertNoResults' );
        var unlockBar = document.getElementById( 'expertUnlockBar' );
        var hasAnyLocked = !! document.querySelector( '.expert-card[data-unlockable]' );

        if ( gridEl )     { gridEl.style.display = ( realMatches > 0 || lockedMatches > 0 ) ? '' : 'none'; }
        if ( noRes )      { noRes.toggleAttribute( 'hidden', realMatches > 0 || lockedMatches > 0 ); }
        if ( unlockBar )  { unlockBar.toggleAttribute( 'hidden', ! hasAnyLocked ); }
        if ( lockedCard ) {
            lockedCard.toggleAttribute( 'hidden', lockedMatches === 0 );
            lockedCard.style.display = lockedMatches > 0 ? '' : 'none';
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
            var ajaxUrl = ( typeof adnExpert !== 'undefined' && adnExpert.ajaxUrl )
                ? adnExpert.ajaxUrl
                : ( typeof adnSite !== 'undefined' && adnSite.ajaxUrl )
                    ? adnSite.ajaxUrl
                    : '/wp-admin/admin-ajax.php';
            var nonce   = ( typeof adnExpert !== 'undefined' && adnExpert.unlockNonce ) ? adnExpert.unlockNonce : '';
            if ( ! pw ) { return; }

            btn.disabled   = true;
            btn.innerHTML  = '<span class="eub-spinner" aria-hidden="true"></span> Unlocking…';
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
                    var token = res.data.token;

                    /* var expression avoids strict-mode block-scoped function issues */
                    var storeAndReveal = function () {
                        // Store unlock token for 1 day — no page reload needed.
                        var expires = new Date( Date.now() + 1 * 24 * 60 * 60 * 1000 ).toUTCString();
                        document.cookie = 'adn_experts_unlocked=' + encodeURIComponent( token )
                            + '; path=/; expires=' + expires + '; SameSite=Lax';

                        // Reveal pre-rendered locked cards and remove gate so applyFilter can reach them.
                        document.querySelectorAll( '.expert-card[data-unlockable]' ).forEach( function ( card ) {
                            card.removeAttribute( 'data-unlockable' );
                            card.removeAttribute( 'hidden' );
                        } );
                        // Hide the locked placeholder and the unlock bar.
                        var placeholder = document.querySelector( '.expert-card--locked-placeholder' );
                        if ( placeholder ) { placeholder.setAttribute( 'hidden', '' ); }
                        var bar = document.getElementById( 'expertUnlockBar' );
                        if ( bar ) { bar.setAttribute( 'hidden', '' ); }
                        // Re-apply current filter so newly revealed cards honour category/search.
                        var searchVal = document.getElementById( 'expertSearch' ) ? document.getElementById( 'expertSearch' ).value : '';
                        applyFilter( activeCategory, searchVal );
                    };

                    var consent = window.adnCookieConsent ? window.adnCookieConsent.getStatus() : 'accepted';

                    if ( consent === 'accepted' ) {
                        storeAndReveal();
                    } else {
                        if ( window.adnCookieConsent ) {
                            window.adnCookieConsent.show(
                                storeAndReveal,
                                function () {
                                    if ( errEl ) {
                                        errEl.textContent = 'Cookie consent is required to save your unlock. Please accept cookies.';
                                        errEl.hidden = false;
                                    }
                                }
                            );
                        } else {
                            storeAndReveal();
                        }
                    }
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

    function initClientWorkLightbox() {
        var wraps = document.querySelectorAll( '.client-img-wrap' );
        if ( ! wraps.length ) { return; }
        
        wraps.forEach( function ( wrap ) {
            wrap.style.cursor = 'pointer';
            wrap.addEventListener( 'click', function ( e ) {
                var img = wrap.querySelector( 'img' );
                var caption = wrap.querySelector( '.client-img-caption' );
                if ( ! img ) { return; }
                
                var src = img.getAttribute( 'src' );
                var alt = img.getAttribute( 'alt' ) || '';
                var captionText = caption ? caption.textContent : '';
                
                // Create a temporary full-screen lightbox modal
                var lightbox = document.createElement( 'div' );
                lightbox.className = 'adn-modal adn-modal--large expert-lightbox-modal';
                lightbox.setAttribute( 'role', 'dialog' );
                lightbox.setAttribute( 'aria-modal', 'true' );
                
                lightbox.innerHTML = 
                    '<div class="adn-modal__overlay"></div>' +
                    '<div class="adn-modal__content" style="max-width:90vw; padding:10px; background:rgba(0,0,0,0.9); border:none; box-shadow:none;">' +
                        '<div class="adn-modal__header" style="border:none; padding:0; justify-content:flex-end;">' +
                            '<button class="adn-modal__close" aria-label="Close dialog" style="color:#fff; font-size:2.2rem; background:none; border:none; cursor:pointer;">&times;</button>' +
                        '</div>' +
                        '<div class="adn-modal__body" style="padding:0; display:flex; flex-direction:column; align-items:center; justify-content:center;">' +
                            '<img src="' + src + '" alt="' + alt + '" style="max-height:80vh; max-width:100%; object-fit:contain; border-radius:4px; display:block;">' +
                            ( captionText ? '<p style="color:#fff; margin-top:12px; font-size:0.95rem; text-align:center; font-weight:500;">' + captionText + '</p>' : '' ) +
                        '</div>' +
                    '</div>';
                
                document.body.appendChild( lightbox );
                
                // Animate visibility
                window.setTimeout( function () {
                    lightbox.classList.add( 'adn-modal--visible' );
                }, 10 );
                
                // Close handlers
                var closeBtn = lightbox.querySelector( '.adn-modal__close' );
                var overlay = lightbox.querySelector( '.adn-modal__overlay' );
                
                function closeLightbox() {
                    lightbox.classList.remove( 'adn-modal--visible' );
                    window.setTimeout( function () {
                        lightbox.remove();
                    }, 300 );
                }
                
                if ( closeBtn ) { closeBtn.addEventListener( 'click', closeLightbox ); }
                if ( overlay ) { overlay.addEventListener( 'click', closeLightbox ); }
                
                var escHandler = function ( e ) {
                    if ( e.key === 'Escape' ) {
                        closeLightbox();
                        document.removeEventListener( 'keydown', escHandler );
                    }
                };
                document.addEventListener( 'keydown', escHandler );
            } );
        });
    }

    /* ── Init ───────────────────────────────────────────────────── */

    function init() {
        initSearch();
        initCategoryTabs();
        initContactModals();
        initContactForms();
        initCardClick();
        initUnlock();
        initClientWorkLightbox();
        /* Run filter on load so the "More experts" card is hidden when there are no
           real experts, and the "No experts found" message shows correctly. */
        applyFilter( activeCategory, '' );
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
