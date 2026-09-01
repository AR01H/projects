<?php

use VintageSoul\Controllers\AboutController;
use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

defined( 'ABSPATH' ) || exit;

$data = ( new AboutController() )->prepare();

$hero   = (array) ( $data['hero'] ?? array() );
$intro  = (array) ( $data['intro'] ?? array() );
$values = (array) ( $data['values'] ?? array() );
$story  = (array) ( $data['story'] ?? array() );

// Load additional data
$team       = (array) JsonFileProvider::read( 'data/content/team.json' );
$milestones = (array) JsonFileProvider::read( 'data/content/milestones.json' );
$gallery    = (array) JsonFileProvider::read( 'data/content/gallery.json' );
$logo_strip = (array) JsonFileProvider::read( 'data/content/logo-strip.json' );

$story_pillars = (array) ( $story['pillars'] ?? array(
	array( 'icon' => 'plant', 'label' => 'TRADITION', 'note' => 'Handcrafted method passed down' ),
	array( 'icon' => 'leaf', 'label' => 'QUALITY', 'note' => 'Freshly harvested cane' ),
	array( 'icon' => 'handshake', 'label' => 'COMMUNITY', 'note' => 'Bringing people together' ),
	array( 'icon' => 'health', 'label' => 'AUTHENTIC', 'note' => 'Zero artificial additives' ),
) );

$team_members = (array) ( $team['items'] ?? array() );
$milestone_items = (array) ( $milestones['items'] ?? array() );
$gallery_items = (array) ( $gallery['items'] ?? array() );
$logo_items = (array) ( $logo_strip['items'] ?? array() );
?>

