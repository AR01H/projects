<?php
/**
 * components/sections/tools_trust_bar.php - Trust bar strip below hero.
 *
 * Props: $trust_items[] { icon, title, subtitle }
 * Usage: adn_component( 'sections/tools_trust_bar', array( 'trust_items' => $ctx['trust_items'] ) );
 */

defined( 'ABSPATH' ) || exit;

$trust_items = isset( $trust_items ) && is_array( $trust_items ) ? $trust_items : array();

if ( empty( $trust_items ) ) {
	return;
}
?>
<div class="tools-trust-bar">
	<div class="tools-trust-inner">
		<?php foreach ( $trust_items as $item ) : ?>
			<div class="tools-trust-item">
				<div class="trust-icon" aria-hidden="true">
					<?php echo adn_icon( isset( $item['icon'] ) ? $item['icon'] : '' ); ?>
				</div>
				<div>
					<strong><?php echo esc_html( isset( $item['title'] )    ? $item['title']    : '' ); ?></strong>
					<span><?php echo esc_html( isset( $item['subtitle'] ) ? $item['subtitle'] : '' ); ?></span>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>

