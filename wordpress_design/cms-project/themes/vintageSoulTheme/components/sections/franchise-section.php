<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

$franchise_data = (array) ( JsonFileProvider::read( 'data/content/franchise-teaser.json' ) ?? array() );

$tag   = (string) ( $tag ?? ( $franchise_data['tag'] ?? 'Partner With Us' ) );
$title = (string) ( $title ?? ( $franchise_data['title'] ?? 'FRANCHISE &amp; <em>Stall Partnerships</em>' ) );
$sub   = (string) ( $sub ?? ( $franchise_data['sub'] ?? 'Own a profitable, turnkey sugarcane juice business with full brand support.' ) );

$franchise_pillars  = (array) ( $pillars ?? ( $franchise_data['pillars'] ?? array() ) );
$franchise_gallery  = (array) ( $gallery ?? ( $franchise_data['gallery'] ?? array() ) );
$franchise_steps    = (array) ( $steps ?? ( $franchise_data['steps'] ?? array() ) );
$franchisee_reviews = (array) ( $reviews ?? ( $franchise_data['reviews'] ?? array() ) );
$cta_box            = (array) ( $franchise_data['cta'] ?? array() );
?>
<section class="section section--franchise franchise-vintage-block torn-dark-block grain-dark" id="franchise">
	<?php View::component( 'background/ambient-layer', array( 'variant' => 'dark', 'cane_positions' => array( 'top-left', 'bottom-right' ), 'bubble_count' => 12 ) ); ?>
	<div class="container franchise-vintage__container">
		
		<!-- 1. Header -->
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'     => 'Business Opportunity',
				'title'   => 'BECOME A <em>Franchise Partner</em>',
				'sub'     => $sub,
				'variant' => 'dark',
				'ribbon'  => true,
			)
		);
		?>

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

		<!-- 3. Franchise Operations & Stalls Photo Gallery Stream (Left-to-Right) -->
		<div class="vintage-ribbon-tag vintage-ribbon-tag--gold">
			<span>OUR OUTLETS & MACHINERY</span>
		</div>
		<?php
		View::component( 'card-stream/card-stream', array(
			'items'      => $franchise_gallery,
			'card_type'  => 'gallery',
			'direction'  => 'ltr',
		) );
		?>

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

		<!-- 5. Franchisee Success Stories Stream (Right-to-Left) -->
		<div class="vintage-ribbon-tag">
			<span>PARTNER EXPERIENCES</span>
		</div>
		<?php
		View::component( 'card-stream/card-stream', array(
			'items'      => $franchisee_reviews,
			'card_type'  => 'dark-review',
			'direction'  => 'rtl',
		) );
		?>

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
				<a class="btn btn--outline-vintage" href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', \VintageSoul\Services\SettingsService::phone() ) ); ?>">
					<span>📞 CALL <?php echo esc_html( \VintageSoul\Services\SettingsService::phone() ); ?></span>
				</a>
			</div>
		</div>

	</div>
</section>
