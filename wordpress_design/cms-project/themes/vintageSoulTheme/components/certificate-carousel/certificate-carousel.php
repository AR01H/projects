<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Support\View;

$items = isset( $items ) && is_array( $items ) ? $items : array();

if ( empty( $items ) ) {
	return;
}
?>
<div class="certificate-carousel" data-vs-certificate-carousel>
	<div class="certificate-carousel__track">
		<?php foreach ( $items as $item ) : ?>
			<?php View::component( 'certificate-card/certificate-card', (array) $item ); ?>
		<?php endforeach; ?>
	</div>
	<button type="button" class="carousel-arrow certificate-carousel__arrow certificate-carousel__arrow--prev" aria-label="<?php esc_attr_e( 'Scroll left', 'vintagesoul' ); ?>">
		<span aria-hidden="true">&larr;</span>
	</button>
	<button type="button" class="carousel-arrow certificate-carousel__arrow certificate-carousel__arrow--next" aria-label="<?php esc_attr_e( 'Scroll right', 'vintagesoul' ); ?>">
		<span aria-hidden="true">&rarr;</span>
	</button>
</div>
