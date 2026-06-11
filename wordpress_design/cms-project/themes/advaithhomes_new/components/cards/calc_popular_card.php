<?php
/**
 * components/cards/calc_popular_card.php — Popular calculator card (centred, with CTA link).
 *
 * Props: $calc { icon, title, desc, url }
 * Usage: adn_component( 'cards/calc_popular_card', array( 'calc' => $calc ) );
 */

defined( 'ABSPATH' ) || exit;

$calc = isset( $calc ) && is_array( $calc ) ? $calc : array();
$url  = esc_url( adn_link( isset( $calc['url'] ) ? $calc['url'] : '' ) );
?>
<a href="<?php echo $url; ?>" class="popular-calc-card">
	<div class="popular-calc-icon" aria-hidden="true">
		<?php echo esc_html( isset( $calc['icon'] ) ? $calc['icon'] : '' ); ?>
	</div>

	<?php if ( ! empty( $calc['title'] ) ) : ?>
		<h4><?php echo esc_html( $calc['title'] ); ?></h4>
	<?php endif; ?>

	<?php if ( ! empty( $calc['desc'] ) ) : ?>
		<p><?php echo esc_html( $calc['desc'] ); ?></p>
	<?php endif; ?>

	<span class="calc-cta">
		<?php echo esc_html__( 'Calculate Now', ADN_TEXT_DOMAIN ); ?> →
	</span>
</a>
