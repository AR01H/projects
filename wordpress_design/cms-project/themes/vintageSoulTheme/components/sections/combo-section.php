<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\UrlHelper;

$title = (string) ( $title ?? 'MAKE IT A COMBO!' );
$body  = (string) ( $body ?? 'Add jaggery, lemon or ginger for an extra burst of flavour.' );
$image = UrlHelper::resolve( (string) ( $image ?? 'assets/images/sugarcane/combo.jpg' ) );
$cta   = (array) ( $cta ?? array( 'label' => 'Order Combo', 'route' => 'contact' ) );
?>
<section class="section section--combo combo-vintage">
	<div class="container container--narrow combo-vintage__container frame--ornate">
		<div class="combo-vintage__content">
			<h3 class="combo-vintage__title"><?php echo esc_html( $title ); ?></h3>
			<p class="combo-vintage__body"><?php echo esc_html( $body ); ?></p>
		</div>
		<div class="combo-vintage__media">
			<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy">
		</div>
	</div>
</section>
