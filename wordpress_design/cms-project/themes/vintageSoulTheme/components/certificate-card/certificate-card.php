<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;

$image  = isset( $image ) ? (string) $image : '';
$icon   = isset( $icon ) ? (string) $icon : '';
$title  = isset( $title ) ? trim( (string) $title ) : '';
$desc   = isset( $desc ) ? (string) $desc : '';
$badge  = isset( $badge ) ? (string) $badge : '';
$button = ( isset( $button ) && is_array( $button ) ) ? $button : array();

if ( '' === $title ) {
	return;
}
?>
<div class="card certificate-card card--horizontal">
	<?php if ( '' !== $image ) : ?>
		<div class="card__media">
			<img src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy">
		</div>
	<?php elseif ( '' !== $icon ) : ?>
		<div class="card__media certificate-card__icon roughness-b" aria-hidden="true"><?php echo esc_html( $icon ); ?></div>
	<?php endif; ?>
	<div class="card__body">
		<h3 class="card__title"><?php echo esc_html( $title ); ?></h3>
		<?php if ( '' !== $desc ) : ?>
			<p class="certificate-card__desc"><?php echo esc_html( $desc ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $badge ) : ?>
			<span class="badge badge--outline certificate-card__badge"><?php echo esc_html( $badge ); ?></span>
		<?php endif; ?>
		<?php if ( ! empty( $button['label'] ) && ! empty( $button['route'] ) ) : ?>
			<a class="btn btn--sm certificate-card__cta" href="<?php echo esc_url( RouteService::url( (string) $button['route'] ) ); ?>"><?php echo esc_html( $button['label'] ); ?></a>
		<?php endif; ?>
	</div>
</div>
