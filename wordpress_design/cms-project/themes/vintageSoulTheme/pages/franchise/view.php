<?php

use VintageSoul\Controllers\FranchiseController;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$data = ( new FranchiseController() )->prepare();

$hero    = (array) ( $data['hero'] ?? array() );
$why     = (array) ( $data['why'] ?? array() );
$how     = (array) ( $data['how'] ?? array() );
$closing = (array) ( $data['closing'] ?? array() );

$franchise_pillars = array(
	array(
		'icon'  => '🎪',
		'title' => 'TURNKEY MOBILE SETUP',
		'desc'  => 'Complete rustic wooden stall, heavy-duty commercial stainless steel extractor, and branded POS setup.',
	),
	array(
		'icon'  => '🌾',
		'title' => 'PREMIUM CANE SUPPLY',
		'desc'  => 'Guaranteed steady supply of freshly harvested, high-yield premium sugarcane stalks delivered to your hub.',
	),
	array(
		'icon'  => '🤝',
		'title' => 'TRAINING & CERTIFICATION',
		'desc'  => 'Hands-on operational training, health & hygiene compliance, recipe mastery, and customer service excellence.',
	),
	array(
		'icon'  => '📈',
		'title' => 'HIGH MARGINS & QUICK ROI',
		'desc'  => 'Low ingredient cost, high consumer demand, zero waste byproduct utilisation, and strong event catering margins.',
	),
);

$franchise_formats = array(
	array(
		'badge'        => 'LOWEST ENTRY',
		'name'         => 'Mobile Vintage Cart',
		'tagline'      => 'Markets, Weddings & Popups',
		'investment'   => '£15,000 – £22,000',
		'margin'       => '72% – 76%',
		'payback'      => '3 – 6 Months',
		'footprint'    => '1.5m × 1m (15 sq ft)',
		'royalty'      => '4% Monthly',
		'desc'         => 'Compact, high-mobility vintage wooden cart equipped with commercial silent cold-press. Perfect for weekend markets, street food festivals, and private catering.',
		'features'     => array(
			'Turnkey Handcrafted Vintage Wooden Cart',
			'Commercial Cold-Press Sugarcane Extractor Included',
			'Initial Batch of 500kg Premium Raw Stalks',
			'Full Food Safety & Hygiene HACCP Certification Kit',
			'Brand Signage, Uniforms & Glassware Supplies',
		),
	),
	array(
		'badge'        => 'MOST POPULAR',
		'name'         => 'Retail Mall Kiosk',
		'tagline'      => 'Shopping Centres & Transit Hubs',
		'investment'   => '£35,000 – £52,000',
		'margin'       => '70% – 74%',
		'payback'      => '6 – 10 Months',
		'footprint'    => '10m² – 18m² (100 – 180 sq ft)',
		'royalty'      => '4.5% Monthly',
		'desc'         => 'High-throughput semi-permanent island kiosk designed for continuous 7-day high footfall trading in shopping centres, train stations, and food halls.',
		'features'     => array(
			'Custom Modular Hardwood & Brass Kiosk Island',
			'Dual High-Output Commercial Pressing Units Included',
			'Under-Counter Commercial Refrigeration & Waste System',
			'Cloud POS Terminal & Digital Botanical Menu Screens',
			'Dedicated Weekly Refrigerated Cane Deliveries',
		),
	),
	array(
		'badge'        => 'FLAGSHIP CONCEPT',
		'name'         => 'Botanical Juice Bar',
		'tagline'      => 'High Street Destination Store',
		'investment'   => '£75,000 – £115,000',
		'margin'       => '68% – 73%',
		'payback'      => '12 – 16 Months',
		'footprint'    => '60m² – 120m² (600 – 1,200 sq ft)',
		'royalty'      => '5% Monthly',
		'desc'         => 'Full experiential retail cafe serving freshly pressed cane juices, artisanal mocktails, sugarcane desserts, and packaged bottled wellness tonics.',
		'features'     => array(
			'Complete Architectural Interior Fitout (20-40 Seats)',
			'Full Commercial Kitchen & Cold-Press Bar Theatre',
			'Expanded Mocktail, Pastry & Bottled Retail Lines',
			'Protected Postcode Territory & Local Marketing Campaign',
			'Comprehensive 4-Week Onsite Staff Training',
		),
	),
);

