<?php

defined( 'ABSPATH' ) || exit;

$title = (string) ( $title ?? 'Reviews' );
$items = (array) ( $items ?? array() );
?>
<section class="section section--reviews reviews-vintage paper-rough" id="reviews">
	<div class="container container--narrow reviews-vintage__container">
		<div class="reviews-vintage__header">
			<h2 class="reviews-vintage__title"><?php echo esc_html( trim( strip_tags( $title ), " -—" ) ); ?></h2>
		</div>

		<div class="reviews-vintage__list">
			<?php foreach ( $items as $item ) :
				$name     = (string) ( $item['name'] ?? '' );
				$location = (string) ( $item['location'] ?? '' );
				$rating   = (int) ( $item['rating'] ?? 5 );
				$text     = (string) ( $item['text'] ?? '' );
				$badge    = (string) ( $item['badge'] ?? '' );
			?>
				<div class="review-box">
					<div class="review-box__stars">
						<?php echo str_repeat( '★', max( 1, min( 5, $rating ) ) ); ?>
					</div>
					<blockquote class="review-box__quote">"<?php echo esc_html( $text ); ?>"</blockquote>
					<div class="review-box__author">
						<span class="review-box__name">— <?php echo esc_html( $name ); ?></span>
						<?php if ( '' !== $location ) : ?>
							<span class="review-box__location"><?php echo esc_html( $location ); ?></span>
						<?php endif; ?>
					</div>
					<?php if ( '' !== $badge ) : ?>
						<span class="review-box__badge"><?php echo esc_html( $badge ); ?></span>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
