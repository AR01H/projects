<?php

defined( 'ABSPATH' ) || exit;

$items = (array) ( $items ?? array() );
if ( empty( $items ) ) {
	return;
}
?>
<div class="ribbon-ticker ribbon-ticker--dark">
	<div class="ribbon-ticker__track">
		<?php for ( $r = 0; $r < 4; $r++ ) : ?>
			<?php foreach ( $items as $item ) : ?>
				<span class="ribbon-ticker__heart">♡</span>
				<span class="ribbon-ticker__text"><?php echo esc_html( (string) $item ); ?></span>
			<?php endforeach; ?>
		<?php endfor; ?>
	</div>
</div>