$franchise_gallery = array(
	array( 'image' => 'assets/images/sugarcane/story_moments.jpg', 'title' => 'Borough Market Stall', 'tag' => 'Flagship Stall' ),
	array( 'image' => 'assets/images/sugarcane/stacks.jpg', 'title' => 'Fresh Cane Supply Hub', 'tag' => 'Raw Materials' ),
	array( 'image' => 'assets/images/sugarcane/hero_juice.jpg', 'title' => 'Commercial Press in Action', 'tag' => 'Machinery' ),
	array( 'image' => 'assets/images/sugarcane/combo.jpg', 'title' => 'High-Volume Serving Counter', 'tag' => 'Operations' ),
);

$franchisee_reviews = array(
	array(
		'quote'  => 'Starting a Cane House franchise was the best decision. The training and cane supply chain are seamless, and customers love the fresh taste.',
		'author' => 'Ramesh B.',
		'city'   => 'Borough Market Partner',
		'rating' => '★★★★★',
	),
	array(
		'quote'  => 'High profit margins and the live juice theatre draws crowds every single weekend. We broke even in just four months.',
		'author' => 'Priya & Suresh K.',
		'city'   => 'South London Franchisee',
		'rating' => '★★★★★',
	),
	array(
		'quote'  => 'The brand recognition and operational support gave us the confidence to launch our second mall kiosk within one year.',
		'author' => 'Tariq A.',
		'city'   => 'Birmingham Metro Partner',
		'rating' => '★★★★★',
	),
);

$franchise_faqs = array(
	array(
		'question' => 'What is the initial investment required?',
		'answer'   => 'Investment packages are custom-tailored to your desired format (Mobile Vintage Cart, Mall Kiosk, or Flagship Storefront), including commercial cold-press equipment, starter inventory, and complete training.',
	),
	array(
		'question' => 'How does the sugarcane stalk supply chain work?',
		'answer'   => 'We manage centralized agricultural imports and dispatch fresh, temperature-controlled, mature organic sugarcane stalks directly to your local operating hub on a weekly schedule.',
	),
	array(
		'question' => 'Do I need prior food & beverage experience?',
		'answer'   => 'No prior hospitality experience is necessary. Our intensive 2-week training program covers machine operation, hygiene standards, recipe mastery, inventory control, and customer service.',
	),
	array(
		'question' => 'Are exclusive territory rights available?',
		'answer'   => 'Yes, franchise partners receive protected geographic territory rights based on agreed postcode clusters and population demographics.',
	),
);
?>

