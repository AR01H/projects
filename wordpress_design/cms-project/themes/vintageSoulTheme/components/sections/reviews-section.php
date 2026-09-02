<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\UrlHelper;
use VintageSoul\Support\View;

$testimonials_data = (array) ( JsonFileProvider::read( 'data/content/testimonials.json' ) ?? array() );

$tag    = (string) ( $tag ?? ( $testimonials_data['tag'] ?? '' ) );
$title  = (string) ( $title ?? ( $testimonials_data['title'] ?? '' ) );
$sub    = (string) ( $sub ?? ( $testimonials_data['sub'] ?? '' ) );
$image  = (string) ( $image ?? ( $testimonials_data['image'] ?? '' ) );
$items  = (array) ( $items ?? ( $testimonials_data['items'] ?? array() ) );

$bg_url = '' !== $image ? UrlHelper::resolve( $image ) : '';
?>
<section class="section section--reviews reviews-vintage paper-rough" id="reviews" style="position: relative; overflow: hidden; background: #fbf6ee;">
	<?php if ( '' !== $bg_url ) : ?>
		<div class="reviews-vintage__bg" style="position: absolute; inset: 0; background-image: linear-gradient(180deg, rgba(251, 246, 238, 0.82) 0%, rgba(251, 246, 238, 0.65) 50%, rgba(251, 246, 238, 0.85) 100%), url('<?php echo esc_url( $bg_url ); ?>'); background-size: cover; background-position: center; pointer-events: none; z-index: 0;" aria-hidden="true"></div>
	<?php endif; ?>
	
	<div class="container reviews-vintage__container" style="position: relative; z-index: 1;">
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'    => $tag,
				'title'  => $title,
				'sub'    => $sub,
				'ribbon' => true,
			)
		);
		?>

		<?php if ( ! empty( $items ) ) : ?>
			<?php
			View::component( 'card-stream/card-stream', array(
				'items'     => $items,
				'card_type' => 'testimonial',
				'direction' => 'rtl',
			) );
			?>
		<?php endif; ?>
	</div>
</section>
