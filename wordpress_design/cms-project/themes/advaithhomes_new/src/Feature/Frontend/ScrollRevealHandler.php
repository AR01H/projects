<?php
namespace Adn\Theme\Feature\Frontend;

defined( 'ABSPATH' ) || exit;

class ScrollRevealHandler {

	public static function gate(): void {
		if ( ! empty( $_GET['content'] ) && 'true' === (string) $_GET['content'] ) {
			return;
		}
		echo "<script>(function(){try{if(!window.matchMedia||!matchMedia('(prefers-reduced-motion: reduce)').matches){document.documentElement.className+=' adn-reveal';}}catch(e){}})();</script>\n";
	}

	public static function runtime(): void {
		if ( ! empty( $_GET['content'] ) && 'true' === (string) $_GET['content'] ) {
			return;
		}
		?>
<script>
(function(){
	var root = document.documentElement;
	if ( ! root.classList.contains('adn-reveal') ) { return; }
	var SEL = '.guide-card,.jny-card,.calc-card,.contact-resource-card,.glc,.spotlight-card,.expert-card,.featured-article,.section-header-wrap';
	var io  = null;
	if ( 'IntersectionObserver' in window ) {
		io = new IntersectionObserver( function( entries ){
			entries.forEach( function( en ){
				if ( en.isIntersecting ) {
					en.target.classList.add('adn-in');
					io.unobserve( en.target );
				}
			} );
		}, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 } );
	}
	function scan(){
		var els = [].slice.call( document.querySelectorAll( SEL ) ).filter( function( e ){
			return ! e.dataset.adnRev;
		} );
		els.forEach( function( e ){
			e.dataset.adnRev = '1';
			if ( ! io ) { e.classList.add('adn-in'); return; }
			var sibs = e.parentNode ? [].indexOf.call( e.parentNode.children, e ) : 0;
			e.style.transitionDelay = ( Math.min( sibs % 8, 6 ) * 55 ) + 'ms';
			io.observe( e );
		} );
	}
	window.adnRevealScan = scan;
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', scan );
	} else {
		scan();
	}
}());
</script>
		<?php
	}
}
