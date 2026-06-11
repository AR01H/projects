<?php
/**
 * components/parts/cta_banner.php — Full-width personalised guidance CTA banner.
 *
 * Props: $cta_banner { icon, title, description, cta { label, url }, trust_items[] }
 * Usage: adn_component( 'parts/cta_banner', array( 'cta_banner' => $ctx['cta_banner'] ) );
 */

defined( 'ABSPATH' ) || exit;

$cta_banner  = isset( $cta_banner ) && is_array( $cta_banner ) ? $cta_banner : array();
$cta         = isset( $cta_banner['cta'] )         ? (array) $cta_banner['cta']         : array();
$trust_items = isset( $cta_banner['trust_items'] ) ? (array) $cta_banner['trust_items'] : array();
?>
<div class="cta-banner">
	<div class="cta-banner-img">
		<?php echo esc_html( isset( $cta_banner['icon'] ) ? $cta_banner['icon'] : '' ); ?>
	</div>

	<div class="cta-banner-content">
		<h3><?php echo esc_html( isset( $cta_banner['title'] ) ? $cta_banner['title'] : '' ); ?></h3>
		<p><?php echo esc_html( isset( $cta_banner['description'] ) ? $cta_banner['description'] : '' ); ?></p>
	</div>

	<div class="cta-banner-actions">
		<?php if ( ! empty( $cta['label'] ) ) : ?>
			<a href="<?php echo esc_url( adn_link( isset( $cta['url'] ) ? $cta['url'] : '' ) ); ?>" class="btn btn-accent btn-lg">
				<?php echo esc_html( $cta['label'] ); ?>
			</a>
		<?php endif; ?>

		<?php if ( $trust_items ) : ?>
			<div class="cta-trust-items">
				<?php foreach ( $trust_items as $item ) : ?>
					<div class="cta-trust-item">&#x2713; <?php echo esc_html( $item ); ?></div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>
