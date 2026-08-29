<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\Services\RouteService;
use VintageSoul\Support\UrlHelper;

$tag    = (string) ( $tag ?? 'Our Range' );
$title  = (string) ( $title ?? 'OUR DRINKS' );
$sub    = (string) ( $sub ?? 'Pure, natural and freshly pressed sugarcane juice — served chilled.' );
$items  = (array) ( $items ?? array() );
?>
<section class="section section--drinks drinks-vintage paper-rough" id="our-drinks">
	<div class="container container--narrow drinks-vintage__container">
		<div class="drinks-vintage__header">
			<span class="vintage-ribbon-tag">
				<span><?php echo esc_html( $tag ); ?></span>
			</span>
			<h2 class="drinks-vintage__title"><?php echo esc_html( trim( strip_tags( $title ), " -—" ) ); ?></h2>
			<p class="section-eyebrow">A Taste of Tradition</p>
			<?php if ( '' !== $sub ) : ?>
				<p class="drinks-vintage__sub"><?php echo esc_html( $sub ); ?></p>
			<?php endif; ?>
		</div>

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
				<div class="drink-row-card">
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
