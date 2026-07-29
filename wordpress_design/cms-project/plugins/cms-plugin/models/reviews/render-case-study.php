<?php
defined( 'ABSPATH' ) || exit;

/**
 * "Case Study" review card - full-bleed cover photo, headline, short
 * description, and a "Tap to See Results" reveal (native <details>, no JS)
 * that expands to show the rating + quote + name. Used by
 * AH_Reviews_Model::render_review() for representing = 'case_study'.
 */
function ah_review_render_case_study( object $r ): string {
	$uid     = 'ah_review_' . (int) $r->id;
	$gallery = ( new AH_Reviews_Model() )->get_images( (int) $r->id );

	$cover_url = '';
	if ( ! empty( $gallery ) ) {
		$cover_url = wp_get_attachment_image_url( (int) $gallery[0]->image_id, 'medium_large' );
	}
	if ( ! $cover_url && ! empty( $r->reviewer_image_id ) ) {
		$cover_url = wp_get_attachment_image_url( (int) $r->reviewer_image_id, 'medium_large' );
	}

	$title  = ! empty( $r->story_title ) ? (string) $r->story_title : (string) $r->reviewer_name;
	$rating = max( 0, min( 5, (int) $r->rating ) );

	ob_start();
	?>
<div id="<?php echo esc_attr( $uid ); ?>" class="ah-review-card ah-review-card--case-study">
	<?php if ( $cover_url ) : ?>
		<img class="ah-rv-cs-cover" src="<?php echo esc_url( $cover_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" loading="lazy">
	<?php endif; ?>
	<div class="ah-rv-cs-body">
		<div class="ah-rv-cs-title"><?php echo esc_html( $title ); ?></div>
		<?php if ( ! empty( $r->short_desc ) ) : ?>
			<p class="ah-rv-cs-desc"><?php echo esc_html( $r->short_desc ); ?></p>
		<?php endif; ?>
		<details class="ah-rv-cs-details">
			<summary class="ah-rv-cs-cta"><?php esc_html_e( 'Tap to See Results', 'ah-cms' ); ?> &rarr;</summary>
			<div class="ah-rv-cs-result">
				<div class="ah-rv-stars"><?php echo esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) ); ?></div>
				<p class="ah-rv-text">&ldquo;<?php echo esc_html( wp_strip_all_tags( (string) $r->review_text ) ); ?>&rdquo;</p>
				<div class="ah-rv-name"><?php echo esc_html( $r->reviewer_name ); ?></div>
			</div>
		</details>
	</div>
</div>
	<?php
	return (string) ob_get_clean();
}
