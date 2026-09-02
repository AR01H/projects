<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\UrlHelper;

$enquiry_data = (array) ( JsonFileProvider::read( 'data/content/enquiry.json' ) ?? array() );

$tag           = (string) ( $tag ?? ( $enquiry_data['tag'] ?? '' ) );
$title         = (string) ( $title ?? ( $enquiry_data['title'] ?? '' ) );
$eyebrow       = (string) ( $eyebrow ?? ( $enquiry_data['eyebrow'] ?? '' ) );
$body          = (string) ( $body ?? ( $enquiry_data['body'] ?? '' ) );
$cta           = (array) ( $cta ?? ( $enquiry_data['cta'] ?? array() ) );
$secondary_cta = (array) ( $secondary_cta ?? ( $enquiry_data['secondary_cta'] ?? array() ) );
$image         = (string) ( $image ?? ( $enquiry_data['image'] ?? '' ) );
$pills         = (array) ( $enquiry_data['pills'] ?? array() );
$img_url       = '' !== $image ? UrlHelper::resolve( $image ) : '';

if ( '' === $title && '' === $body ) {
	return;
}
?>
<section class="section section--enquiry enquiry-vintage" id="enquiry">
	<div class="container enquiry-vintage__container">
		<div class="enquiry-vintage__card frame--ornate grain-dark">
			
			<!-- Left Column: Content -->
			<div class="enquiry-vintage__content">
				<?php if ( '' !== $tag ) : ?>
					<span class="vintage-ribbon-tag vintage-ribbon-tag--gold">
						<span><?php echo esc_html( $tag ); ?></span>
					</span>
				<?php endif; ?>
				<?php if ( '' !== $title ) : ?>
					<h2 class="enquiry-vintage__title"><?php echo esc_html( $title ); ?></h2>
				<?php endif; ?>
				<?php if ( '' !== $eyebrow ) : ?>
					<p class="section-eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $body ) : ?>
					<p class="enquiry-vintage__body"><?php echo esc_html( $body ); ?></p>
				<?php endif; ?>
				
				<div class="enquiry-vintage__actions">
					<?php if ( ! empty( $cta['label'] ) ) : ?>
						<a class="btn btn--primary-vintage btn--order-now" href="<?php echo esc_url( RouteService::url( (string) ( $cta['route'] ?? 'contact' ) ) ); ?>">
							<span class="btn__icon">✉️</span>
							<span><?php echo esc_html( (string) $cta['label'] ); ?></span>
						</a>
					<?php endif; ?>
					<?php if ( ! empty( $secondary_cta['label'] ) ) : ?>
						<a class="btn btn--secondary-vintage btn--outline-vintage" href="<?php echo esc_url( RouteService::url( (string) ( $secondary_cta['route'] ?? 'contact' ) ) ); ?>">
							<span class="btn__icon">📞</span>
							<span><?php echo esc_html( (string) $secondary_cta['label'] ); ?></span>
						</a>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $pills ) ) : ?>
					<div class="enquiry-vintage__quick-contact">
						<?php foreach ( $pills as $pill ) : ?>
							<span class="quick-contact-pill"><?php echo esc_html( (string) $pill ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Right Column: Visual Frame -->
			<?php if ( '' !== $img_url ) : ?>
				<div class="enquiry-vintage__media">
					<div class="enquiry-vintage__photo frame--ornate">
						<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy">
					</div>
				</div>
			<?php endif; ?>

		</div>
	</div>
</section>
