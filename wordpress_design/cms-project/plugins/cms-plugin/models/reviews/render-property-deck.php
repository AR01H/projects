<?php
defined( 'ABSPATH' ) || exit;

/**
 * "Property Deck" review card - a compact gold gradient card (portrait,
 * short quote, name). Used standalone by AH_Reviews_Model::render_review()
 * for representing = 'property_deck' (e.g. the [ah_review id="X"] shortcode).
 *
 * The full fanned-deck carousel with the synced location/title/stat detail
 * panel - the design this card's own gradient/style matches - lives in
 * themes/advaithhomes_new/components/sections/reviews_property_deck.php,
 * since it needs the whole group of property_deck reviews at once, not a
 * single review. This file is just what a single one looks like on its own.
 */
function ah_review_render_property_deck( object $r ): string {
	$uid     = 'ah_review_' . (int) $r->id;
	$img_url = ! empty( $r->reviewer_image_id ) ? wp_get_attachment_image_url( (int) $r->reviewer_image_id, 'thumbnail' ) : '';
	$quote   = wp_strip_all_tags( (string) $r->review_text );

	ob_start();
	?>
<div id="<?php echo esc_attr( $uid ); ?>" class="ah-review-card ah-review-card--property-deck">
	<?php if ( $img_url ) : ?>
		<img class="ah-rv-pd-portrait" src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $r->reviewer_name ); ?>">
	<?php endif; ?>
	<p class="ah-rv-pd-quote">&ldquo;<?php echo esc_html( wp_trim_words( $quote, 16 ) ); ?>&rdquo;</p>
	<div class="ah-rv-pd-name"><?php echo esc_html( $r->reviewer_name ); ?></div>
	<?php if ( ! empty( $r->stat_line ) ) : ?>
		<div class="ah-rv-pd-stat"><?php echo esc_html( $r->stat_line ); ?></div>
	<?php endif; ?>
</div>
	<?php
	return (string) ob_get_clean();
}
