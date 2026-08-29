<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\UrlHelper;

$tag   = (string) ( $tag ?? 'Get In Touch' );
$title = (string) ( $title ?? 'DIRECT ENQUIRY' );
$eyebrow = (string) ( $eyebrow ?? 'Have a question or special request?' );
$body  = (string) ( $body ?? 'We are here to help! Whether you need live sugarcane pressing for your wedding, festival stall bookings, or fresh delivery, our team is ready.' );
$cta   = (array) ( $cta ?? array( 'label' => 'MAKE AN ENQUIRY', 'route' => 'contact' ) );
$image = (string) ( $image ?? 'assets/images/sugarcane/hero_juice.jpg' );
$img_url = UrlHelper::resolve( $image );
?>
<section class="section section--enquiry enquiry-vintage" id="enquiry">
	<div class="container enquiry-vintage__container">
		<div class="enquiry-vintage__card frame--ornate grain-dark">
			
			<!-- Left Column: Content -->
			<div class="enquiry-vintage__content">
				<span class="vintage-ribbon-tag vintage-ribbon-tag--gold">
					<span><?php echo esc_html( $tag ); ?></span>
				</span>
				<h2 class="enquiry-vintage__title"><?php echo esc_html( $title ); ?></h2>
				<p class="section-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<p class="enquiry-vintage__body"><?php echo esc_html( $body ); ?></p>
				
				<div class="enquiry-vintage__actions">
					<a class="btn btn--primary-vintage btn--order-now" href="<?php echo esc_url( RouteService::url( (string) ( $cta['route'] ?? 'contact' ) ) ); ?>">
						<span class="btn__icon">✉️</span>
						<span><?php echo esc_html( (string) ( $cta['label'] ?? 'MAKE AN ENQUIRY' ) ); ?></span>
					</a>
					<a class="btn btn--secondary-vintage btn--outline-vintage" href="<?php echo esc_url( RouteService::url( 'contact' ) ); ?>">
						<span class="btn__icon">📞</span>
						<span>CALL US DIRECTLY</span>
					</a>
				</div>

				<div class="enquiry-vintage__quick-contact">
					<span class="quick-contact-pill">📍 London, UK</span>
					<span class="quick-contact-pill">⏱️ Response within 24h</span>
					<span class="quick-contact-pill">🎪 Events & Markets</span>
				</div>
			</div>

			<!-- Right Column: Framed Vintage Cut Image -->
			<div class="enquiry-vintage__media-wrap">
				<div class="enquiry-vintage__photo-frame">
					<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy" class="enquiry-vintage__img hover-zoom">
					<div class="enquiry-vintage__stamp stamp-circle">
						<span class="stamp-circle__line1">EST. 2014</span>
						<span class="stamp-circle__line2">DIRECT</span>
						<span class="stamp-circle__line3">ENQUIRY</span>
					</div>
				</div>
			</div>

		</div>
	</div>
</section>
