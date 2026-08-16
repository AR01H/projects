<?php

defined( 'ABSPATH' ) || exit;

$icon  = isset( $icon ) ? (string) $icon : '';
$title = isset( $title ) ? trim( (string) $title ) : '';
$text  = isset( $text ) ? (string) $text : '';
$stat  = isset( $stat ) ? (string) $stat : '';

if ( '' === $title ) {
	return;
}
?>
<div class="card feature-card">
	<div class="card__body">
		<?php if ( '' !== $icon ) : ?>
			<span class="feature-card__icon" aria-hidden="true"><?php echo esc_html( $icon ); ?></span>
		<?php endif; ?>
		<h3 class="card__title"><?php echo esc_html( $title ); ?></h3>
		<?php if ( '' !== $text ) : ?>
			<p class="feature-card__text"><?php echo esc_html( $text ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $stat ) : ?>
			<span class="feature-card__stat"><?php echo esc_html( $stat ); ?></span>
		<?php endif; ?>
	</div>
</div>
