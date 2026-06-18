<?php
/**
 * components/cards/calc_popular_card.php - Popular calculator card (centred, with CTA link).
 *
 * Props: $calc { icon, title, desc, url, thumbnail?, highlight? }
 * Usage: adn_component( 'cards/calc_popular_card', array( 'calc' => $calc ) );
 */

defined( 'ABSPATH' ) || exit;

$calc      = isset( $calc ) && is_array( $calc ) ? $calc : array();
$url       = esc_url( adn_link( isset( $calc['url'] ) ? $calc['url'] : '' ) );
$thumbnail = isset( $calc['thumbnail'] ) && '' !== $calc['thumbnail'] ? (string) $calc['thumbnail'] : '';
$highlight = isset( $calc['highlight'] ) && '' !== $calc['highlight'] ? (string) $calc['highlight'] : '';
?>
<a href="<?php echo $url; ?>" class="popular-calc-card">
	<?php if ( $highlight ) : ?>
		<span class="calc-badge"><?php echo esc_html( $highlight ); ?></span>
	<?php endif; ?>

	<div class="popular-calc-icon" aria-hidden="true">
		<?php if ( $thumbnail ) : ?>
			<img src="<?php echo esc_url( $thumbnail ); ?>" alt="" class="calc-popular-thumb" loading="lazy">
		<?php else : ?>
			<?php echo adn_icon( isset( $calc['icon'] ) ? $calc['icon'] : '' ); ?>
		<?php endif; ?>
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
