<?php
defined( 'ABSPATH' ) || exit;
$items = ch_get_marquee_items();
if ( empty( $items ) ) return;
?>
<div class="ch-marquee-wrap" aria-hidden="true">
	<div class="ch-marquee-track">
		<?php foreach ( $items as $item ) : ?>
			<span class="ch-marquee-item"><?php echo esc_html( $item ); ?></span>
			<span class="ch-marquee-sep">✦</span>
		<?php endforeach; ?>
		<?php foreach ( $items as $item ) : ?>
			<span class="ch-marquee-item"><?php echo esc_html( $item ); ?></span>
			<span class="ch-marquee-sep">✦</span>
		<?php endforeach; ?>
	</div>
</div>
