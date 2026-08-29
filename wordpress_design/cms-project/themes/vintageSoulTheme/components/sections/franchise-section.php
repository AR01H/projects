<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;

$title    = (string) ( $title ?? 'FRANCHISE OPPORTUNITIES' );
$sub      = (string) ( $sub ?? 'Own a profitable, turnkey sugarcane juice business with full brand support.' );

$franchise_pillars = array(
	array(
		'icon'  => 'stall',
		'title' => 'TURNKEY MOBILE COUNTER',
		'desc'  => 'Complete rustic wooden stall, heavy-duty commercial stainless steel extractor, and branded POS setup.',
	),
	array(
		'icon'  => 'plant',
		'title' => 'PREMIUM CANE SUPPLY',
		'desc'  => 'Guaranteed steady supply of freshly harvested, high-yield premium sugarcane stalks delivered to your hub.',
	),
	array(
		'icon'  => 'handshake',
		'title' => 'TRAINING & CERTIFICATION',
		'desc'  => 'Hands-on operational training, health & hygiene compliance, recipe mastery, and customer service excellence.',
	),
	array(
		'icon'  => 'growth',
		'title' => 'HIGH MARGINS & QUICK ROI',
		'desc'  => 'Low ingredient cost, high consumer demand, zero waste byproduct utilisation, and strong event catering margins.',
	),
);

$franchise_gallery = array(
	array( 'image' => 'assets/images/sugarcane/story_moments.jpg', 'title' => 'Borough Market Stall', 'tag' => 'Flagship Stall' ),
	array( 'image' => 'assets/images/sugarcane/stacks.jpg', 'title' => 'Fresh Cane Supply Hub', 'tag' => 'Raw Materials' ),
	array( 'image' => 'assets/images/sugarcane/hero_juice.jpg', 'title' => 'Commercial Press in Action', 'tag' => 'Machinery' ),
	array( 'image' => 'assets/images/sugarcane/combo.jpg', 'title' => 'High-Volume Serving Counter', 'tag' => 'Operations' ),
);

$franchise_steps = array(
	array( 'num' => '01', 'title' => 'EXPRESS INTEREST', 'desc' => 'Submit your application and schedule an initial discovery call.' ),
	array( 'num' => '02', 'title' => 'LOCATION & PLAN', 'desc' => 'Review territories, pitch locations (markets, malls, mobile event vans).' ),
	array( 'num' => '03', 'title' => 'SETUP & TRAINING', 'desc' => 'Receive your branded machinery, stock, and complete training.' ),
	array( 'num' => '04', 'title' => 'GRAND LAUNCH', 'desc' => 'Open your stall with our marketing support and start pouring fresh cane juice.' ),
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
);
?>
<section class="section section--franchise franchise-vintage-block torn-dark-block grain-dark" id="franchise">
	<div class="container franchise-vintage__container">
		
		<!-- 1. Header -->
		<div class="franchise-vintage__header">
			<h2 class="franchise-vintage__title"><?php echo esc_html( trim( strip_tags( $title ), " -—" ) ); ?></h2>
			<p class="franchise-vintage__sub"><?php echo esc_html( $sub ); ?></p>
		</div>

		<!-- 2. What We Provide / 4 Pillars Grid -->
		<div class="franchise-pillars-grid">
			<?php foreach ( $franchise_pillars as $pillar ) :
				$icon_svg = IconHelper::get( $pillar['icon'], '#f6d599', 30 );
			?>
				<div class="franchise-pillar-card card--rough-cut-dark">
					<span class="franchise-pillar-card__icon"><?php echo $icon_svg; // phpcs:ignore ?></span>
					<h3 class="franchise-pillar-card__title"><?php echo esc_html( $pillar['title'] ); ?></h3>
					<p class="franchise-pillar-card__desc"><?php echo esc_html( $pillar['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- 3. Franchise Operations & Stalls Photo Gallery -->
		<div class="vintage-ribbon-tag vintage-ribbon-tag--gold">
			<span>OUR OUTLETS & MACHINERY</span>
		</div>
		<div class="franchise-gallery-grid">
			<?php foreach ( $franchise_gallery as $fg ) :
				$fg_img = UrlHelper::resolve( $fg['image'] );
			?>
				<div class="franchise-gallery-card frame--rough-cut">
					<div class="franchise-gallery-card__media">
						<img src="<?php echo esc_url( $fg_img ); ?>" alt="<?php echo esc_attr( $fg['title'] ); ?>" loading="lazy">
						<span class="franchise-gallery-card__tag"><?php echo esc_html( $fg['tag'] ); ?></span>
					</div>
					<h4 class="franchise-gallery-card__title"><?php echo esc_html( $fg['title'] ); ?></h4>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- 4. Step-by-Step Launch Timeline -->
		<div class="vintage-ribbon-tag vintage-ribbon-tag--gold">
			<span>HOW TO GET STARTED</span>
		</div>
		<div class="franchise-steps-grid">
			<?php foreach ( $franchise_steps as $st ) : ?>
				<div class="franchise-step-card card--rough-cut-dark">
					<span class="franchise-step-card__badge"><?php echo esc_html( $st['num'] ); ?></span>
					<h4 class="franchise-step-card__title"><?php echo esc_html( $st['title'] ); ?></h4>
					<p class="franchise-step-card__desc"><?php echo esc_html( $st['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- 5. Franchisee Success Stories -->
		<div class="vintage-ribbon-tag">
			<span>PARTNER EXPERIENCES</span>
		</div>
		<div class="franchise-reviews-grid">
			<?php foreach ( $franchisee_reviews as $rev ) : ?>
				<div class="franchise-review-card card--rough-cut-dark">
					<div class="franchise-review-card__rating"><?php echo esc_html( $rev['rating'] ); ?></div>
					<p class="franchise-review-card__quote">“<?php echo esc_html( $rev['quote'] ); ?>”</p>
					<div class="franchise-review-card__meta">
						<strong><?php echo esc_html( $rev['author'] ); ?></strong>
						<span><?php echo esc_html( $rev['city'] ); ?></span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- 6. Franchise Call to Action Box -->
		<div class="franchise-cta-box card--rough-cut">
			<div class="franchise-cta-box__content">
				<h3 class="franchise-cta-box__title">REQUEST YOUR FRANCHISE PROSPECTUS</h3>
				<p class="franchise-cta-box__text">Join the UK's fastest-growing fresh sugarcane juice movement. Limited territorial rights available for 2025.</p>
			</div>
			<div class="franchise-cta-box__actions">
				<a class="btn btn--primary-vintage" href="<?php echo esc_url( RouteService::url( 'contact' ) ); ?>">
					<span>APPLY FOR FRANCHISE</span>
				</a>
				<a class="btn btn--outline-vintage" href="tel:+447770461999">
					<span>📞 CALL FRANCHISE TEAM</span>
				</a>
			</div>
		</div>

	</div>
</section>
