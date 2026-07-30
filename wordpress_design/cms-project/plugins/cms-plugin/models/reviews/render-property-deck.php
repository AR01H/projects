<?php
defined( 'ABSPATH' ) || exit;

/**
 * "Property Deck" review card - a flip card. Front: a plain white identity
 * card (portrait or initials disc, name, role). Back (hover on desktop, tap
 * on mobile): the gold gradient "story" design - story title, quote, mini
 * description, name, role, and result stat. Used standalone by
 * AH_Reviews_Model::render_review() for representing = 'property_deck'
 * (e.g. the [ah_review id="X"] shortcode).
 *
 * The full fanned-deck carousel with the synced location/title/stat detail
 * panel - which mirrors this card's gold back face - lives in
 * themes/advaithhomes_new/components/sections/reviews_property_deck.php,
 * since it needs the whole group of property_deck reviews at once, not a
 * single review. This file is just what a single one looks like on its own.
 */
function ah_review_render_property_deck( object $r ): string {
	$uid     = 'ah_review_' . (int) $r->id;
	$img_url = ! empty( $r->reviewer_image_id ) ? wp_get_attachment_image_url( (int) $r->reviewer_image_id, 'thumbnail' ) : '';
	$quote   = wp_strip_all_tags( (string) $r->review_text );

	// Initials from the first two words of the name, for the front's no-photo fallback.
	$initials = '';
	foreach ( array_slice( preg_split( '/\s+/', trim( (string) $r->reviewer_name ) ), 0, 2 ) as $part ) {
		if ( '' !== $part ) { $initials .= mb_substr( $part, 0, 1 ); }
	}
	$initials = mb_strtoupper( $initials );

	// Highlight Stat(s) can hold multiple "Label: Value" lines (for Big Box's
	// stat breakdown) - this compact card only has room for one, so it shows
	// just the first line, reassembled as "Label: Value".
	$stats     = AH_Reviews_Model::parse_stat_lines( (string) ( $r->stat_line ?? '' ) );
	$stat_text = '';
	if ( ! empty( $stats ) ) {
		$stat_text = '' !== $stats[0]['label'] ? $stats[0]['label'] . ': ' . $stats[0]['value'] : $stats[0]['value'];
	}

	ob_start();
	?>
<div id="<?php echo esc_attr( $uid ); ?>" class="ah-review-card ah-review-card--property-deck ah-rv-flip">
	<div class="ah-rv-flip-inner">
		<div class="ah-rv-flip-front">
			<?php if ( $img_url ) : ?>
				<img class="ah-rv-pd-front-portrait" src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $r->reviewer_name ); ?>">
			<?php elseif ( '' !== $initials ) : ?>
				<div class="ah-rv-pd-front-initials" aria-hidden="true"><?php echo esc_html( $initials ); ?></div>
			<?php endif; ?>
			<div class="ah-rv-pd-front-name"><?php echo esc_html( $r->reviewer_name ); ?></div>
			<?php if ( ! empty( $r->reviewer_title ) ) : ?>
				<div class="ah-rv-pd-front-role"><?php echo esc_html( $r->reviewer_title ); ?></div>
			<?php endif; ?>
			<span class="ah-rv-flip-hint"><?php esc_html_e( 'Tap to read their story →', 'ah-cms' ); ?></span>
		</div>
		<div class="ah-rv-flip-back">
			<?php if ( $img_url ) : ?>
				<img class="ah-rv-pd-portrait" src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $r->reviewer_name ); ?>">
			<?php else : ?>
				<div class="ah-rv-name-fallback" aria-hidden="true"><?php echo esc_html( $r->reviewer_name ); ?></div>
			<?php endif; ?>
			<?php if ( ! empty( $r->story_title ) ) : ?>
				<div class="ah-rv-pd-title"><?php echo esc_html( $r->story_title ); ?></div>
			<?php endif; ?>
			<p class="ah-rv-pd-quote">&ldquo;<?php echo esc_html( wp_trim_words( $quote, 16 ) ); ?>&rdquo;</p>
			<?php if ( ! empty( $r->short_desc ) ) : ?>
				<p class="ah-rv-pd-mini-desc"><?php echo esc_html( $r->short_desc ); ?></p>
			<?php endif; ?>
			<div class="ah-rv-pd-name"><?php echo esc_html( $r->reviewer_name ); ?></div>
			<?php if ( ! empty( $r->reviewer_title ) ) : ?>
				<div class="ah-rv-pd-role"><?php echo esc_html( $r->reviewer_title ); ?></div>
			<?php endif; ?>
			<?php if ( '' !== $stat_text ) : ?>
				<div class="ah-rv-pd-stat"><?php echo esc_html( $stat_text ); ?></div>
			<?php endif; ?>
		</div>
	</div>
</div>
	<?php
	return (string) ob_get_clean() . ah_review_render_flip_script_once();
}