<div class="about-page">
	<?php View::component( 'background/parchment-botanical-bg', array( 'seed' => 23 ) ); ?>
	<!-- ═══════════ 1. MASTER VINTAGE HERO ═══════════ -->
	<?php
	View::component(
		'subpage-hero/subpage-hero',
		array(
			'id'    => 'about-hero',
			'tag'   => 'The Cane House Story',
			'title' => 'ABOUT <em>The Cane House</em>',
			'sub'   => 'We are on a mission to bring the authentic, traditional sugarcane juice experience back to modern communities — freshly pressed, 100% natural, and full of memories.',
			'image' => 'assets/images/backgrounds/pure_sugarcane_forest_trees_engraving.jpg',
		)
	);
	?>

	<!-- ═══════════ 2. INTRO: MORE THAN JUST A CROP ═══════════ -->
	<section class="section about-intro-section">
		<div class="container container--narrow about-intro__container">
			<h2 class="about-section-title">MORE THAN JUST A <em>Crop</em></h2>
			<p class="about-intro__sub">A Tradition That Brings People Together</p>
			<p class="about-intro__text">What starts as a simple, humble plant becomes something people gather around, celebrate with, and remember for years to come. Every stalk is carefully selected, freshly cold-pressed before your eyes, and served pure with love — just as it has been done for generations.</p>
			<div class="about-intro__media frame--ornate">
				<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/sugarcane/story_moments.jpg' ) ); ?>" alt="The Cane House Heritage" loading="lazy">
			</div>
		</div>
	</section>

	<!-- Gold Wave Divider -->
	<div class="gold-wave-divider" aria-hidden="true">
		<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/textures/border/gold-wave.svg' ) ); ?>" alt="" loading="lazy">
	</div>

	<!-- ═══════════ 3. OUR SERVICES ═══════════ -->
	<section class="section about-services-section">
		<div class="container">
			<h2 class="about-section-title">WHAT WE <em>Do & Serve</em></h2>
			<p class="about-intro__sub" style="text-align:center; margin-bottom:24px;">Our Core Services</p>

			<div class="about-services-grid">
				<div class="service-card">
					<div class="service-card__icon-wrap">
						<?php echo IconHelper::get( 'stall', '#f3d49d', 28 ); // phpcs:ignore ?>
					</div>
					<h3 class="service-card__title">LIVE SUGARCANE BAR</h3>
					<p class="service-card__desc">Our signature live cold-pressing setup for weddings, birthdays, corporate events, and festivals. Fresh juice served before your guests' eyes.</p>
					<a class="btn btn--primary-vintage btn--sm" href="<?php echo esc_url( RouteService::url( 'contact' ) ); ?>">BOOK NOW</a>
				</div>

				<div class="service-card">
					<div class="service-card__icon-wrap">
						<?php echo IconHelper::get( 'leaf', '#f3d49d', 28 ); // phpcs:ignore ?>
					</div>
					<h3 class="service-card__title">FRESH JUICE STALL</h3>
					<p class="service-card__desc">Visit our permanent stall in Sutton for freshly pressed classic, lemon ginger, mint, masala, pineapple, and mix-fruit sugarcane juice.</p>
					<a class="btn btn--primary-vintage btn--sm" href="/#our-drinks">VIEW MENU</a>
				</div>

				<div class="service-card">
					<div class="service-card__icon-wrap">
						<?php echo IconHelper::get( 'handshake', '#f3d49d', 28 ); // phpcs:ignore ?>
					</div>
					<h3 class="service-card__title">FRANCHISE PARTNERSHIP</h3>
					<p class="service-card__desc">Join The Cane House family. We offer end-to-end franchise support including training, branding, equipment, and ongoing mentorship.</p>
					<a class="btn btn--primary-vintage btn--sm" href="/#franchise">LEARN MORE</a>
				</div>

				<div class="service-card">
					<div class="service-card__icon-wrap">
						<?php echo IconHelper::get( 'growth', '#f3d49d', 28 ); // phpcs:ignore ?>
					</div>
					<h3 class="service-card__title">WHOLESALE SUPPLY</h3>
					<p class="service-card__desc">Bulk fresh sugarcane juice supply for restaurants, cafes, and health food stores. Consistent quality, reliable delivery.</p>
					<a class="btn btn--primary-vintage btn--sm" href="<?php echo esc_url( RouteService::url( 'contact' ) ); ?>">ENQUIRE</a>
				</div>
			</div>
		</div>
	</section>

	<!-- Deckled Edge Divider -->
	<div class="deckled-divider" aria-hidden="true">
		<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/textures/border/deckled-edge.svg' ) ); ?>" alt="" loading="lazy">
	</div>

	<!-- ═══════════ 4. FOUR PILLARS (Dark Botanical) ═══════════ -->
	<section class="section section--dark-botanical about-pillars-section">
		<div class="container">
			<div class="section-header">
				<h2 class="section-header__title">FOUR PILLARS OF OUR <em>Craft</em></h2>
				<p class="section-header__tag">Integrity in Every Drop</p>
			</div>

			<div class="history-grid-4">
				<?php foreach ( $story_pillars as $p ) :
					$p_icon = (string) ( $p['icon'] ?? 'plant' );
					$p_label = (string) ( $p['label'] ?? '' );
					$p_note = (string) ( $p['note'] ?? '' );
					$p_svg = IconHelper::get( $p_icon, '#172b15', 24 );
					if ( empty( $p_svg ) ) {
						$p_svg = IconHelper::get( 'plant', '#172b15', 24 );
					}
				?>
					<div class="pillar-card frame--ornate-sm">
						<div class="pillar-card__icon"><?php echo $p_svg; // phpcs:ignore ?></div>
						<h3 class="pillar-card__label"><?php echo esc_html( $p_label ); ?></h3>
						<p class="pillar-card__note"><?php echo esc_html( $p_note ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- ═══════════ 5. OUR CORE VALUES ═══════════ -->
	<section class="section about-values-section">
		<div class="container">
			<h2 class="about-section-title">OUR CORE <em>Values & Heritage</em></h2>
			<p class="about-intro__sub" style="text-align:center; margin-bottom:24px;">Three Things We Never Compromise On</p>

			<div class="about-values-grid">
				<div class="value-card">
					<span class="value-card__badge">01</span>
					<h3 class="value-card__title">PURITY</h3>
					<p class="value-card__text">100% natural, freshly extracted juice with zero added water, zero added sugar, and zero preservatives. What you taste is what nature made.</p>
				</div>
				<div class="value-card">
					<span class="value-card__badge">02</span>
					<h3 class="value-card__title">QUALITY</h3>
					<p class="value-card__text">We partner directly with sustainable growers, selecting prime sugarcane stalks harvested at peak sweetness for the richest flavour.</p>
				</div>
				<div class="value-card">
					<span class="value-card__badge">03</span>
					<h3 class="value-card__title">TRADITION</h3>
					<p class="value-card__text">Preserving the authentic cold-press craft and bringing back timeless memories of enjoying fresh cane juice under the sun.</p>
				</div>
			</div>
		</div>
	</section>

	<!-- Gold Wave Divider -->
	<div class="gold-wave-divider" aria-hidden="true">
		<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/textures/border/gold-wave.svg' ) ); ?>" alt="" loading="lazy">
	</div>

	<!-- ═══════════ 6. OUR MILESTONES TIMELINE ═══════════ -->
	<?php if ( ! empty( $milestone_items ) ) : ?>
		<section class="section about-milestones-section">
			<div class="container container--narrow">
				<h2 class="about-section-title">OUR HISTORIC <em>Journey</em></h2>
				<p class="about-intro__sub" style="text-align:center; margin-bottom:24px;">Milestones Over The Years</p>

				<div class="milestone-timeline">
					<?php foreach ( $milestone_items as $ms ) : ?>
						<div class="milestone-item">
							<div class="milestone-item__year"><?php echo esc_html( (string) ( $ms['year'] ?? '' ) ); ?></div>
							<div class="milestone-item__content">
								<h4 class="milestone-item__title"><?php echo esc_html( (string) ( $ms['title'] ?? '' ) ); ?></h4>
								<p class="milestone-item__desc"><?php echo esc_html( (string) ( $ms['desc'] ?? '' ) ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- Deckled Edge Divider -->
	<div class="deckled-divider" aria-hidden="true">
		<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/textures/border/deckled-edge.svg' ) ); ?>" alt="" loading="lazy">
	</div>

	<!-- ═══════════ 7. MEET THE TEAM CAROUSEL (Dark Botanical) ═══════════ -->
	<?php if ( ! empty( $team_members ) ) : ?>
		<section class="section section--dark-botanical about-team-section" id="team">
			<div class="container">
				<div class="section-header">
					<h2 class="section-header__title">MEET THE <em>Cane Family</em></h2>
					<p class="section-header__tag">The Hands Behind The Craft</p>
				</div>

				<div class="vintage-carousel-wrapper">
					<button class="vintage-carousel-ctrl vintage-carousel-ctrl--prev" type="button" aria-label="Previous Team Member" onclick="document.getElementById('team-carousel-track').scrollBy({left: -300, behavior: 'smooth'})">‹</button>
					<div class="vintage-card-carousel" id="team-carousel-track">
						<?php foreach ( $team_members as $member ) : ?>
							<div class="team-card frame--rough-cut">
								<div class="team-card__photo frame--ornate-sm">
									<img src="<?php echo esc_url( UrlHelper::resolve( (string) ( $member['photo'] ?? '' ) ) ); ?>" alt="<?php echo esc_attr( (string) ( $member['name'] ?? '' ) ); ?>" loading="lazy">
								</div>
								<div class="team-card__body">
									<h4 class="team-card__name"><?php echo esc_html( (string) ( $member['name'] ?? '' ) ); ?></h4>
									<span class="team-card__role"><?php echo esc_html( (string) ( $member['role'] ?? '' ) ); ?></span>
									<p class="team-card__bio"><?php echo esc_html( (string) ( $member['bio'] ?? '' ) ); ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
					<button class="vintage-carousel-ctrl vintage-carousel-ctrl--next" type="button" aria-label="Next Team Member" onclick="document.getElementById('team-carousel-track').scrollBy({left: 300, behavior: 'smooth'})">›</button>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<!-- ═══════════ 8. PHOTO GALLERY CAROUSEL ═══════════ -->
	<section class="section about-gallery-section" id="heritage-gallery">
		<div class="container">
			<h2 class="about-section-title">HERITAGE <em>Photo Gallery</em></h2>
			<p class="about-intro__sub" style="text-align:center; margin-bottom:20px;">Moments from Our Journey</p>

			<div class="vintage-carousel-wrapper">
				<button class="vintage-carousel-ctrl vintage-carousel-ctrl--prev" type="button" aria-label="Previous Gallery Photo" onclick="document.getElementById('gallery-carousel-track').scrollBy({left: -300, behavior: 'smooth'})">‹</button>
				<div class="vintage-card-carousel" id="gallery-carousel-track">
					<div class="vintage-carousel-card">
						<div class="goodness-carousel-card__photo">
							<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/sugarcane/hero_juice.jpg' ) ); ?>" alt="Fresh Sugarcane Juice" loading="lazy">
						</div>
						<div class="goodness-carousel-card__body">
							<h4 class="goodness-carousel-card__title">COLD-PRESSED PURE</h4>
							<span class="goodness-carousel-card__step">FRESH HARVEST</span>
							<p class="goodness-carousel-card__desc">Raw cane extracted before your eyes.</p>
						</div>
					</div>
					<div class="vintage-carousel-card">
						<div class="goodness-carousel-card__photo">
							<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/sugarcane/stacks.jpg' ) ); ?>" alt="Sugarcane Stacks" loading="lazy">
						</div>
						<div class="goodness-carousel-card__body">
							<h4 class="goodness-carousel-card__title">SUGARCANE STACKS</h4>
							<span class="goodness-carousel-card__step">ORGANIC FARM</span>
							<p class="goodness-carousel-card__desc">Prime mature stalks delivered daily.</p>
						</div>
					</div>
					<div class="vintage-carousel-card">
						<div class="goodness-carousel-card__photo">
							<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/sugarcane/story_moments.jpg' ) ); ?>" alt="Story Moments" loading="lazy">
						</div>
						<div class="goodness-carousel-card__body">
							<h4 class="goodness-carousel-card__title">HERITAGE MOMENTS</h4>
							<span class="goodness-carousel-card__step">STALL GATHERINGS</span>
							<p class="goodness-carousel-card__desc">Bringing families together with taste.</p>
						</div>
					</div>
					<div class="vintage-carousel-card">
						<div class="goodness-carousel-card__photo">
							<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/sugarcane/sugarcane_stalks_etching.jpg' ) ); ?>" alt="Sugarcane Stalks" loading="lazy">
						</div>
						<div class="goodness-carousel-card__body">
							<h4 class="goodness-carousel-card__title">BOTANICAL GROVE</h4>
							<span class="goodness-carousel-card__step">SUSTAINABLE CROP</span>
							<p class="goodness-carousel-card__desc">Grown with nature under the sun.</p>
						</div>
					</div>
					<div class="vintage-carousel-card">
						<div class="goodness-carousel-card__photo">
							<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/sugarcane/drink_classic.jpg' ) ); ?>" alt="Classic Juice" loading="lazy">
						</div>
						<div class="goodness-carousel-card__body">
							<h4 class="goodness-carousel-card__title">CLASSIC RAW JUICE</h4>
							<span class="goodness-carousel-card__step">ZERO PRESERVATIVES</span>
							<p class="goodness-carousel-card__desc">100% natural, sweet and refreshing.</p>
						</div>
					</div>
					<div class="vintage-carousel-card">
						<div class="goodness-carousel-card__photo">
							<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/sugarcane/drink_lemon.jpg' ) ); ?>" alt="Lemon Ginger" loading="lazy">
						</div>
						<div class="goodness-carousel-card__body">
							<h4 class="goodness-carousel-card__title">LEMON GINGER ZEST</h4>
							<span class="goodness-carousel-card__step">SIGNATURE BLEND</span>
							<p class="goodness-carousel-card__desc">Invigorating spice & citrus blend.</p>
						</div>
					</div>
				</div>
				<button class="vintage-carousel-ctrl vintage-carousel-ctrl--next" type="button" aria-label="Next Gallery Photo" onclick="document.getElementById('gallery-carousel-track').scrollBy({left: 300, behavior: 'smooth'})">›</button>
			</div>
		</div>
	</section>

	<!-- Gold Wave Divider -->
	<div class="gold-wave-divider" aria-hidden="true">
		<img src="<?php echo esc_url( UrlHelper::resolve( 'assets/images/textures/border/gold-wave.svg' ) ); ?>" alt="" loading="lazy">
	</div>

	<!-- ═══════════ 9. LOGO STRIP / PARTNERS ═══════════ -->
	<?php View::component( 'sections/logo-strip-section' ); ?>

	<!-- ═══════════ 10. CTA BANNER ═══════════ -->
	<section class="section about-cta-section">
		<div class="container container--narrow">
			<div class="back-home__box">
				<h3 class="back-home__title">WANT TO TASTE THE <em>Tradition?</em></h3>
				<p class="back-home__sub">Visit our stall in Sutton, London or book our live sugarcane bar for your next wedding, birthday, corporate event, or festival.</p>
				<div class="hero-sugarcane__buttons" style="justify-content: center;">
					<a class="btn btn--primary-vintage" href="<?php echo esc_url( RouteService::url( 'contact' ) ); ?>">
						<span>BOOK AN EVENT</span>
					</a>
					<a class="btn btn--outline-vintage" href="<?php echo esc_url( RouteService::url( 'history' ) ); ?>">
						<span>ALL ABOUT CANE</span>
					</a>
				</div>
			</div>
		</div>
	</section>

	<!-- ═══════════ 11. TRUST RIBBON ═══════════ -->
	<?php View::component( 'sections/trust-ribbon-section' ); ?>
</div>
