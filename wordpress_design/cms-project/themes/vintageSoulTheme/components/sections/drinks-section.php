<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Services\RouteService;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

$products_data = (array) ( JsonFileProvider::read( 'data/content/products.json' ) ?? array() );

$tag   = (string) ( $tag ?? ( $products_data['tag'] ?? '' ) );
$title = (string) ( $title ?? ( $products_data['title'] ?? '' ) );
$sub   = (string) ( $sub ?? ( $products_data['sub'] ?? '' ) );
$items = (array) ( $items ?? ( $products_data['items'] ?? array() ) );
?>
<section class="section section--drinks drinks-vintage paper-rough" id="our-drinks">
	<div class="container container--narrow drinks-vintage__container">
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'     => $tag,
				'title'   => 'SIGNATURE CANE <em>Creations</em>',
				'eyebrow' => 'A Taste of Tradition',
				'sub'     => $sub,
				'ribbon'  => true,
			)
		);
		?>

		<div class="drinks-vintage__list">
			<?php foreach ( $items as $item ) :
				$name      = (string) ( $item['name'] ?? '' );
				$desc      = (string) ( $item['desc'] ?? '' );
				$price     = (string) ( $item['price'] ?? '' );
				$img_raw   = (string) ( $item['image'] ?? 'assets/images/sugarcane/drink_classic.jpg' );
				$img       = UrlHelper::resolve( $img_raw );
				$btn_label = (string) ( $item['button']['label'] ?? 'ORDER NOW' );
				$btn_route = (string) ( $item['button']['route'] ?? 'contact' );
			?>
				<div class="drink-row-card card--cut">
					<div class="drink-row-card__media">
						<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
					</div>
					<div class="drink-row-card__content">
						<h3 class="drink-row-card__name"><?php echo esc_html( $name ); ?></h3>
						<?php if ( '' !== $desc ) : ?>
							<p class="drink-row-card__desc"><?php echo esc_html( $desc ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
