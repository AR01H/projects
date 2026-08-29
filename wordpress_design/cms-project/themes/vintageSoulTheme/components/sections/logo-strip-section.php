<?php

use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

$title = (string) ( $title ?? 'AS FEATURED & TRUSTED BY' );
$items = (array) ( $items ?? array(
	array( 'name' => 'BBC GOOD FOOD', 'image' => 'assets/images/partners/bbc-good-food.svg' ),
	array( 'name' => 'TIME OUT LONDON', 'image' => 'assets/images/partners/timeout-london.svg' ),
	array( 'name' => 'BOROUGH MARKET', 'image' => 'assets/images/partners/borough-market.svg' ),
	array( 'name' => 'THE GROCER', 'image' => 'assets/images/partners/the-grocer.svg' ),
	array( 'name' => 'SLOW FOOD UK', 'image' => 'assets/images/partners/slow-food.svg' ),
	array( 'name' => 'FOOD HYGIENE 5★', 'image' => 'assets/images/partners/food-hygiene-5.svg' ),
) );
?>
<section class="section section--logo-strip logo-strip-vintage" id="partners">
	<div class="vintage-ribbon-tag">
		<span><?php echo esc_html( $title ); ?></span>
	</div>

	<div class="logo-strip-vintage__scroller">
		<div class="logo-strip-vintage__track">
			<?php for ( $loop = 0; $loop < 3; ++$loop ) : ?>
				<?php foreach ( $items as $item ) :
					$name  = (string) ( $item['name'] ?? '' );
					$image = (string) ( $item['image'] ?? '' );
					if ( empty( $image ) ) {
						$image = 'assets/images/partners/bbc-good-food.svg';
					}
					$resolved_img = UrlHelper::resolve( $image );
				?>
					<div class="logo-strip-item-img">
						<img src="<?php echo esc_url( $resolved_img ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy">
					</div>
				<?php endforeach; ?>
			<?php endfor; ?>
		</div>
	</div>
</section>
