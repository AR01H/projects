<?php
/**
 * Testimonial cards - reusable across pages with DIFFERENT content per page.
 *
 * Data source is switchable so each page shows its own testimonials:
 *   page_sections.json -> { "component": "reviews", "args": { "source": "reviews_events" } }
 * Defaults to admin/data/reviews.json.
 *
 * Source shape: { tag, title, sub, items[] { name, location, rating, text } }.
 * Renders nothing if there are no items.
 */
defined( 'ABSPATH' ) || exit;

$review_source = ( isset( $source ) && $source ) ? (string) $source : 'reviews';
$data          = nt_data( $review_source );
$items         = $data['items'] ?? array();
if ( empty( $items ) ) {
	return;
}

$tag   = $data['tag'] ?? '';
$title = $data['title'] ?? '';
$sub   = $data['sub'] ?? '';
?>
<section class="nt-reviews" id="reviews">
	<div class="container">
		<div class="nt-reviews__header">
			<?php if ( $tag ) : ?><span class="nt-section-tag"><?php echo esc_html( $tag ); ?></span><?php endif; ?>
			<?php if ( $title ) : ?><h2 class="nt-reviews__title"><?php echo wp_kses( $title, array( 'em' => array(), 'span' => array( 'class' => array() ) ) ); ?></h2><?php endif; ?>
			<?php if ( $sub ) : ?><p class="nt-reviews__sub"><?php echo esc_html( $sub ); ?></p><?php endif; ?>
		</div>

		<div class="nt-reviews__grid">
			<?php foreach ( $items as $r ) :
				$r      = (array) $r;
				$name   = $r['name'] ?? '';
				$loc    = $r['location'] ?? '';
				$text   = $r['text'] ?? '';
				$rating = max( 0, min( 5, (int) ( $r['rating'] ?? 5 ) ) );
				if ( '' === trim( (string) $text ) ) {
					continue;
				}
			?>
				<figure class="nt-review-card">
					<div class="nt-review-card__stars" aria-label="<?php echo esc_attr( $rating ); ?> out of 5">
						<?php echo str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ); ?>
					</div>
					<blockquote class="nt-review-card__text"><?php echo esc_html( $text ); ?></blockquote>
					<figcaption class="nt-review-card__by">
						<span class="nt-review-card__name"><?php echo esc_html( $name ); ?></span>
						<?php if ( $loc ) : ?><span class="nt-review-card__loc"><?php echo esc_html( $loc ); ?></span><?php endif; ?>
					</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
