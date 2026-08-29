<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;

$title    = (string) ( $title ?? 'OUR JUICES' );
$subtitle = (string) ( $subtitle ?? 'Freshly pressed 100% natural sugarcane juice — served ice cold.' );

$products = array(
	array( 'name' => 'CLASSIC CANE JUICE', 'desc' => '100% Pure & Sweet', 'price' => '£ 4.50', 'image' => 'assets/images/sugarcane/drink_classic.jpg' ),
	array( 'name' => 'GINGER CANE JUICE', 'desc' => 'Spicy & Warming Kick', 'price' => '£ 5.00', 'image' => 'assets/images/sugarcane/drink_pineapple.jpg' ),
	array( 'name' => 'LEMON CANE JUICE', 'desc' => 'Zesty & Refreshing', 'price' => '£ 5.00', 'image' => 'assets/images/sugarcane/drink_lemon.jpg' ),
	array( 'name' => 'MINT CANE JUICE', 'desc' => 'Cooling Botanical Infusion', 'price' => '£ 5.00', 'image' => 'assets/images/sugarcane/drink_mint.jpg' ),
	array( 'name' => 'MASALA CANE JUICE', 'desc' => 'Traditional Chaat Spices', 'price' => '£ 5.00', 'image' => 'assets/images/sugarcane/drink_masala.jpg' ),
	array( 'name' => 'MIX FRUIT CANE JUICE', 'desc' => 'Seasonal Fruit Fusion', 'price' => '£ 5.50', 'image' => 'assets/images/sugarcane/drink_mixfruit.jpg' ),
);

$order_gallery = array(
	array( 'image' => 'assets/images/sugarcane/hero_juice.jpg', 'title' => 'Fresh Cold Extraction', 'tag' => 'Made to Order' ),
	array( 'image' => 'assets/images/sugarcane/combo.jpg', 'title' => 'Artisanal Flavor Blends', 'tag' => 'Botanical Mix' ),
	array( 'image' => 'assets/images/sugarcane/stacks.jpg', 'title' => 'Clean Farm Stalks', 'tag' => 'Raw Sourcing' ),
	array( 'image' => 'assets/images/sugarcane/story_moments.jpg', 'title' => 'Chilled Delivery Crates', 'tag' => 'Fast Dispatch' ),
);

$wholesale_options = array(
	array(
		'icon'  => 'pack',
		'title' => 'EVENT KEGS & DISPENSERS',
		'desc'  => '5L & 10L sealed stainless steel beverage kegs for catering, weddings, and private dinner parties.',
		'tag'   => 'Catering Favourite',
	),
	array(
		'icon'  => 'leaf',
		'title' => 'WHOLESALE RAW STALKS',
		'desc'  => 'Pre-washed, peeled, and graded premium sugarcane stalks for juice bars, restaurants, and grocers.',
		'tag'   => 'Commercial Supply',
	),
	array(
		'icon'  => 'delivery',
		'title' => 'WEEKLY HOME DELIVERY',
		'desc'  => 'Chilled 500ml & 1L glass bottles delivered fresh to your door in London every Friday & Saturday.',
		'tag'   => 'Subscription',
	),
);

