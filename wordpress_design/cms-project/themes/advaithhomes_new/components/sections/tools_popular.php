<?php
/**
 * components/sections/tools_popular.php - Popular calculators 4-col grid.
 *
 * Props: $popular_tools[] { icon, title, desc, url }
 * Usage: adn_component( 'sections/tools_popular', array( 'popular_tools' => $ctx['popular_tools'] ) );
 */

defined( 'ABSPATH' ) || exit;

$popular_tools = isset( $popular_tools ) && is_array( $popular_tools ) ? $popular_tools : array();

if ( empty( $popular_tools ) ) {
	return;
}
?>
<section class="popular-tools-section">
	<h2><?php echo esc_html__( 'Popular Calculators', ADN_TEXT_DOMAIN ); ?></h2>
	<div class="popular-tools-grid">
		<?php foreach ( $popular_tools as $calc ) :
			adn_component( 'cards/tool_popular_card', array( 'calc' => $calc ) );
		endforeach; ?>
	</div>
</section>


