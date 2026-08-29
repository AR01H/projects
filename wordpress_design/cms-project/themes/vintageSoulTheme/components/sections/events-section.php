<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;

$title      = (string) ( $title ?? 'EVENTS & LIVE CANE BAR CATERING' );
$sub        = (string) ( $sub ?? 'Bring the theatrical experience of freshly pressed sugarcane juice to your special occasion.' );

$event_types = array(
	array(
		'icon'     => '🎪',
		'title'    => 'WEDDINGS & RECEPTIONS',
		'tag'      => 'Popular Choice',
		'desc'     => 'A live sugarcane juice counter that charms guests with fresh, bespoke mocktails and traditional sugarcane theatre.',
		'features' => array( 'Customised rustic cane bar setup', 'Unlimited fresh pressings', 'Fresh mint, ginger & lime infusions' ),
	),
	array(
		'icon'     => '🏢',
		'title'    => 'CORPORATE & BRAND EVENTS',
		'tag'      => 'Wellness & Energy',
		'desc'     => 'Boost workplace wellness and celebrate milestones with a 100% natural, energising live juice station.',
		'features' => array( 'Fast professional service', 'Branded cups & bar signage', 'Zero artificial sugars or syrups' ),
	),
	array(
		'icon'     => '🎉',
		'title'    => 'BIRTHDAYS & PRIVATE PARTIES',
		'tag'      => 'All Ages Favourite',
		'desc'     => 'Fun, interactive live pressing that kids and adults adore. Sweet memories crafted right before their eyes.',
		'features' => array( 'Compact mobile setup', 'Kids friendly mocktails', 'Full hygiene certified team' ),
	),
	array(
		'icon'     => '🌾',
		'title'    => 'FESTIVALS & COMMUNITY MEETS',
		'tag'      => 'High Volume Capacity',
		'desc'     => 'High-throughput commercial extractors ready to serve hundreds of festival-goers with icy, refreshing cane juice.',
		'features' => array( 'High-speed twin rollers', 'Eco-friendly recyclable cups', 'Trained event crew' ),
	),
);

$event_gallery = array(
	array( 'image' => 'assets/images/sugarcane/hero_juice.jpg', 'title' => 'Live Pressing in Action', 'tag' => 'Wedding Stall' ),
	array( 'image' => 'assets/images/sugarcane/story_moments.jpg', 'title' => 'Guest Smiles & Cheers', 'tag' => 'Private Party' ),
	array( 'image' => 'assets/images/sugarcane/combo.jpg', 'title' => 'Craft Infusions Bar', 'tag' => 'Festival Stage' ),
	array( 'image' => 'assets/images/sugarcane/stacks.jpg', 'title' => 'Fresh Stalks Display', 'tag' => 'Corporate Gala' ),
);

$event_reviews = array(
	array(
		'quote'    => 'The Cane House live bar was the biggest hit at our wedding reception! Guests are still talking about the ginger-mint sugarcane juice.',
		'author'   => 'Priya & Rahul M.',
		'event'    => 'Wedding Reception, London',
		'rating'   => '★★★★★',
	),
	array(
		'quote'    => 'Super professional team, immaculate setup, and deliciously refreshing juice. Perfect addition to our summer corporate picnic.',
		'author'   => 'David K.',
		'event'    => 'Corporate Summer Gala',
		'rating'   => '★★★★★',
	),
	array(
		'quote'    => 'Brought back so many childhood memories. Freshly pressed right in front of us. Highly recommended for any event!',
		'author'   => 'Ananya S.',
		'event'    => '50th Birthday Celebration',
		'rating'   => '★★★★★',
	),
);
?>
<section class="section section--events events-vintage paper-rough" id="events">
	<div class="container events-vintage__container">
		
		<!-- 1. Header & Overview -->
		<div class="events-vintage__header">
			<h2 class="events-vintage__title"><?php echo esc_html( trim( strip_tags( $title ), " -—" ) ); ?></h2>
			<p class="events-vintage__sub"><?php echo esc_html( $sub ); ?></p>
		</div>

		<!-- 2. Event Types & Packages Grid -->
		<div class="events-types-grid">
			<?php foreach ( $event_types as $evt ) : ?>
				<div class="event-type-card card--rough-cut">
					<div class="event-type-card__head">
						<span class="event-type-card__icon"><?php echo esc_html( $evt['icon'] ); ?></span>
						<span class="event-type-card__tag"><?php echo esc_html( $evt['tag'] ); ?></span>
					</div>
					<h3 class="event-type-card__title"><?php echo esc_html( $evt['title'] ); ?></h3>
					<p class="event-type-card__desc"><?php echo esc_html( $evt['desc'] ); ?></p>
					<ul class="event-type-card__features">
						<?php foreach ( $evt['features'] as $feat ) : ?>
							<li>✓ <?php echo esc_html( $feat ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- 3. Event Photo Gallery Ribbon + Grid -->
		<div class="vintage-ribbon-tag vintage-ribbon-tag--gold">
			<span>LIVE EVENTS IN ACTION</span>
		</div>
		<div class="events-gallery-grid">
			<?php foreach ( $event_gallery as $g_item ) :
				$g_img = UrlHelper::resolve( $g_item['image'] );
			?>
				<div class="event-gallery-card frame--rough-cut">
					<div class="event-gallery-card__media">
						<img src="<?php echo esc_url( $g_img ); ?>" alt="<?php echo esc_attr( $g_item['title'] ); ?>" loading="lazy">
						<span class="event-gallery-card__tag"><?php echo esc_html( $g_item['tag'] ); ?></span>
					</div>
					<h4 class="event-gallery-card__title"><?php echo esc_html( $g_item['title'] ); ?></h4>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- 4. Client Reviews & Quick Event Booking CTA -->
		<div class="vintage-ribbon-tag">
			<span>CLIENT EXPERIENCES</span>
		</div>
		<div class="events-reviews-grid">
			<?php foreach ( $event_reviews as $rev ) : ?>
				<div class="event-review-card card--rough-cut">
					<div class="event-review-card__rating"><?php echo esc_html( $rev['rating'] ); ?></div>
					<p class="event-review-card__quote">“<?php echo esc_html( $rev['quote'] ); ?>”</p>
					<div class="event-review-card__meta">
						<strong><?php echo esc_html( $rev['author'] ); ?></strong>
						<span><?php echo esc_html( $rev['event'] ); ?></span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Quick Booking Banner -->
		<div class="events-booking-banner card--rough-cut-dark">
			<div class="events-booking-banner__content">
				<h3 class="events-booking-banner__title">READY TO BOOK OUR LIVE CANE BAR?</h3>
				<p class="events-booking-banner__text">Dates fill up fast for weekends & summer wedding seasons. Contact us today to check availability and get a tailored quote.</p>
			</div>
			<div class="events-booking-banner__actions">
				<a class="btn btn--primary-vintage" href="https://wa.me/447770461999" target="_blank" rel="noopener">
					<span>💬 CHAT ON WHATSAPP</span>
				</a>
				<a class="btn btn--outline-vintage" href="<?php echo esc_url( RouteService::url( 'contact' ) ); ?>">
					<span>ENQUIRE FOR EVENT</span>
				</a>
			</div>
		</div>

	</div>
</section>
