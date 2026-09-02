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
	<?php View::component( 'background/ambient-layer', array( 'variant' => 'light', 'cane_positions' => array( 'top-right', 'bottom-left' ), 'bubble_count' => 12 ) ); ?>
	<div class="container events-vintage__container">
		
		<!-- 1. Header & Overview -->
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'     => $tag,
				'title'   => $title,
				'sub'     => $sub,
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

		<!-- 3. Catering Packages & Setups Full-Width Showcase -->
		<?php
		$pkg_data  = (array) ( $events_data['packages'] ?? array() );
		$pkg_items = (array) ( $pkg_data['items'] ?? array() );
		if ( ! empty( $pkg_items ) ) :
		?>
			<div class="vintage-ribbon-tag vintage-ribbon-tag--gold" style="margin-top: 36px !important;">
				<span>CATERING PACKAGES &amp; SETUPS</span>
			</div>
			<div class="events-packages-grid">
				<?php foreach ( $pkg_items as $pkg ) :
					$pkg_name     = (string) ( $pkg['name'] ?? '' );
					$pkg_tag      = (string) ( $pkg['tagline'] ?? '' );
					$pkg_guest    = (string) ( $pkg['guests'] ?? '' );
					$pkg_badge    = (string) ( $pkg['badge'] ?? '' );
					$pkg_duration = (string) ( $pkg['duration'] ?? '3 Hours' );
					$pkg_servings = (string) ( $pkg['servings'] ?? 'Unlimited' );
					$pkg_foot     = (string) ( $pkg['footprint'] ?? 'Live Bar' );
					$pkg_feats    = (array) ( $pkg['features'] ?? array() );
				?>
					<div class="event-package-card card--rough-cut">
						<div class="event-package-card__badge-row">
							<?php if ( '' !== $pkg_badge ) : ?>
								<span class="event-package-card__badge"><?php echo esc_html( $pkg_badge ); ?></span>
							<?php endif; ?>
							<span class="event-package-card__guest-tag">👥 <?php echo esc_html( $pkg_guest ); ?></span>
						</div>
						
						<h3 class="event-package-card__title"><?php echo esc_html( $pkg_name ); ?></h3>
						<p class="event-package-card__tagline"><?php echo esc_html( $pkg_tag ); ?></p>
						
						<div class="event-package-card__specs">
							<div class="event-package-card__spec-item">
								<span class="event-package-card__spec-lbl">Service Duration</span>
								<strong class="event-package-card__spec-val"><?php echo esc_html( $pkg_duration ); ?></strong>
							</div>
							<div class="event-package-card__spec-item">
								<span class="event-package-card__spec-lbl">Serving Capacity</span>
								<strong class="event-package-card__spec-val"><?php echo esc_html( $pkg_servings ); ?></strong>
							</div>
							<div class="event-package-card__spec-item">
								<span class="event-package-card__spec-lbl">Bar Footprint</span>
								<strong class="event-package-card__spec-val"><?php echo esc_html( $pkg_foot ); ?></strong>
							</div>
						</div>

						<div class="event-package-card__features-title">Package Inclusions:</div>
						<ul class="event-package-card__features-list">
							<?php foreach ( $pkg_feats as $feat ) : ?>
								<li>✓ <?php echo esc_html( (string) $feat ); ?></li>
							<?php endforeach; ?>
						</ul>

						<a href="<?php echo esc_url( RouteService::url( 'contact' ) ); ?>" class="btn btn--primary-vintage event-package-card__btn">
							<span>BOOK THIS PACKAGE ✦</span>
						</a>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- 4. Real Live Event Photo Stream (Left-to-Right) -->
		<div class="vintage-ribbon-tag vintage-ribbon-tag--gold" style="margin-top: 36px !important;">
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
