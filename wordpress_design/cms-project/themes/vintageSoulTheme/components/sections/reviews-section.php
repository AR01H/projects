<?php

defined( 'ABSPATH' ) || exit;

use VintageSoul\DataProviders\JsonFileProvider;
use VintageSoul\Support\View;

$testimonials_data = (array) ( JsonFileProvider::read( 'data/content/testimonials.json' ) ?? array() );

$tag     = (string) ( $tag ?? ( $testimonials_data['tag'] ?? '' ) );
$title   = (string) ( $title ?? ( $testimonials_data['title'] ?? '' ) );
$sub     = (string) ( $sub ?? ( $testimonials_data['sub'] ?? '' ) );
$items   = (array) ( $items ?? ( $testimonials_data['items'] ?? array() ) );
?>
<section class="section section--reviews reviews-vintage paper-rough" id="reviews">
	<div class="container reviews-vintage__container">
		<?php
		View::component(
			'section-header/section-header',
			array(
				'tag'     => ! empty( $tag ) ? $tag : 'Customer Stories',
				'title'   => 'WHAT PEOPLE <em>Say</em>',
				'eyebrow' => "Loved Across Generations",
				'sub'     => ! empty( $sub ) ? $sub : 'Treasured feedback from festivals, private parties, and daily visitors.',
				'ribbon'  => true,
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

