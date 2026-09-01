<?php

use VintageSoul\Controllers\EventsController;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$data = ( new EventsController() )->prepare();

$hero     = (array) ( $data['hero'] ?? array() );
$packages = (array) ( $data['packages'] ?? array() );
$process  = (array) ( $data['process'] ?? array() );
$gallery  = (array) ( $data['gallery'] ?? array() );
$faqs     = (array) ( $data['faqs'] ?? array() );

$event_gallery = ! empty( $gallery['items'] ) && is_array( $gallery['items'] ) ? $gallery['items'] : array(
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
	array(
		'quote'    => 'The live juicing theatre created an incredible buzz at our tech summit. Healthy, energising, and delicious.',
		'author'   => 'Marcus V.',
		'event'    => 'TechNova Annual Summit',
		'rating'   => '★★★★★',
	),
);

$event_inclusions = array(
	array( 'icon' => '🎪', 'title' => 'Rustic Wooden Bar Setup', 'desc' => 'Beautiful bespoke vintage mobile bar with brass accents and cane stalk displays.' ),
	array( 'icon' => '⚡', 'title' => 'Commercial Cold-Press', 'desc' => 'High-throughput silent extraction unit delivering ice-cold juice in under 30 seconds.' ),
	array( 'icon' => '👨‍🍳', 'title' => 'Certified Master Juicers', 'desc' => 'Uniformed, hygiene-certified artisans who press and serve with flair and warmth.' ),
	array( 'icon' => '🍋', 'title' => 'Botanical Flavor Menu', 'desc' => 'Fresh ginger, crushed garden mint, Sicilian lemon, spiced black salt, and mocktail blends.' ),
	array( 'icon' => '♻️', 'title' => 'Eco-Conscious Service', 'desc' => '100% plant-based compostable cups, straws, and napkins with zero landfill waste.' ),
	array( 'icon' => '🌾', 'title' => 'Farm Harvest Allocation', 'desc' => 'Premium, sweet sugarcane harvested exclusively for your date to ensure peak freshness.' ),
);
?>

<!-- ═══════════ 1. COMMON LUXURY VINTAGE SUBPAGE HERO HEADER ═══════════ -->
<?php
View::component(
	'subpage-hero/subpage-hero',
	array(
		'id'    => 'events-hero',
		'tag'   => (string) ( $hero['tag'] ?? 'Live Stalls • Weddings • Private Galas' ),
		'title' => 'BESPOKE <em>Bar Catering</em>',
		'sub'   => (string) ( $hero['sub'] ?? 'Transform your special occasion with our live vintage cold-press sugarcane bar.' ),
		'image' => (string) ( $hero['image'] ?? 'assets/images/backgrounds/vintage_coldpress_bar_catering.jpg' ),
	)
);
?>