$order_reviews = array(
	array(
		'quote'  => 'Ordered a 10L keg for our Eid family garden party. Everyone loved how icy, sweet and completely natural it tasted!',
		'author' => 'Zainab A.',
		'city'   => 'London Home Delivery',
		'rating' => '★★★★★',
	),
	array(
		'quote'  => 'We buy wholesale stalks weekly for our restaurant. Top tier sweetness and super clean preparation.',
		'author' => 'Marcus T.',
		'city'   => 'Soho Restaurant Partner',
		'rating' => '★★★★★',
	),
);
?>
<section class="section section--order-juice order-juice-vintage-block torn-dark-block grain-dark" id="order">
	<div class="container order-juice-vintage__container">
		
		<!-- 1. Header -->
		<div class="order-juice-vintage__header">
			<span class="vintage-ribbon-tag vintage-ribbon-tag--gold">
				<span>OUR MENU</span>
			</span>
			<h2 class="order-juice-vintage__title"><?php echo esc_html( trim( strip_tags( $title ), " -—" ) ); ?></h2>
			<p class="section-eyebrow">Pressed Fresh to Order</p>
			<p class="order-juice-vintage__sub"><?php echo esc_html( $subtitle ); ?></p>
		</div>

		<!-- 2. Product Range Grid -->
		<div class="order-juice-vintage__products-grid">
			<?php foreach ( $products as $prod ) :
				$name    = (string) ( $prod['name'] ?? '' );
				$desc    = (string) ( $prod['desc'] ?? '' );
				$price   = (string) ( $prod['price'] ?? '' );
				$img_raw = (string) ( $prod['image'] ?? 'assets/images/sugarcane/drink_classic.jpg' );
				$img     = UrlHelper::resolve( $img_raw );
			?>
				<div class="order-product-card card--rough-cut">
					<div class="order-product-card__media">
						<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
					</div>
					<div class="order-product-card__content">
						<h3 class="order-product-card__title"><?php echo esc_html( $name ); ?></h3>
						<p class="order-product-card__desc"><?php echo esc_html( $desc ); ?></p>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- 3. Fresh Preparation & Production Photo Gallery -->
		<div class="vintage-ribbon-tag vintage-ribbon-tag--gold">
			<span>FRESH EXTRACTION & BOTTLING</span>
		</div>
		<div class="order-gallery-grid">
			<?php foreach ( $order_gallery as $og ) :
				$og_img = UrlHelper::resolve( $og['image'] );
			?>
				<div class="order-gallery-card frame--rough-cut">
					<div class="order-gallery-card__media">
						<img src="<?php echo esc_url( $og_img ); ?>" alt="<?php echo esc_attr( $og['title'] ); ?>" loading="lazy">
						<span class="order-gallery-card__tag"><?php echo esc_html( $og['tag'] ); ?></span>
					</div>
					<h4 class="order-gallery-card__title"><?php echo esc_html( $og['title'] ); ?></h4>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- 4. Wholesale & Catering Supply Options -->
		<div class="vintage-ribbon-tag">
			<span>WHOLESALE & BULK SUPPLY</span>
		</div>
		<div class="wholesale-cards-grid">
			<?php foreach ( $wholesale_options as $ws ) :
				$ws_icon = IconHelper::get( $ws['icon'], '#f6d599', 28 );
			?>
				<div class="wholesale-card card--rough-cut-dark">
					<div class="wholesale-card__head">
						<span class="wholesale-card__icon"><?php echo $ws_icon; // phpcs:ignore ?></span>
						<span class="wholesale-card__tag"><?php echo esc_html( $ws['tag'] ); ?></span>
					</div>
					<h3 class="wholesale-card__title"><?php echo esc_html( $ws['title'] ); ?></h3>
					<p class="wholesale-card__desc"><?php echo esc_html( $ws['desc'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- 5. Customer Reviews -->
		<div class="vintage-ribbon-tag">
			<span>CUSTOMER FEEDBACK</span>
		</div>
		<div class="order-reviews-grid">
			<?php foreach ( $order_reviews as $rev ) : ?>
				<div class="order-review-card card--rough-cut-dark">
					<div class="order-review-card__rating"><?php echo esc_html( $rev['rating'] ); ?></div>
					<p class="order-review-card__quote">“<?php echo esc_html( $rev['quote'] ); ?>”</p>
					<div class="order-review-card__meta">
						<strong><?php echo esc_html( $rev['author'] ); ?></strong>
						<span><?php echo esc_html( $rev['city'] ); ?></span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- 6. Direct Order & Delivery Callout Banner -->
		<div class="order-delivery-banner card--rough-cut">
			<div class="order-delivery-banner__content">
				<h3 class="order-delivery-banner__title">NEED FRESH JUICE OR BULK SUPPLY?</h3>
				<p class="order-delivery-banner__text">We press fresh on demand and deliver across Sutton and Greater London. Message us on WhatsApp for fast orders and instant delivery confirmation.</p>
			</div>
			<div class="order-delivery-banner__actions">
				<a class="btn btn--primary-vintage" href="https://wa.me/447770461999" target="_blank" rel="noopener">
					<span>💬 ORDER ON WHATSAPP</span>
				</a>
				<a class="btn btn--outline-vintage" href="tel:+447770461999">
					<span>📞 CALL +44 7770 461 999</span>
				</a>
			</div>
		</div>

	</div>
</section>
