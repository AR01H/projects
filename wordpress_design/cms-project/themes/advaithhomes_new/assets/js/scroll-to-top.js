( function () {
    'use strict';

    var btn = document.getElementById( 'scrollToTop' );
    if ( ! btn ) { return; }

    var shown = false;

    window.addEventListener( 'scroll', function () {
        var past = window.scrollY > 320;
        if ( past && ! shown ) {
            btn.removeAttribute( 'hidden' );
            /* rAF ensures display:none is cleared before the class triggers transition */
            requestAnimationFrame( function () { btn.classList.add( 'is-visible' ); } );
            shown = true;
        } else if ( ! past && shown ) {
            btn.classList.remove( 'is-visible' );
            shown = false;
        }
    }, { passive: true } );

    btn.addEventListener( 'click', function () {
        window.scrollTo( { top: 0, behavior: 'smooth' } );
    } );
} )();
