<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Support\View;

$image = isset( $image ) ? (string) $image : '';
$stamp = ( isset( $stamp ) && is_array( $stamp ) ) ? $stamp : array();
$stamp_center = trim( (string) ( $stamp['center'] ?? '' ) );
$id = ( isset( $id ) && '' !== trim( (string) $id ) ) ? sanitize_html_class( (string) $id ) : 'photo-stamp';

if ( '' === $image ) {
	return;
}
?>
<div class="photo-stamp">
	<div class="photo-stamp__media hover-zoom tex-film-grain-a tex-dust-a">
		<img src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy">
	</div>
	<?php if ( '' !== $stamp_center ) : ?>
		<div class="photo-stamp__stamp">
			<?php
			View::component(
				'stamp/stamp',
				array(
					'id'     => $id . '-stamp',
					'top'    => (string) ( $stamp['top'] ?? '' ),
					'center' => $stamp_center,
					'bottom' => (string) ( $stamp['bottom'] ?? '' ),
					'size'   => isset( $stamp['size'] ) ? (int) $stamp['size'] : 110,
				)
			);
			?>
		</div>
	<?php endif; ?>
</div>
