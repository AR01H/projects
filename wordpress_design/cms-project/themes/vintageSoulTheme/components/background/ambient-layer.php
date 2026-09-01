<?php
/**
 * VintageSoulTheme - Ambient Background Layer
 * Renders animated effervescent juice bubbles and atmospheric sugarcane botanical watermarks.
 */

defined( 'ABSPATH' ) || exit;

use VintageSoul\Support\UrlHelper;

$variant        = (string) ( $variant ?? 'dark' );
$bubble_count   = (int) ( $bubble_count ?? 10 );
$cane_positions = (array) ( $cane_positions ?? array( 'top-right', 'bottom-left' ) );

$cane_assets = array(
	UrlHelper::resolve( 'assets/images/decorative/ingredients/sugarcane-stalk-cluster.png' ),
	UrlHelper::resolve( 'assets/images/decorative/ingredients/sugarcane-stalks-bundle.png' ),
	UrlHelper::resolve( 'assets/images/decorative/ingredients/sugarcane-plant-with-roots.png' ),
	UrlHelper::resolve( 'assets/images/decorative/ingredients/sugarcane-stalks-crossed.png' ),
);
?>
<div class="vst-ambient-layer vst-ambient-layer--<?php echo esc_attr( $variant ); ?>" aria-hidden="true">
	
	<!-- 1. Background Botanical Sugarcane Watermarks -->
	<div class="vst-ambient-botanicals">
		<?php foreach ( $cane_positions as $idx => $pos ) :
			$img_src = $cane_assets[ $idx % count( $cane_assets ) ];
			$pos_class = is_string( $pos ) ? 'vst-ambient-cane--' . $pos : 'vst-ambient-cane--top-right';
		?>
			<img class="vst-ambient-cane <?php echo esc_attr( $pos_class ); ?>" 
			     src="<?php echo esc_url( $img_src ); ?>" 
			     alt="" 
			     loading="lazy" 
			     draggable="false">
		<?php endforeach; ?>
	</div>

	<!-- 2. Light Animated Rising Fizz / Juice Bubbles -->
	<div class="vst-ambient-bubbles">
		<?php for ( $i = 0; $i < $bubble_count; $i++ ) :
			$left     = ( ( $i * 19 + 7 ) % 94 ) + 3; // Evenly distributed pseudo-random left %
			$size     = 10 + ( ( $i * 7 ) % 28 );     // 10px to 38px
			$dur      = 8 + ( ( $i * 3 ) % 10 );      // 8s to 18s duration
			$delay    = ( ( $i * 2.3 ) % 7 );         // 0s to 7s stagger
			$sway     = ( $i % 2 === 0 ? 1 : -1 ) * ( 15 + ( $i * 5 % 25 ) ); // -40px to +40px
			$opacity  = 0.25 + ( ( $i % 4 ) * 0.1 );  // 0.25 to 0.55
		?>
			<span class="vst-bubble" style="--b-left: <?php echo $left; ?>%; --b-size: <?php echo $size; ?>px; --b-dur: <?php echo $dur; ?>s; --b-delay: -<?php echo $delay; ?>s; --b-sway: <?php echo $sway; ?>px; --b-op: <?php echo $opacity; ?>;"></span>
		<?php endfor; ?>
	</div>

</div>
