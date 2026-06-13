/**
 * contact.js - Contact page interactions.
 * - Enquiry type button grid: single-select toggle
 * - Syncs hidden input value with selected type
 */

( function () {
    'use strict';

    function init() {
        bindEnquiryTypes();
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

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', init );
    } else {
        init();
    }

} )();
