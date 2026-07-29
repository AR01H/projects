<?php
defined( 'ABSPATH' ) || exit;

/**
 * "Spotlight" review card - wide editorial layout: rating, a large serif pull
 * quote, then the name with role/location beneath, and a big circular portrait
 * on the right. Used by AH_Reviews_Model::render_review() for
 * representing = 'spotlight'.
 *
 * Uses two fields no other card surfaces:
 *   reviewer_title    -> role      ("Sole Buyer")
 *   division_category -> location  ("Bristol")
 * rendered together as "Sole Buyer in Bristol" when both are present.
 *
 * Falls back to a gold initials disc when the review has no portrait, so the
 * layout never collapses to a lopsided card.
 */
function ah_review_render_spotlight( object $r ): string {
	$uid     = 'ah_review_' . (int) $r->id;
	$img_url = ! empty( $r->reviewer_image_id ) ? wp_get_attachment_image_url( (int) $r->reviewer_image_id, 'medium' ) : '';
	$rating  = max( 0, min( 5, (int) $r->rating ) );
	$quote   = wp_strip_all_tags( (string) $r->review_text );

	$role     = trim( (string) ( $r->reviewer_title ?? '' ) );
	$location = trim( (string) ( $r->division_category ?? '' ) );
	if ( '' !== $role && '' !== $location ) {
		$meta = sprintf( '%s in %s', $role, $location );
	} else {
		$meta = '' !== $role ? $role : $location;
	}

	// Initials from the first two words of the name, for the no-photo fallback.
	$initials = '';
	foreach ( array_slice( preg_split( '/\s+/', trim( (string) $r->reviewer_name ) ), 0, 2 ) as $part ) {
		if ( '' !== $part ) { $initials .= mb_substr( $part, 0, 1 ); }
	}
	$initials = mb_strtoupper( $initials );

	ob_start();
	?>
<div id="<?php echo esc_attr( $uid ); ?>" class="ah-review-card ah-review-card--spotlight">
	<div class="ah-rv-main">
		<div class="ah-rv-stars"><?php echo esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) ); ?></div>
		<p class="ah-rv-quote"><?php echo esc_html( $quote ); ?></p>
		<div class="ah-rv-name"><?php echo esc_html( $r->reviewer_name ); ?></div>
		<?php if ( '' !== $meta ) : ?>
			<div class="ah-rv-meta"><?php echo esc_html( $meta ); ?></div>
		<?php endif; ?>
	</div>
	<?php if ( $img_url ) : ?>
		<img class="ah-rv-portrait" src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $r->reviewer_name ); ?>" loading="lazy">
	<?php elseif ( '' !== $initials ) : ?>
		<div class="ah-rv-portrait ah-rv-portrait--initials" aria-hidden="true"><?php echo esc_html( $initials ); ?></div>
	<?php endif; ?>
</div>
	<?php
	return (string) ob_get_clean();
}
