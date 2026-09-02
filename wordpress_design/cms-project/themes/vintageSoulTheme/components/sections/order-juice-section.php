<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

$order_data    = (array) ( JsonFileProvider::read( 'data/content/order-juice.json' ) ?? array() );
$products_data = (array) ( JsonFileProvider::read( 'data/content/products.json' ) ?? array() );

$tag      = (string) ( $tag ?? ( $order_data['tag'] ?? 'Fresh Orders & Bulk Supply' ) );
$title    = (string) ( $title ?? ( $order_data['title'] ?? 'ORDER FRESH JUICE &amp; <em>Bulk Catering</em>' ) );
$subtitle = (string) ( $subtitle ?? ( $sub ?? ( $order_data['sub'] ?? 'Pressed fresh to order from premium whole sugarcane. Available for live takeaway, party kegs, and London home deliveries.' ) ) );

$products          = (array) ( $products ?? ( $products_data['items'] ?? array() ) );
$order_gallery     = (array) ( $order_gallery ?? ( $order_data['gallery'] ?? array() ) );
$wholesale_options = (array) ( $wholesale_options ?? ( $order_data['wholesale_options'] ?? array() ) );
$order_reviews     = (array) ( $order_reviews ?? ( $order_data['reviews'] ?? array() ) );
$banner            = (array) ( $order_data['banner'] ?? array() );
?>
<section class="section section--order-juice order-juice-vintage-block torn-dark-block grain-dark" id="order" style="position:relative; overflow:hidden;">
	<?php View::component( 'background/ambient-layer', array( 'variant' => 'dark', 'cane_positions' => array( 'top-right', 'mid-right', 'bottom-left' ), 'bubble_count' => 14 ) ); ?>

	<div class="container order-juice-vintage__container" style="position:relative; z-index:3;">
		
		<!-- 1. Header (3 Elements: Tag, Title, Subtitle) -->
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'     => 'OUR MENU',
				'title'   => 'ORDER FRESH <em>Cane Juice</em>',
				'sub'     => $subtitle,
				'variant' => 'dark',
				'ribbon'  => true,
			)
		);
		?>

		<!-- 2. Product Range Stream (Left-to-Right) -->
		<?php
		View::component( 'card-stream/card-stream', array(
			'items'      => $products,
			'card_type'  => 'product',
			'direction'  => 'ltr',
		) );
		?>

		<!-- 3. Fresh Preparation & Production Stream (Right-to-Left) -->
		<div class="vintage-ribbon-tag vintage-ribbon-tag--gold">
			<span>FRESH EXTRACTION & BOTTLING</span>
		</div>
		<?php
		View::component( 'card-stream/card-stream', array(
			'items'      => $order_gallery,
			'card_type'  => 'gallery',
			'direction'  => 'rtl',
		) );
		?>

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

		<!-- 5. Customer Reviews Stream (Left-to-Right) -->
		<div class="vintage-ribbon-tag">
			<span>CUSTOMER FEEDBACK</span>
		</div>
		<?php
		View::component( 'card-stream/card-stream', array(
			'items'      => $order_reviews,
			'card_type'  => 'dark-review',
			'direction'  => 'ltr',
		) );
		?>

		<!-- 6. Direct Order & Delivery Callout Banner -->
		<div class="order-delivery-banner card--rough-cut">
			<div class="order-delivery-banner__content">
				<h3 class="order-delivery-banner__title">NEED FRESH JUICE OR BULK SUPPLY?</h3>
				<p class="order-delivery-banner__text">We press fresh on demand and deliver across Sutton and Greater London. Message us on WhatsApp for fast orders and instant delivery confirmation.</p>
			</div>
			<div class="order-delivery-banner__actions">
				<a class="btn btn--primary-vintage" href="<?php echo esc_url( \VintageSoul\Services\SettingsService::whatsapp_url() ); ?>" target="_blank" rel="noopener">
					<span>💬 ORDER ON WHATSAPP</span>
				</a>
				<a class="btn btn--outline-vintage" href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', \VintageSoul\Services\SettingsService::phone() ) ); ?>">
					<span>📞 CALL <?php echo esc_html( \VintageSoul\Services\SettingsService::phone() ); ?></span>
				</a>
			</div>
		</div>

	</div>
</section>
