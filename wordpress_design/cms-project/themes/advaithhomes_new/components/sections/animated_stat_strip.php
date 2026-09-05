<?php
/**
 * components/sections/animated_stat_strip.php
 *
 * Animated stat strip. Uses the theme's existing global count-up + reveal
 * micro-interactions (assets/js/premium.js watches every .acard-num on the
 * page and animates it into view; no extra JS is loaded for this section).
 *
 * Props: $stats { items[] { number, label, sub } }
 * Usage: adn_component( 'sections/animated_stat_strip', array( 'stats' => $ctx['stats'] ) );
 */
defined( 'ABSPATH' ) || exit;

$_items = isset( $stats['items'] ) && is_array( $stats['items'] ) ? $stats['items'] : array();
if ( empty( $_items ) ) return;
?>
<section class="hiw-stats-section">
	<div class="container">
		<div class="hiw-stats-grid">
			<?php foreach ( $_items as $_s ) :
				$_num = esc_html( isset( $_s['number'] ) ? (string) $_s['number'] : '' );
				$_lbl = esc_html( isset( $_s['label'] )  ? (string) $_s['label']  : '' );
				$_sub = esc_html( isset( $_s['sub'] )    ? (string) $_s['sub']    : '' );
			?>
				<div class="hiw-stat acard-stat">
					<span class="hiw-stat-num acard-num"><?php echo $_num; ?></span>
					<span class="hiw-stat-label"><?php echo $_lbl; ?></span>
					<?php if ( '' !== $_sub ) : ?><span class="hiw-stat-sub"><?php echo $_sub; ?></span><?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
