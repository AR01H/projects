<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;

$name   = isset( $name ) ? trim( (string) $name ) : '';
$desc   = isset( $desc ) ? (string) $desc : '';
$price  = isset( $price ) ? (string) $price : '';
$image  = isset( $image ) ? (string) $image : '';
$button = ( isset( $button ) && is_array( $button ) ) ? $button : array();

if ( '' === $name ) {
	return;
}
?>
<div class="card product-card">
	<?php if ( '' !== $image ) : ?>
		<div class="card__media hover-zoom">
			<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
		</div>
	<?php endif; ?>
	<div class="card__body">
		<div class="product-card__row">
			<h3 class="card__title"><?php echo esc_html( $name ); ?></h3>
			<?php if ( '' !== $price ) : ?>
				<span class="product-card__price"><?php echo esc_html( $price ); ?></span>
			<?php endif; ?>
		</div>
		<?php if ( '' !== $desc ) : ?>
			<p class="product-card__desc"><?php echo esc_html( $desc ); ?></p>
		<?php endif; ?>
	</div>
	<?php if ( ! empty( $button['label'] ) && ! empty( $button['route'] ) ) : ?>
		<div class="card__footer">
			<a class="btn btn--sm" href="<?php echo esc_url( RouteService::url( (string) $button['route'] ) ); ?>"><?php echo esc_html( $button['label'] ); ?></a>
		</div>
	<?php endif; ?>
</div>
