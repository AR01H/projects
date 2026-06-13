<?php
/**
 * components/parts/sidebar_market_snapshot.php - Market key/value stats with change badges.
 *
 * Props: $market_snapshot { heading, items[] { label, value, change, change_class }, updated }
 * change_class values: msi-up | msi-down | msi-flat
 * Usage: adn_component( 'parts/sidebar_market_snapshot', array( 'market_snapshot' => $ctx['sidebar']['market_snapshot'] ) );
 */

defined( 'ABSPATH' ) || exit;

$market_snapshot = isset( $market_snapshot ) && is_array( $market_snapshot ) ? $market_snapshot : array();
$items           = isset( $market_snapshot['items'] ) ? (array) $market_snapshot['items'] : array();

if ( empty( $items ) ) {
	return;
}
?>
<div class="news-sb-box">
	<?php if ( ! empty( $market_snapshot['heading'] ) ) : ?>
		<div class="news-sb-title"><?php echo esc_html( $market_snapshot['heading'] ); ?></div>
	<?php endif; ?>

	<ul class="market-snap-list">
		<?php foreach ( $items as $item ) :
			$change_class = isset( $item['change_class'] ) ? sanitize_html_class( $item['change_class'] ) : 'msi-flat';
		?>
			<li class="market-snap-item">
				<span class="msi-label"><?php echo esc_html( isset( $item['label'] ) ? $item['label'] : '' ); ?></span>
				<span class="msi-right">
					<span class="msi-value"><?php echo esc_html( isset( $item['value'] ) ? $item['value'] : '' ); ?></span>
					<?php if ( ! empty( $item['change'] ) ) : ?>
						<span class="msi-change <?php echo esc_attr( $change_class ); ?>">
							<?php echo esc_html( $item['change'] ); ?>
						</span>
					<?php endif; ?>
				</span>
			</li>
		<?php endforeach; ?>
	</ul>

	<?php if ( ! empty( $market_snapshot['updated'] ) ) : ?>
		<p class="msi-updated"><?php echo esc_html( $market_snapshot['updated'] ); ?></p>
	<?php endif; ?>
</div>
