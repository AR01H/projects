<?php

use VintageSoul\Controllers\EventsController;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$data = ( new EventsController() )->prepare();

$hero             = (array) ( $data['hero'] ?? array() );
$inclusions_data  = (array) ( $data['inclusions'] ?? array() );
$event_inclusions = (array) ( $inclusions_data['items'] ?? array() );
$process          = (array) ( $data['process'] ?? array() );
$gallery          = (array) ( $data['gallery'] ?? array() );
$event_gallery    = (array) ( $gallery['items'] ?? array() );
$reviews_data     = (array) ( $data['reviews'] ?? array() );
$event_reviews    = (array) ( $reviews_data['items'] ?? array() );
$faqs             = (array) ( $data['faqs'] ?? array() );
?>

<!-- ═══════════ 1. COMMON LUXURY VINTAGE SUBPAGE HERO HEADER ═══════════ -->
<?php
View::component(
	'subpage-hero/subpage-hero',
	array(
		'id'    => 'events-hero',
		'tag'   => (string) ( $hero['tag'] ?? '' ),
		'title' => (string) ( $hero['title'] ?? '' ),
		'sub'   => (string) ( $hero['sub'] ?? '' ),
		'image' => (string) ( $hero['image'] ?? '' ),
	)
);
?>

<?php View::component( 'background/parchment-botanical-bg', array( 'seed' => 52 ) ); ?>

<!-- ═══════════ 2. WHAT IS INCLUDED WITH EVERY BOOKING ═══════════ -->
<section class="section" id="inclusions">
	<div class="container">
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'   => (string) ( $inclusions_data['tag'] ?? '' ),
				'title' => (string) ( $inclusions_data['title'] ?? '' ),
				'sub'   => (string) ( $inclusions_data['sub'] ?? '' ),
			)
		);
		?>

		<div class="events-types-grid" style="margin-bottom: 20px;">
			<?php foreach ( $event_inclusions as $inc ) : ?>
				<div class="event-type-card card--rough-cut">
					<div class="event-type-card__head">
						<span class="event-type-card__icon"><?php echo esc_html( (string) ( $inc['icon'] ?? '✦' ) ); ?></span>
					</div>
					<h3 class="event-type-card__title"><?php echo esc_html( (string) ( $inc['title'] ?? '' ) ); ?></h3>
					<p class="event-type-card__desc"><?php echo esc_html( (string) ( $inc['desc'] ?? '' ) ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ═══════════ 3. LIVE EVENT MOVING CARD STREAMS ═══════════ -->
<section class="section section--events events-vintage paper-rough" style="padding-top: 10px; padding-bottom: 20px;">
	<div class="container events-vintage__container">
		<?php if ( ! empty( $gallery['ribbon'] ) ) : ?>
			<div class="vintage-ribbon-tag vintage-ribbon-tag--gold">
				<span><?php echo esc_html( (string) $gallery['ribbon'] ); ?></span>
			</div>
		<?php endif; ?>
		<?php
		View::component( 'card-stream/card-stream', array(
			'items'      => $event_gallery,
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
			'items'      => $event_reviews,
			'card_type'  => 'review',
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

<!-- ═══════════ 4. HOW BOOKING WORKS (Step Chain) ═══════════ -->
<?php if ( ! empty( $process['items'] ) ) : ?>
	<section class="section section--alt" id="booking-process">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $process['tag'] ?? '' ),
					'title' => (string) ( $process['title'] ?? '' ),
					'sub'   => (string) ( $process['sub'] ?? '' ),
				)
			);
			View::component(
				'step-chain/step-chain',
				array( 'items' => (array) ( $process['items'] ?? array() ) )
			);
			?>
		</div>
	</section>
<?php endif; ?>

<!-- ═══════════ 5. FEATURED TRUST STRIP ═══════════ -->
<?php View::component( 'sections/logo-strip-section' ); ?>

<!-- ═══════════ 6. DIRECT EVENT CONCIERGE BOOKING ═══════════ -->
<div id="event-booking">
	<?php View::component( 'sections/contact-form-section' ); ?>
</div>

<!-- ═══════════ 7. EVENT FAQS ═══════════ -->
<?php if ( ! empty( $faqs['items'] ) ) : ?>
	<?php
	View::component(
		'faq/faq',
		array(
			'tag'     => (string) ( $faqs['tag'] ?? '' ),
			'heading' => (string) ( $faqs['title'] ?? '' ),
			'items'   => (array) ( $faqs['items'] ?? array() ),
			'id'      => 'event-faqs',
		)
	);
	?>
<?php endif; ?>
