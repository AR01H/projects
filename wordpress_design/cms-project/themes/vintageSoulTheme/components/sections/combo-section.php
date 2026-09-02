<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\UrlHelper;

$title = (string) ( $title ?? '' );
$body  = (string) ( $body ?? '' );
$image = (string) ( $image ?? '' );
$cta   = (array) ( $cta ?? array() );

if ( '' === $title && '' === $body ) {
	return;
}

$img_url = '' !== $image ? UrlHelper::resolve( $image ) : '';
?>
<section class="section section--combo combo-vintage">
	<div class="container container--narrow combo-vintage__container frame--ornate">
		<div class="combo-vintage__content">
			<?php if ( '' !== $title ) : ?>
				<h3 class="combo-vintage__title"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>
			<?php if ( '' !== $body ) : ?>
				<p class="combo-vintage__body"><?php echo esc_html( $body ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( '' !== $img_url ) : ?>
			<div class="combo-vintage__media">
				<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy">
			</div>
		<?php endif; ?>
	</div>
</section>
