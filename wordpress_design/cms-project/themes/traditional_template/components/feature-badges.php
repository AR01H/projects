<?php
/**
 * Feature badges - a compact strip of trust / benefit points.
 *
 * GENERIC: renders whatever badges the JSON supplies (icon + label + note), so
 * it suits any industry's selling points. Switch data per page with `source`.
 * Data: { items[] { icon, label, note } }
 */
defined( 'ABSPATH' ) || exit;

$fb_source = ( isset( $source ) && $source ) ? (string) $source : 'feature_badges';
$data      = nt_data( $fb_source );
$items     = ( is_array( $data ) && ! empty( $data['items'] ) ) ? (array) $data['items'] : array();
if ( empty( $items ) ) {
	return;
}
?>
<section class="nt-badges">
	<div class="container nt-badges__inner">
		<?php foreach ( $items as $item ) :
			$item  = (array) $item;
			$label = $item['label'] ?? '';
			if ( '' === trim( (string) $label ) ) {
				continue;
			}
		?>
			<div class="nt-badges__item">
				<?php if ( ! empty( $item['icon'] ) ) : ?>
					<span class="nt-badges__icon" aria-hidden="true"><?php echo esc_html( $item['icon'] ); ?></span>
				<?php endif; ?>
				<span class="nt-badges__label"><?php echo esc_html( $label ); ?></span>
				<?php if ( ! empty( $item['note'] ) ) : ?>
					<span class="nt-badges__note"><?php echo esc_html( $item['note'] ); ?></span>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
