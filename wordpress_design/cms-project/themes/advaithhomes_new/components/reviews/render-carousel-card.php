<?php
defined( 'ABSPATH' ) || exit;

/**
 * Carousel slide card - a fixed width so several sit side by side in a
 * horizontal track. Review text is clamped to 4 lines with an ellipsis
 * (CSS line-clamp, backed by a server-side word-trim fallback for browsers
 * that don't support it). Used by AH_Reviews_Model::render_carousel_card().
 *
 * Theme-level override of plugins/cms-plugin/models/reviews/render-carousel-card.php -
 * see render-big.php in this folder for how the override resolves.
 */
function ah_review_render_carousel_card( object $r ): string {
	$uid       = 'ah_review_car_' . (int) $r->id;
	$img_url   = ! empty( $r->reviewer_image_id ) ? wp_get_attachment_image_url( (int) $r->reviewer_image_id, 'thumbnail' ) : '';
	$rating    = max( 0, min( 5, (int) $r->rating ) );
	$full_text = wp_strip_all_tags( (string) $r->review_text );
	// Belt-and-braces cap for browsers without line-clamp support - the CSS
	// clamp below is what actually enforces "4 lines" visually.
	$text = wp_trim_words( $full_text, 40 );

	ob_start();
	?>
<div id="<?php echo esc_attr( $uid ); ?>" class="ah-review-card ah-review-card--carousel">
	<div class="ah-rv-head">
		<?php if ( $img_url ) : ?>
			<img class="ah-rv-avatar" src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $r->reviewer_name ); ?>">
		<?php endif; ?>
		<div>
			<div class="ah-rv-name"><?php echo esc_html( $r->reviewer_name ); ?></div>
			<?php if ( ! empty( $r->reviewer_title ) ) : ?><div class="ah-rv-title"><?php echo esc_html( $r->reviewer_title ); ?></div><?php endif; ?>
		</div>
	</div>
	<div class="ah-rv-stars"><?php echo esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) ); ?></div>
	<p class="ah-rv-text" title="<?php echo esc_attr( $full_text ); ?>"><?php echo esc_html( $text ); ?></p>
</div>
	<?php
	return (string) ob_get_clean();
}
