<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

$events_data = (array) ( JsonFileProvider::read( 'data/content/events.json' ) ?? array() );

$tag   = (string) ( $tag ?? ( $events_data['hero']['tag'] ?? 'Live Stalls • Weddings • Private Galas' ) );
$title = (string) ( $title ?? ( $events_data['hero']['title'] ?? 'EVENTS &amp; LIVE <em>Cane Bar Catering</em>' ) );
$sub   = (string) ( $sub ?? ( $events_data['hero']['sub'] ?? 'Bring the theatrical experience of freshly pressed sugarcane juice to your special occasion.' ) );

$event_types   = (array) ( $upcoming ?? ( $events_data['event_types'] ?? array() ) );
$event_gallery = (array) ( $gallery ?? ( $events_data['gallery']['items'] ?? array() ) );
$event_reviews = (array) ( $reviews ?? ( $events_data['reviews'] ?? array() ) );
?>
<section class="section section--events events-vintage paper-rough" id="events">
	<?php View::component( 'background/ambient-layer', array( 'variant' => 'dark', 'cane_positions' => array( 'top-right', 'bottom-left' ), 'bubble_count' => 12 ) ); ?>
	<div class="container events-vintage__container">
		
		<!-- 1. Header & Overview -->
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'     => 'Bespoke Experience',
				'title'   => 'LIVE CANE BAR &amp; <em>Catering</em>',
				'eyebrow' => 'Weddings, Private Events & Corporate Celebrations',
				'sub'     => $sub,
				'variant' => 'dark',
				'ribbon'  => true,
			)
		);
		?>

		<!-- 2. Event Types & Packages Grid -->
		<div class="events-types-grid">
			<?php foreach ( $event_types as $evt ) :
				$e_icon     = (string) ( $evt['icon'] ?? '🌿' );
				$e_tag      = (string) ( $evt['tag'] ?? ( $evt['date'] ?? '' ) );
				$e_title    = (string) ( $evt['title'] ?? '' );
				$e_desc     = (string) ( $evt['desc'] ?? '' );
				$e_features = (array) ( $evt['features'] ?? array() );
			?>
				<div class="event-type-card card--rough-cut">
					<div class="event-type-card__head">
						<span class="event-type-card__icon"><?php echo IconHelper::render( $e_icon, '#8e622d', 26 ); // phpcs:ignore ?></span>
						<?php if ( '' !== $e_tag ) : ?>
							<span class="event-type-card__tag"><?php echo esc_html( $e_tag ); ?></span>
						<?php endif; ?>
					</div>
					<h3 class="event-type-card__title"><?php echo esc_html( $e_title ); ?></h3>
					<?php if ( '' !== $e_desc ) : ?>
						<p class="event-type-card__desc"><?php echo esc_html( $e_desc ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $e_features ) ) : ?>
						<ul class="event-type-card__features">
							<?php foreach ( $e_features as $feat ) : ?>
								<li>✓ <?php echo esc_html( (string) $feat ); ?></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- 3. Real Live Event Photo Stream (Left-to-Right) -->
		<div class="vintage-ribbon-tag vintage-ribbon-tag--gold">
			<span>LIVE EVENTS IN ACTION</span>
		</div>
		<?php
		View::component( 'card-stream/card-stream', array(
			'items'      => $event_gallery,
			'card_type'  => 'gallery',
			'direction'  => 'ltr',
		) );
		?>

		<!-- 4. Client Experiences & Reviews Stream (Right-to-Left) -->
		<div class="vintage-ribbon-tag">
			<span>CLIENT EXPERIENCES</span>
		</div>
		<?php
		View::component( 'card-stream/card-stream', array(
			'items'      => $event_reviews,
			'card_type'  => 'review',
			'direction'  => 'rtl',
		) );
		?>

		<!-- Quick Booking Banner -->
		<div class="events-booking-banner card--rough-cut-dark">
			<div class="events-booking-banner__content">
				<h3 class="events-booking-banner__title">READY TO BOOK OUR LIVE CANE BAR?</h3>
				<p class="events-booking-banner__text">Dates fill up fast for weekends & summer wedding seasons. Contact us today to check availability and get a tailored quote.</p>
			</div>
			<div class="events-booking-banner__actions">
				<a class="btn btn--primary-vintage" href="<?php echo esc_url( \VintageSoul\Services\SettingsService::whatsapp_url() ); ?>" target="_blank" rel="noopener">
					<span class="btn__icon"><?php echo IconHelper::render( 'whatsapp', '#f6d599', 15 ); // phpcs:ignore ?></span>
					<span>CHAT ON WHATSAPP</span>
				</a>
				<a class="btn btn--outline-vintage" href="<?php echo esc_url( RouteService::url( 'contact' ) ); ?>">
					<span class="btn__icon"><?php echo IconHelper::render( 'mail', '#f6d599', 15 ); // phpcs:ignore ?></span>
					<span>ENQUIRE FOR EVENT</span>
				</a>
			</div>
		</div>

	</div>
</section>
