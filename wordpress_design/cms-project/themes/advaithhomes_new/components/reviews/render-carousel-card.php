<?php
defined( 'ABSPATH' ) || exit;

/**
 * Carousel slide card - a fixed width so several sit side by side in a
 * horizontal track. Review text is clamped to 4 lines with an ellipsis.
 * Used by AH_Reviews_Model::render_carousel_card().
 *
 * Theme-level override of plugins/cms-plugin/models/reviews/render-carousel-card.php.
 */
function ah_review_render_carousel_card( object $r ): string {
	$uid       = 'ah_review_car_' . (int) $r->id;
	$img_url   = ! empty( $r->reviewer_image_id ) ? wp_get_attachment_image_url( (int) $r->reviewer_image_id, 'thumbnail' ) : '';
	$rating    = max( 0, min( 5, (int) $r->rating ) );
	$full_text = wp_strip_all_tags( (string) $r->review_text );
	$text      = wp_trim_words( $full_text, 40 );
	$needs_expand = trim( wp_trim_words( $full_text, 40, '' ) ) !== trim( $full_text );
	$initial   = ! empty( $r->reviewer_name ) ? strtoupper( mb_substr( (string) $r->reviewer_name, 0, 1 ) ) : 'A';

	ob_start();
	?>
<div id="<?php echo esc_attr( $uid ); ?>" class="ah-review-card ah-review-card--carousel">
	<?php /* Ambient decorative quote watermark */ ?>
	<span class="ah-rv-quote-mark" aria-hidden="true">&ldquo;</span>

	<div class="ah-rv-head">
		<div class="ah-rv-author-left">
			<?php if ( $img_url ) : ?>
				<img class="ah-rv-avatar" src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $r->reviewer_name ); ?>">
			<?php else : ?>
				<span class="ah-rv-avatar ah-rv-avatar--initial" aria-hidden="true"><?php echo esc_html( $initial ); ?></span>
			<?php endif; ?>
			<div class="ah-rv-author-info">
				<h4 class="ah-rv-name"><?php echo esc_html( $r->reviewer_name ); ?></h4>
				<?php if ( ! empty( $r->reviewer_title ) ) : ?>
					<div class="ah-rv-meta-row">
						<span class="ah-rv-title"><?php echo esc_html( $r->reviewer_title ); ?></span>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $needs_expand ) : ?>
			<button type="button" class="ah-rv-expand-icon-btn ah-rv-text-modal-trigger" aria-label="<?php echo esc_attr__( 'Read full story', 'ah-cms' ); ?>" title="<?php echo esc_attr__( 'Read full story', 'ah-cms' ); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<polyline points="15 3 21 3 21 9"/>
					<polyline points="9 21 3 21 3 15"/>
					<line x1="21" y1="3" x2="14" y2="10"/>
					<line x1="3" y1="21" x2="10" y2="14"/>
				</svg>
			</button>
		<?php endif; ?>
	</div>

	<div class="ah-rv-rating-strip" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: star rating out of 5 */ __( '%d out of 5 stars', 'ah-cms' ), $rating ) ); ?>">
		<div class="ah-rv-stars">
			<?php for ( $s = 1; $s <= 5; $s++ ) : ?>
				<span class="ah-rv-star <?php echo $s <= $rating ? 'ah-rv-star--filled' : 'ah-rv-star--empty'; ?>">★</span>
			<?php endfor; ?>
		</div>
		<span class="ah-rv-rating-score"><?php echo number_format( (float)$rating, 1 ); ?></span>
	</div>

	<div class="ah-rv-body">
		<p class="ah-rv-text" title="<?php echo esc_attr( $full_text ); ?>">
			&ldquo;<?php echo esc_html( $text ); ?>&rdquo;
			<?php if ( $needs_expand ) : ?>
				<button type="button" class="ah-rv-inline-more ah-rv-text-modal-trigger" aria-label="<?php echo esc_attr__( 'Read full story', 'ah-cms' ); ?>">Read more &rarr;</button>
			<?php endif; ?>
		</p>
	</div>

	<?php if ( $needs_expand ) : ?>
		<template class="ah-rv-text-modal-content">
			<span class="ah-rv-modal-quote-mark" aria-hidden="true">&ldquo;</span>
			<div class="ah-rv-modal-head">
				<?php if ( $img_url ) : ?>
					<img class="ah-rv-modal-avatar" src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $r->reviewer_name ); ?>">
				<?php else : ?>
					<span class="ah-rv-modal-avatar ah-rv-avatar--initial" aria-hidden="true"><?php echo esc_html( $initial ); ?></span>
				<?php endif; ?>
				<div class="ah-rv-modal-author-info">
					<h3 class="ah-rv-modal-name"><?php echo esc_html( $r->reviewer_name ); ?></h3>
					<?php if ( ! empty( $r->reviewer_title ) ) : ?>
						<div class="ah-rv-modal-meta-row">
							<span class="ah-rv-modal-title"><?php echo esc_html( $r->reviewer_title ); ?></span>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="ah-rv-modal-stars" aria-label="<?php echo esc_attr( sprintf( __( '%d out of 5 stars', 'ah-cms' ), $rating ) ); ?>">
				<?php for ( $s = 1; $s <= 5; $s++ ) : ?>
					<span class="ah-rv-star <?php echo $s <= $rating ? 'ah-rv-star--filled' : 'ah-rv-star--empty'; ?>">★</span>
				<?php endfor; ?>
			</div>
			<div class="ah-rv-modal-text"><?php echo wpautop( wp_kses_post( (string) $r->review_text ) ); ?></div>
		</template>
	<?php endif; ?>
</div>
	<?php
	return (string) ob_get_clean() . ( $needs_expand ? ah_review_render_text_modal_once() : '' );
}