<!-- ═══════════ 2. WHAT IS INCLUDED WITH EVERY BOOKING ═══════════ -->
<section class="section" id="inclusions">
	<div class="container">
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'   => 'The Cane House Standard',
				'title' => 'WHAT IS INCLUDED WITH <em>Every Live Bar</em>',
				'sub'   => 'Every event package is fully managed from setup to clean down, ensuring a stress-free experience for hosts.',
			)
		);
		?>

		<div class="events-types-grid" style="margin-bottom: 20px;">
			<?php foreach ( $event_inclusions as $inc ) : ?>
				<div class="event-type-card card--rough-cut">
					<div class="event-type-card__head">
						<span class="event-type-card__icon"><?php echo esc_html( $inc['icon'] ); ?></span>
					</div>
					<h3 class="event-type-card__title"><?php echo esc_html( $inc['title'] ); ?></h3>
					<p class="event-type-card__desc"><?php echo esc_html( $inc['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ═══════════ 3. LIVE EVENT MOVING CARD STREAMS ═══════════ -->
<section class="section section--events events-vintage paper-rough" style="padding-top: 10px; padding-bottom: 20px;">
	<div class="container events-vintage__container">
		<div class="vintage-ribbon-tag vintage-ribbon-tag--gold">
			<span>LIVE EVENTS IN ACTION</span>
		</div>
		<?php
		View::component( 'card-stream/card-stream', array(
			'items'      => $event_gallery,
			'card_type'  => 'gallery',
			'direction'  => 'ltr',
			'aria_label' => 'Live events gallery stream',
		) );
		?>

		<div class="vintage-ribbon-tag">
			<span>CLIENT EXPERIENCES</span>
		</div>
		<?php
		View::component( 'card-stream/card-stream', array(
			'items'      => $event_reviews,
			'card_type'  => 'review',
			'direction'  => 'rtl',
			'aria_label' => 'Client experiences stream',
		) );
		?>
	</div>
</section>

<!-- ═══════════ 4. ARTISANAL CATERING PACKAGES CAROUSEL ═══════════ -->
<?php if ( ! empty( $packages['items'] ) ) : ?>
	<section class="section" id="packages">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $packages['tag'] ?? 'Artisanal Event Packages' ),
					'title' => (string) ( $packages['title'] ?? 'Catering Packages' ),
				)
			);
			?>
			<p class="about-intro__sub" style="text-align:center; max-width:760px; margin:-10px auto 26px;">
				<?php echo esc_html( (string) ( $packages['sub'] ?? 'From intimate garden celebrations to grand multi-day cultural weddings and corporate wellness popups.' ) ); ?>
			</p>

			<div class="vintage-carousel-wrapper">
				<button class="vintage-carousel-ctrl vintage-carousel-ctrl--prev" type="button" aria-label="Previous Package" onclick="document.getElementById('packages-carousel-track').scrollBy({left: -320, behavior: 'smooth'})">‹</button>
				<div class="vintage-card-carousel" id="packages-carousel-track">
					<?php foreach ( (array) $packages['items'] as $pkg ) :
						$pkg_name  = (string) ( $pkg['name'] ?? '' );
						$pkg_tag   = (string) ( $pkg['tagline'] ?? '' );
						$pkg_guest = (string) ( $pkg['guests'] ?? '' );
						$pkg_badge = (string) ( $pkg['badge'] ?? '' );
						$pkg_feats = (array) ( $pkg['features'] ?? array() );
					?>
						<div class="vintage-carousel-card frame--rough-cut" style="flex:0 0 320px; max-width:320px; padding:22px 18px;">
							<div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
								<span style="font-family:'Cinzel',serif; font-size:10px; font-weight:700; color:#8e5f2b; letter-spacing:0.1em; text-transform:uppercase;">👥 <?php echo esc_html( $pkg_guest ); ?></span>
								<?php if ( $pkg_badge ) : ?>
									<span class="lifecycle-carousel-card__badge" style="position:static;"><?php echo esc_html( $pkg_badge ); ?></span>
								<?php endif; ?>
							</div>
							<h4 class="goodness-carousel-card__title" style="font-size:17px; margin-bottom:4px;"><?php echo esc_html( $pkg_name ); ?></h4>
							<p style="font-size:13px; font-style:italic; color:#8e5222; margin:0 0 14px;"><?php echo esc_html( $pkg_tag ); ?></p>
							
							<ul style="list-style:none; padding:0; margin:0 0 18px; display:flex; flex-direction:column; gap:8px;">
								<?php foreach ( $pkg_feats as $feat ) : ?>
									<li style="font-size:13.5px; line-height:1.4; color:#3a2814; display:flex; align-items:flex-start; gap:6px;">
										<span style="color:#11381b; font-weight:700;">✓</span>
										<span><?php echo esc_html( $feat ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>

							<a href="#event-booking" class="btn btn--order-now" style="width:100%; text-align:center; justify-content:center; margin-top:auto;">
								BOOK THIS PACKAGE
							</a>
						</div>
					<?php endforeach; ?>
				</div>
				<button class="vintage-carousel-ctrl vintage-carousel-ctrl--next" type="button" aria-label="Next Package" onclick="document.getElementById('packages-carousel-track').scrollBy({left: 320, behavior: 'smooth'})">›</button>
			</div>
		</div>
	</section>
<?php endif; ?>

<!-- Deckled Border Divider -->
<div class="deckled-divider" aria-hidden="true">
	<img src="<?php echo esc_url( \VintageSoul\Support\UrlHelper::resolve( 'assets/images/textures/border/deckled-edge.svg' ) ); ?>" alt="" loading="lazy">
</div>

<!-- ═══════════ 5. HOW BOOKING WORKS (Step Chain) ═══════════ -->
<?php if ( ! empty( $process['items'] ) ) : ?>
	<section class="section section--alt" id="booking-process">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $process['tag'] ?? 'Seamless Planning' ),
					'title' => (string) ( $process['title'] ?? 'How Booking Works' ),
					'sub'   => 'A frictionless 4-step concierge service from your initial enquiry to the live pour on event day.',
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

<!-- ═══════════ 6. FEATURED TRUST STRIP ═══════════ -->
<?php View::component( 'sections/logo-strip-section' ); ?>

<!-- ═══════════ 7. DIRECT EVENT CONCIERGE BOOKING ═══════════ -->
<div id="event-booking">
	<?php View::component( 'sections/contact-form-section' ); ?>
</div>

<!-- ═══════════ 8. EVENT FAQS ═══════════ -->
<?php if ( ! empty( $faqs['items'] ) ) : ?>
	<?php
	View::component(
		'faq/faq',
		array(
			'tag'     => 'Booking & Service Answers',
			'heading' => (string) ( $faqs['title'] ?? 'Frequently Asked <em>Questions</em>' ),
			'items'   => (array) ( $faqs['items'] ?? array() ),
			'id'      => 'event-faqs',
		)
	);
	?>
<?php endif; ?>

<!-- ═══════════ 9. TRUST RIBBON BOTTOM ═══════════ -->
<?php View::component( 'sections/trust-ribbon-section' ); ?>

