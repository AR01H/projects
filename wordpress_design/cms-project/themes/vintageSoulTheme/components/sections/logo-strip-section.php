<?php
/**
 * VintageSoulTheme - As Featured & Trusted By Partner Logo Strip
 *
 * Dynamic JSON data flow featuring London Sutton Market, Soil Association,
 * Food Hygiene 5★ Rating, BBC Good Food, Time Out London, and Borough Market.
 */
use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\UrlHelper;

defined( 'ABSPATH' ) || exit;

$strip_data = (array) ( JsonFileProvider::read( 'data/content/logo-strip.json' ) ?? array() );
$title      = (string) ( $title ?? ( $strip_data['title'] ?? 'AS FEATURED & TRUSTED BY' ) );
$items      = (array) ( $items ?? ( $strip_data['items'] ?? array() ) );

if ( empty( $items ) ) {
	$items = array(
		array( 'name' => 'SUTTON MARKET', 'image' => 'assets/images/partners/sutton-market.svg' ),
		array( 'name' => 'FOOD HYGIENE 5★', 'image' => 'assets/images/partners/food-hygiene-5.svg' ),
		array( 'name' => 'SOIL ASSOCIATION', 'image' => 'assets/images/partners/soil-association.svg' ),
		array( 'name' => 'BBC GOOD FOOD', 'image' => 'assets/images/partners/bbc-good-food.svg' ),
		array( 'name' => 'TIME OUT LONDON', 'image' => 'assets/images/partners/timeout-london.svg' ),
		array( 'name' => 'BOROUGH MARKET', 'image' => 'assets/images/partners/borough-market.svg' ),
		array( 'name' => 'SLOW FOOD UK', 'image' => 'assets/images/partners/slow-food.svg' ),
		array( 'name' => 'THE GROCER', 'image' => 'assets/images/partners/the-grocer.svg' ),
	);
}
?>
<section class="section section--logo-strip logo-strip-vintage" id="partners">
	<div class="container logo-strip-vintage__header">
		<div class="vintage-ribbon-tag">
			<span><?php echo esc_html( $title ); ?></span>
		</div>
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
					<div class="logo-strip-item-img" title="<?php echo esc_attr( $name ); ?>">
						<img src="<?php echo esc_url( $resolved_img ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" height="46">
					</div>
				<?php endforeach; ?>
			<?php endfor; ?>
		</div>
	</div>
</section>