<!-- ═══════════ 1. COMMON LUXURY VINTAGE SUBPAGE HERO HEADER ═══════════ -->
<?php
View::component(
	'subpage-hero/subpage-hero',
	array(
		'id'    => 'franchise-hero',
		'tag'   => (string) ( $hero['tag'] ?? 'A Growing Opportunity • UK & Beyond' ),
		'title' => 'BRING THE TRADITION <em>To Your City</em>',
		'sub'   => (string) ( $hero['sub'] ?? 'Join our growing family of partners and bring the authentic raw cold-press sugarcane experience to your community.' ),
		'image' => 'assets/images/backgrounds/pure_sugarcane_forest_trees_engraving.jpg',
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
				'tag'   => 'Partner Benefits',
				'title' => 'WHY PARTNER WITH <em>The Cane House</em>',
				'sub'   => 'Join a proven botanical beverage business model with high consumer appeal and comprehensive operational backing.',
			)
		);
		?>

		<div class="events-types-grid" style="margin-bottom: 20px;">
			<?php foreach ( $franchise_pillars as $pillar ) : ?>
				<div class="event-type-card card--rough-cut">
					<div class="event-type-card__head">
						<span class="event-type-card__icon"><?php echo esc_html( $pillar['icon'] ); ?></span>
					</div>
					<h3 class="event-type-card__title"><?php echo esc_html( $pillar['title'] ); ?></h3>
					<p class="event-type-card__desc"><?php echo esc_html( $pillar['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ═══════════ 3. FRANCHISE FORMATS & INVESTMENT OPTIONS ═══════════ -->
<section class="section" id="formats">
	<div class="container">
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'   => 'Flexible Business Models',
				'title' => 'CHOOSE YOUR <em>Franchise Format</em>',
				'sub'   => 'From mobile festival carts to turnkey retail bars, select the model that best matches your investment goals and target location.',
			)
		);
		?>

		<div class="vintage-carousel-wrapper">
			<button class="vintage-carousel-ctrl vintage-carousel-ctrl--prev" type="button" aria-label="Previous Format" onclick="document.getElementById('formats-carousel-track').scrollBy({left: -350, behavior: 'smooth'})">‹</button>
			<div class="vintage-card-carousel" id="formats-carousel-track">
				<?php foreach ( $franchise_formats as $fmt ) : ?>
					<div class="vintage-carousel-card frame--rough-cut" style="flex:0 0 350px; max-width:350px; padding:24px 20px; display:flex; flex-direction:column;">
						<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
							<span class="lifecycle-carousel-card__badge" style="position:static; font-size:10px;"><?php echo esc_html( $fmt['badge'] ); ?></span>
							<span style="font-family:'Cinzel',serif; font-size:11px; font-weight:700; color:#8e5222; letter-spacing:0.06em;"><?php echo esc_html( $fmt['footprint'] ); ?></span>
						</div>
						
						<h4 class="goodness-carousel-card__title" style="font-size:18px; margin-bottom:4px;"><?php echo esc_html( $fmt['name'] ); ?></h4>
						<p style="font-family:'Dancing Script',cursive; font-size:15px; color:#8e5222; margin:0 0 12px;"><?php echo esc_html( $fmt['tagline'] ); ?></p>
						
						<!-- ═══════════ FINANCIAL & INVESTMENT METRICS ═══════════ -->
						<div style="background:rgba(23,43,21,0.06); border:1.5px dashed #8e622d; border-radius:6px; padding:12px 14px; margin-bottom:14px;">
							<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; border-bottom:1px solid rgba(142,98,45,0.2); padding-bottom:6px;">
								<span style="font-family:'Cinzel',serif; font-size:11px; font-weight:700; color:#11381b; letter-spacing:0.08em; text-transform:uppercase;">OPERATIONAL METRICS</span>
								<span style="font-family:'Cinzel',serif; font-size:10.5px; font-weight:700; color:#8e5222; text-transform:uppercase;">CUSTOM TAILORED</span>
							</div>
							<div style="display:grid; grid-template-columns:1fr 1fr; gap:8px 12px;">
								<div>
									<span style="font-size:10px; font-family:'Cinzel',serif; color:#8e5222; display:block; text-transform:uppercase;">Gross Margin</span>
									<strong style="font-size:13px; font-family:'Cinzel',serif; color:#172b15;"><?php echo esc_html( $fmt['margin'] ); ?></strong>
								</div>
								<div>
									<span style="font-size:10px; font-family:'Cinzel',serif; color:#8e5222; display:block; text-transform:uppercase;">Est. Payback</span>
									<strong style="font-size:13px; font-family:'Cinzel',serif; color:#172b15;"><?php echo esc_html( $fmt['payback'] ); ?></strong>
								</div>
								<div>
									<span style="font-size:10px; font-family:'Cinzel',serif; color:#8e5222; display:block; text-transform:uppercase;">Space Required</span>
									<strong style="font-size:12px; font-family:'Cinzel',serif; color:#172b15;"><?php echo esc_html( $fmt['footprint'] ); ?></strong>
								</div>
								<div>
									<span style="font-size:10px; font-family:'Cinzel',serif; color:#8e5222; display:block; text-transform:uppercase;">Ongoing Royalty</span>
									<strong style="font-size:12px; font-family:'Cinzel',serif; color:#172b15;"><?php echo esc_html( $fmt['royalty'] ); ?></strong>
								</div>
							</div>
						</div>

						<p style="font-size:13.5px; line-height:1.45; color:#5d3d1a; margin:0 0 14px;"><?php echo esc_html( $fmt['desc'] ); ?></p>
						
						<div style="font-family:'Cinzel',serif; font-size:11px; font-weight:700; color:#11381b; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.06em;">Package Includes:</div>
						<ul style="list-style:none; padding:0; margin:0 0 18px; display:flex; flex-direction:column; gap:7px; flex-grow:1;">
							<?php foreach ( $fmt['features'] as $feat ) : ?>
								<li style="font-size:12.5px; line-height:1.35; color:#3a2814; display:flex; align-items:flex-start; gap:6px;">
									<span style="color:#11381b; font-weight:700; font-size:13px;">✓</span>
									<span><?php echo esc_html( $feat ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>

						<a href="#franchise-enquiry" class="btn btn--order-now" style="width:100%; text-align:center; justify-content:center; margin-top:auto;">
							ENQUIRE FOR THIS FORMAT ✦
						</a>
					</div>
				<?php endforeach; ?>
			</div>
			<button class="vintage-carousel-ctrl vintage-carousel-ctrl--next" type="button" aria-label="Next Format" onclick="document.getElementById('formats-carousel-track').scrollBy({left: 350, behavior: 'smooth'})">›</button>
		</div>
	</div>
</section>

<!-- ═══════════ 4. FRANCHISE MOVING CARD STREAMS ═══════════ -->
<section class="section section--franchise franchise-vintage-block torn-dark-block grain-dark" style="padding-top: 24px; padding-bottom: 30px;">
	<div class="container franchise-vintage__container">
		
		<div class="vintage-ribbon-tag vintage-ribbon-tag--gold">
			<span>OUR OUTLETS & MACHINERY</span>
		</div>
		<?php
		View::component( 'card-stream/card-stream', array(
			'items'      => $franchise_gallery,
			'card_type'  => 'gallery',
			'direction'  => 'ltr',
			'aria_label' => 'Franchise outlets and machinery stream',
		) );
		?>

		<div class="vintage-ribbon-tag">
			<span>PARTNER EXPERIENCES</span>
		</div>
		<?php
		View::component( 'card-stream/card-stream', array(
			'items'      => $franchisee_reviews,
			'card_type'  => 'dark-review',
			'direction'  => 'rtl',
			'aria_label' => 'Partner experiences stream',
		) );
		?>
	</div>
</section>

<!-- Deckled Border Divider -->
<div class="deckled-divider" aria-hidden="true">
	<img src="<?php echo esc_url( \VintageSoul\Support\UrlHelper::resolve( 'assets/images/textures/border/deckled-edge.svg' ) ); ?>" alt="" loading="lazy">
</div>

<!-- ═══════════ 5. HOW IT WORKS (Step Chain) ═══════════ -->
<?php if ( ! empty( $how['items'] ) ) : ?>
	<section class="section" id="franchise-steps">
		<div class="container">
			<?php
			View::component(
				'section-header/section-header',
				array(
					'tag'   => (string) ( $how['tag'] ?? 'The Process' ),
					'title' => (string) ( $how['title'] ?? 'How It Works' ),
					'sub'   => 'A step-by-step roadmap from submitting your interest to pouring your first glass on grand opening day.',
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

<!-- ═══════════ 6. FEATURED TRUST STRIP ═══════════ -->
<?php View::component( 'sections/logo-strip-section' ); ?>

<!-- ═══════════ 7. DIRECT FRANCHISE APPLICATION & ENQUIRY ═══════════ -->
<div id="franchise-enquiry">
	<?php View::component( 'sections/contact-form-section' ); ?>
</div>

<!-- ═══════════ 8. FRANCHISE FAQS ═══════════ -->
<?php
View::component(
	'faq/faq',
	array(
		'tag'     => 'Partnership Q&A',
		'heading' => 'Franchise <em>FAQs</em>',
		'items'   => $franchise_faqs,
		'id'      => 'franchise-faqs',
	)
);
?>

<!-- ═══════════ 9. TRUST RIBBON BOTTOM ═══════════ -->
<?php View::component( 'sections/trust-ribbon-section' ); ?>

