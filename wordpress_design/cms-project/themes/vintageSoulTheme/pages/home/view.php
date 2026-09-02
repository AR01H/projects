<?php

use VintageSoul\Controllers\HomeController;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$data = ( new HomeController() )->prepare();

$hero             = (array) ( $data['hero'] ?? array() );
$ticker           = (array) ( $data['ticker'] ?? array() );
$intro            = (array) ( $data['intro'] ?? array() );
$story            = (array) ( $data['story'] ?? array() );
$sourcing         = (array) ( $data['sourcing'] ?? array() );
$products         = (array) ( $data['products'] ?? array() );
$certs            = (array) ( $data['certifications'] ?? array() );
$benefits         = (array) ( $data['benefits'] ?? array() );
$serve            = (array) ( $data['serve_steps'] ?? array() );
$memories         = (array) ( $data['memories'] ?? array() );
$testimonials     = (array) ( $data['testimonials_meta'] ?? array() );
$community        = (array) ( $data['community'] ?? array() );
$logo_strip       = (array) ( $data['logo_strip'] ?? array() );
$gallery          = (array) ( $data['gallery'] ?? array() );
$events           = (array) ( $data['events'] ?? array() );
$franchise_teaser = (array) ( $data['franchise_teaser'] ?? array() );
$combo_upsell     = (array) ( $data['combo_upsell'] ?? array() );
$video_showcase   = (array) ( $data['video_showcase'] ?? array() );
$order_steps      = (array) ( $data['order_steps'] ?? array() );
$enquiry          = (array) ( $data['enquiry'] ?? array() );
$faqs             = (array) ( $data['faqs'] ?? array() );
$contact          = (array) ( $data['contact'] ?? array() );
$closing          = (array) ( $data['closing'] ?? array() );

$deckled_edge_url = UrlHelper::resolve( 'assets/images/textures/border/deckled-edge.svg' );
?>

<?php
// ══════════════════════════════════════════════════════════════════════════
// 1. HERO & WELCOME ARC
// ══════════════════════════════════════════════════════════════════════════
?>
<?php if ( ! empty( $hero['slides'] ) ) : ?>
	<?php View::component( 'sections/hero-section', array( 'hero' => $hero ) ); ?>
<?php endif; ?>

<?php if ( ! empty( $ticker['items'] ) ) : ?>
	<?php View::component( 'sections/ticker-section', array( 'items' => $ticker['items'] ) ); ?>
<?php endif; ?>

<?php if ( ! empty( $intro['title'] ) ) : ?>
	<?php View::component( 'sections/intro-section', $intro ); ?>
	<div class="deckled-divider" aria-hidden="true">
		<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
	</div>
<?php endif; ?>

<?php
// ══════════════════════════════════════════════════════════════════════════
// 2. SIGNATURE DRINKS & EXTRACTION THEATRE
// ══════════════════════════════════════════════════════════════════════════
?>
<?php if ( ! empty( $products['items'] ) ) : ?>
	<?php View::component( 'sections/order-juice-section', array( 'products' => (array) ( $products['items'] ?? array() ) ) ); ?>
	<div class="deckled-divider" aria-hidden="true">
		<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
	</div>
<?php endif; ?>

<?php if ( ! empty( $video_showcase['videos'] ) ) : ?>
	<?php View::component( 'sections/video-showcase-section', array( 'showcase_data' => $video_showcase ) ); ?>
	<div class="deckled-divider" aria-hidden="true">
		<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
	</div>
<?php endif; ?>

<?php View::component( 'sections/trust-ribbon-section' ); ?>
<div class="deckled-divider" aria-hidden="true">
	<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
</div>

<?php
// ══════════════════════════════════════════════════════════════════════════
// 3. HERITAGE, SOURCING & BOTANICAL BENEFITS
// ══════════════════════════════════════════════════════════════════════════
?>
<?php if ( ! empty( $story ) ) : ?>
	<?php View::component( 'sections/story-section', $story ); ?>
	<div class="deckled-divider" aria-hidden="true">
		<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
	</div>
<?php endif; ?>

<?php if ( ! empty( $sourcing ) ) : ?>
	<?php View::component( 'sections/sourcing-section', $sourcing ); ?>
	<div class="deckled-divider" aria-hidden="true">
		<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
	</div>
<?php endif; ?>

<?php if ( ! empty( $benefits['items'] ) ) : ?>
	<?php View::component( 'sections/benefits-section', $benefits ); ?>
	<div class="deckled-divider" aria-hidden="true">
		<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
	</div>
<?php endif; ?>

<?php
// ══════════════════════════════════════════════════════════════════════════
// 4. SOCIAL PROOF & REVIEWS
// ══════════════════════════════════════════════════════════════════════════
?>
<?php View::component( 'sections/social-stream-section' ); ?>
<div class="deckled-divider" aria-hidden="true">
	<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
</div>

<?php if ( ! empty( $testimonials['items'] ) ) : ?>
	<?php View::component( 'sections/reviews-section', $testimonials ); ?>
	<div class="deckled-divider" aria-hidden="true">
		<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
	</div>
<?php endif; ?>

<?php if ( ! empty( $logo_strip['items'] ) ) : ?>
	<?php View::component( 'sections/logo-strip-section', $logo_strip ); ?>
	<div class="deckled-divider" aria-hidden="true">
		<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
	</div>
<?php endif; ?>

<?php
// ══════════════════════════════════════════════════════════════════════════
// 5. LIVE EVENTS CATERING & FRANCHISE PARTNERSHIPS
// ══════════════════════════════════════════════════════════════════════════
?>
<?php View::component( 'sections/events-section', $events ); ?>
<div class="deckled-divider" aria-hidden="true">
	<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
</div>

<?php View::component( 'sections/franchise-section', $franchise_teaser ); ?>
<div class="deckled-divider" aria-hidden="true">
	<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
</div>

<?php if ( ! empty( $community['items'] ) ) : ?>
	<?php View::component( 'sections/community-section', $community ); ?>
	<div class="deckled-divider" aria-hidden="true">
		<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
	</div>
<?php endif; ?>

<?php
// ══════════════════════════════════════════════════════════════════════════
// 6. FAQS & CONCIERGE CONTACT / INQUIRY
// ══════════════════════════════════════════════════════════════════════════
?>
<?php if ( ! empty( $faqs['items'] ) ) : ?>
	<?php View::component( 'sections/faq-section', $faqs ); ?>
	<div class="deckled-divider" aria-hidden="true">
		<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
	</div>
<?php endif; ?>

<?php if ( ! empty( $certs['groups'] ) || ! empty( $certs['items'] ) ) : ?>
	<?php View::component( 'sections/certifications-section', $certs ); ?>
	<div class="deckled-divider" aria-hidden="true">
		<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
	</div>
<?php endif; ?>

<?php View::component( 'sections/contact-form-section', $contact ); ?>
<div class="deckled-divider" aria-hidden="true">
	<img src="<?php echo esc_url( $deckled_edge_url ); ?>" alt="" loading="lazy">
</div>
