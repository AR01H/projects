<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\RouteService;
use VintageSoul\Services\SettingsService;
use VintageSoul\Support\IconHelper;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

$order_data    = (array) ( JsonFileProvider::read( 'data/content/order-juice.json' ) ?? array() );
$products_data = (array) ( JsonFileProvider::read( 'data/content/products.json' ) ?? array() );

$tag      = (string) ( $tag ?? ( $order_data['tag'] ?? '' ) );
$title    = (string) ( $title ?? ( $order_data['title'] ?? '' ) );
$subtitle = (string) ( $subtitle ?? ( $sub ?? ( $order_data['sub'] ?? '' ) ) );

$gallery_ribbon   = (string) ( $order_data['gallery_ribbon'] ?? '' );
$wholesale_ribbon = (string) ( $order_data['wholesale_ribbon'] ?? '' );
$reviews_ribbon   = (string) ( $order_data['reviews_ribbon'] ?? '' );

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
				'tag'     => $tag,
				'title'   => $title,
				'sub'     => $subtitle,
				'variant' => 'dark',
				'ribbon'  => true,
			)
		);
		?>

		<!-- 2. Product Range Stream (Left-to-Right) -->
		<?php if ( ! empty( $products ) ) : ?>
			<?php
			View::component( 'card-stream/card-stream', array(
				'items'      => $products,
				'card_type'  => 'product',
				'direction'  => 'ltr',
				'aria_label' => $title,
			) );
			?>
		<?php endif; ?>

		<!-- 3. Fresh Preparation & Production Stream (Right-to-Left) -->
		<?php if ( ! empty( $order_gallery ) ) : ?>
			<?php if ( '' !== $gallery_ribbon ) : ?>
				<div class="vintage-ribbon-tag vintage-ribbon-tag--gold">
					<span><?php echo esc_html( $gallery_ribbon ); ?></span>
				</div>
			<?php endif; ?>
			<?php
			View::component( 'card-stream/card-stream', array(
				'items'      => $order_gallery,
				'card_type'  => 'gallery',
				'direction'  => 'rtl',
				'aria_label' => $gallery_ribbon,
			) );
			?>
		<?php endif; ?>

		<!-- 4. Wholesale & Catering Supply Options -->
		<?php if ( ! empty( $wholesale_options ) ) : ?>
			<?php if ( '' !== $wholesale_ribbon ) : ?>
				<div class="vintage-ribbon-tag">
					<span><?php echo esc_html( $wholesale_ribbon ); ?></span>
				</div>
			<?php endif; ?>
			<div class="wholesale-cards-grid">
				<?php foreach ( $wholesale_options as $ws ) :
					$ws_icon = IconHelper::get( (string) ( $ws['icon'] ?? 'leaf' ), '#f6d599', 28 );
				?>
					<div class="wholesale-card card--rough-cut-dark">
						<div class="wholesale-card__head">
							<span class="wholesale-card__icon"><?php echo $ws_icon; // phpcs:ignore ?></span>
							<?php if ( ! empty( $ws['tag'] ) ) : ?>
								<span class="wholesale-card__tag"><?php echo esc_html( (string) $ws['tag'] ); ?></span>
							<?php endif; ?>
						</div>
						<h3 class="wholesale-card__title"><?php echo esc_html( (string) ( $ws['title'] ?? '' ) ); ?></h3>
						<p class="wholesale-card__desc"><?php echo esc_html( (string) ( $ws['desc'] ?? '' ) ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<!-- 5. Customer Reviews Stream (Left-to-Right) -->
		<?php if ( ! empty( $order_reviews ) ) : ?>
			<?php if ( '' !== $reviews_ribbon ) : ?>
				<div class="vintage-ribbon-tag">
					<span><?php echo esc_html( $reviews_ribbon ); ?></span>
				</div>
			<?php endif; ?>
			<?php
			View::component( 'card-stream/card-stream', array(
				'items'      => $order_reviews,
				'card_type'  => 'dark-review',
				'direction'  => 'ltr',
				'aria_label' => $reviews_ribbon,
			) );
			?>
		<?php endif; ?>

		<!-- 6. Direct Order & Delivery Callout Banner -->
		<?php if ( ! empty( $banner ) ) : ?>
			<div class="order-delivery-banner card--rough-cut">
				<div class="order-delivery-banner__content">
					<?php if ( ! empty( $banner['title'] ) ) : ?>
						<h3 class="order-delivery-banner__title"><?php echo esc_html( (string) $banner['title'] ); ?></h3>
					<?php endif; ?>
					<?php if ( ! empty( $banner['text'] ) ) : ?>
						<p class="order-delivery-banner__text"><?php echo esc_html( (string) $banner['text'] ); ?></p>
					<?php endif; ?>
				</div>
				<div class="order-delivery-banner__actions">
					<a class="btn btn--primary-vintage" href="<?php echo esc_url( SettingsService::whatsapp_url() ); ?>" target="_blank" rel="noopener">
						<span>💬 ORDER ON WHATSAPP</span>
					</a>
					<a class="btn btn--outline-vintage" href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', SettingsService::phone() ) ); ?>">
						<span>📞 CALL <?php echo esc_html( SettingsService::phone() ); ?></span>
					</a>
				</div>
			</div>
		<?php endif; ?>

	</div>
</section>
