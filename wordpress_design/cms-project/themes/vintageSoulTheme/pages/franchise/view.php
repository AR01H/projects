<?php

use VintageSoul\Controllers\FranchiseController;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$data = ( new FranchiseController() )->prepare();

$hero               = (array) ( $data['hero'] ?? array() );
$why                = (array) ( $data['why'] ?? array() );
$how                = (array) ( $data['how'] ?? array() );
$franchise_pillars  = (array) ( $data['pillars'] ?? array() );
$franchise_formats  = (array) ( $data['formats'] ?? array() );
$gallery            = (array) ( $data['gallery'] ?? array() );
$franchise_gallery  = (array) ( $gallery['items'] ?? array() );
$reviews_data       = (array) ( $data['reviews'] ?? array() );
$franchisee_reviews = (array) ( $reviews_data['items'] ?? array() );
$faqs               = (array) ( $data['faqs'] ?? array() );
$franchise_faqs     = (array) ( $faqs['items'] ?? array() );
$closing            = (array) ( $data['closing'] ?? array() );
?>

<!-- ═══════════ 1. COMMON LUXURY VINTAGE SUBPAGE HERO HEADER ═══════════ -->
<?php
View::component(
	'subpage-hero/subpage-hero',
	array(
		'id'    => 'franchise-hero',
		'tag'   => (string) ( $hero['tag'] ?? '' ),
		'title' => (string) ( $hero['title'] ?? '' ),
		'sub'   => (string) ( $hero['sub'] ?? '' ),
		'image' => (string) ( $hero['image'] ?? '' ),
	)
);
?>

<!-- ═══════════ 2. WHY PARTNER WITH US (4 Pillars Grid) ═══════════ -->
<section class="section section--alt" id="why-partner">
	<div class="container">
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'   => (string) ( $why['tag'] ?? '' ),
				'title' => (string) ( $why['title'] ?? '' ),
				'sub'   => (string) ( $why['sub'] ?? '' ),
			)
		);
		?>

		<div class="events-types-grid" style="margin-bottom: 20px;">
			<?php foreach ( $franchise_pillars as $pillar ) : ?>
				<div class="event-type-card card--rough-cut">
					<div class="event-type-card__head">
						<span class="event-type-card__icon"><?php echo esc_html( (string) ( $pillar['icon'] ?? '✦' ) ); ?></span>
					</div>
					<h3 class="event-type-card__title"><?php echo esc_html( (string) ( $pillar['title'] ?? '' ) ); ?></h3>
					<p class="event-type-card__desc"><?php echo esc_html( (string) ( $pillar['desc'] ?? '' ) ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ═══════════ 3. FRANCHISE MOVING CARD STREAMS ═══════════ -->
<section class="section section--franchise franchise-vintage-block torn-dark-block grain-dark" style="padding-top: 24px; padding-bottom: 30px;">
	<div class="container franchise-vintage__container">
		
		<?php if ( ! empty( $gallery['ribbon'] ) ) : ?>
			<div class="vintage-ribbon-tag vintage-ribbon-tag--gold">
				<span><?php echo esc_html( (string) $gallery['ribbon'] ); ?></span>
			</div>
		<?php endif; ?>
		<?php
		View::component( 'card-stream/card-stream', array(
			'items'      => $franchise_gallery,
			'card_type'  => 'gallery',
			'direction'  => 'ltr',
			'aria_label' => (string) ( $gallery['title'] ?? '' ),
		) );
		?>

		<?php if ( ! empty( $reviews_data['ribbon'] ) ) : ?>
			<div class="vintage-ribbon-tag">
				<span><?php echo esc_html( (string) $reviews_data['ribbon'] ); ?></span>
			</div>
		<?php endif; ?>
		<?php
		View::component( 'card-stream/card-stream', array(
			'items'      => $franchisee_reviews,
			'card_type'  => 'dark-review',
			'direction'  => 'rtl',
			'aria_label' => (string) ( $reviews_data['title'] ?? '' ),
		) );
		?>
	</div>
</section>

<!-- Deckled Border Divider -->
<div class="deckled-divider" aria-hidden="true">
	<img src="<?php echo esc_url( \VintageSoul\Support\UrlHelper::resolve( 'assets/images/textures/border/deckled-edge.svg' ) ); ?>" alt="" loading="lazy">
</div>

<!-- ═══════════ 4. HOW IT WORKS (Step Chain) ═══════════ -->
<?php if ( ! empty( $how['items'] ) ) : ?>
	<section class="section" id="franchise-steps">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $how['tag'] ?? '' ),
					'title' => (string) ( $how['title'] ?? '' ),
					'sub'   => (string) ( $how['sub'] ?? '' ),
				)
			);
			View::component(
				'step-chain/step-chain',
				array( 'items' => (array) ( $how['items'] ?? array() ) )
			);
			?>
		</div>
	</section>
<?php endif; ?>

<!-- ═══════════ 5. FEATURED TRUST STRIP ═══════════ -->
<?php View::component( 'sections/logo-strip-section' ); ?>

<!-- ═══════════ 6. DIRECT FRANCHISE APPLICATION & ENQUIRY ═══════════ -->
<div id="franchise-enquiry">
	<?php View::component( 'sections/contact-form-section' ); ?>
</div>

<!-- ═══════════ 7. FRANCHISE FAQS ═══════════ -->
<?php if ( ! empty( $franchise_faqs ) ) : ?>
	<?php
	View::component(
		'faq/faq',
		array(
			'tag'     => (string) ( $faqs['tag'] ?? '' ),
			'heading' => (string) ( $faqs['title'] ?? '' ),
			'items'   => $franchise_faqs,
			'id'      => 'franchise-faqs',
		)
	);
	?>
<?php endif; ?>

<!-- ═══════════ 8. TRUST RIBBON BOTTOM ═══════════ -->
<?php View::component( 'sections/trust-ribbon-section' ); ?>
