<?php
/**
 * components/sections/calcs_popular.php — Popular calculators 4-col grid.
 *
 * Props: $popular_calcs[] { icon, title, desc, url }
 * Usage: adn_component( 'sections/calcs_popular', array( 'popular_calcs' => $ctx['popular_calcs'] ) );
 */

defined( 'ABSPATH' ) || exit;

$popular_calcs = isset( $popular_calcs ) && is_array( $popular_calcs ) ? $popular_calcs : array();

if ( empty( $popular_calcs ) ) {
	return;
}
?>
<section class="popular-calcs-section">
	<h2><?php echo esc_html__( 'Popular Calculators', ADN_TEXT_DOMAIN ); ?></h2>
	<div class="popular-calcs-grid">
		<?php foreach ( $popular_calcs as $calc ) :
			adn_component( 'cards/calc_popular_card', array( 'calc' => $calc ) );
		endforeach; ?>
	</div>
</section>
