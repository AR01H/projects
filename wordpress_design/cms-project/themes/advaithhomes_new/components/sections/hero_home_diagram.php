<?php
/**
 * components/sections/hero_home_diagram.php - Reusable diagram circle
 *
 * Props: $diagram array (center_icon, center_lines[], nodes[])
 * Used inside hero_home.php (desktop) and as a standalone mobile section.
 * JS initialises once per page via a window flag to avoid duplicate listeners.
 */

defined( 'ABSPATH' ) || exit;

$diagram = isset( $diagram ) && is_array( $diagram ) ? $diagram : array();
$nodes   = isset( $diagram['nodes'] ) ? (array) $diagram['nodes'] : array();
?>
<div class="hero-process-diagram">
	<div class="process-circle"></div>
	<div class="process-center">
		<span class="process-center-icon"><?php echo adn_icon( isset( $diagram['center_icon'] ) ? $diagram['center_icon'] : '' ); ?></span>
		<span class="process-center-text"><?php
			$center_lines = isset( $diagram['center_lines'] ) ? (array) $diagram['center_lines'] : array();
			$first        = true;
			foreach ( $center_lines as $center_line ) {
				if ( ! $first ) { echo '<br>'; }
				echo esc_html( $center_line );
				$first = false;
			}
		?></span>
	</div>
	<div class="process-nodes">
		<?php foreach ( array_values( $nodes ) as $i => $node ) : ?>
			<div class="process-node node-<?php echo esc_attr( (string) ( $i + 1 ) ); ?>">
				<div class="process-node-icon"><?php echo adn_icon( isset( $node['icon'] ) ? $node['icon'] : '' ); ?></div>
				<div class="process-node-label"><?php echo esc_html( isset( $node['label'] ) ? $node['label'] : '' ); ?></div>
			</div>
		<?php endforeach; ?>
	</div>
</div>
<script>
(function () {
	/*
	 * This component may be included twice (once inside the hero grid for desktop,
	 * once in the mobile strip below the hero). The guard ensures setup runs only
	 * once. We defer to DOMContentLoaded so BOTH diagram elements are in the DOM
	 * before we try to position nodes — the first inclusion's script runs while
	 * the second diagram hasn't been parsed yet.
	 */
	if ( window._adnDiagramInit ) { return; }
	window._adnDiagramInit = true;

	var MAX_NODES = 8;

	function positionDiagram( diagram ) {
		/* Skip elements hidden with display:none — offsetParent is null for those. */
		if ( ! diagram || ! diagram.offsetParent ) { return; }

		var nodes = Array.prototype.slice.call(
			diagram.querySelectorAll( '.process-node' )
		).slice( 0, MAX_NODES );

		var n = nodes.length;
		if ( ! n ) { return; }

		diagram.setAttribute( 'data-nodes', n );

		var mobile = window.innerWidth <= 1024;
		var rx, ry;
		if      ( n <= 5 ) { rx = mobile ? 40 : 42; ry = mobile ? 35 : 38; }
		else if ( n === 6 ) { rx = mobile ? 38 : 40; ry = mobile ? 33 : 36; }
		else if ( n === 7 ) { rx = mobile ? 35 : 37; ry = mobile ? 30 : 33; }
		else                { rx = mobile ? 32 : 34; ry = mobile ? 27 : 30; }

		nodes.forEach( function ( node, i ) {
			var deg = -90 + ( 360 / n ) * i;
			var rad = deg * Math.PI / 180;
			node.style.left      = ( 50 + rx * Math.cos( rad ) ) + '%';
			node.style.top       = ( 50 + ry * Math.sin( rad ) ) + '%';
			node.style.transform = 'translate(-50%,-50%)';
		} );
	}

	function positionAll() {
		document.querySelectorAll( '.hero-process-diagram' ).forEach( positionDiagram );
	}

	/* Wait for full DOM so both diagram instances (in-hero + mobile strip) exist. */
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', positionAll );
	} else {
		positionAll();
	}
	window.addEventListener( 'resize', positionAll );
}());
</script>
