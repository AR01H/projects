<?php
defined( 'ABSPATH' ) || exit;
$items = ch_get_marquee_items();
if ( empty( $items ) ) return;
?>
<div class="ch-ticker" role="marquee" aria-label="<?php esc_attr_e( 'Latest updates', 'ch-theme' ); ?>">
	<div class="ch-ticker__track">
		<?php foreach ( $items as $item ) : ?>
			<span class="ch-ticker__item"><?php echo esc_html( $item ); ?></span>
			<span class="ch-ticker__sep" aria-hidden="true">✦</span>
		<?php endforeach; ?>
		<?php foreach ( $items as $item ) : ?>
			<span class="ch-ticker__item"><?php echo esc_html( $item ); ?></span>
			<span class="ch-ticker__sep" aria-hidden="true">✦</span>
		<?php endforeach; ?>
	</div>
</div>
