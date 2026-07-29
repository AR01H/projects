<?php
defined( 'ABSPATH' ) || exit;

/**
 * "Mini Card" review card - a flip card. Front: avatar, name, role, stars.
 * Back (hover on desktop, tap on mobile): the full, untruncated quote on a
 * gold gradient - no more 14-word trim, since flipping already reveals it
 * in full. Used by AH_Reviews_Model::render_review() for representing = 'mini_card'.
 *
 * Theme-level override of plugins/cms-plugin/models/reviews/render-mini.php -
 * see render-big.php's own header comment for how the override resolves.
 */
function ah_review_render_mini( object $r ): string {
	$uid     = 'ah_review_' . (int) $r->id;
	$img_url = ! empty( $r->reviewer_image_id ) ? wp_get_attachment_image_url( (int) $r->reviewer_image_id, 'thumbnail' ) : '';
	$rating  = max( 0, min( 5, (int) $r->rating ) );

	ob_start();
	?>
<div id="<?php echo esc_attr( $uid ); ?>" class="ah-review-card ah-review-card--mini ah-rv-flip">
	<div class="ah-rv-flip-inner">
		<div class="ah-rv-flip-front">
			<?php if ( $img_url ) : ?>
				<img class="ah-rv-avatar" src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $r->reviewer_name ); ?>">
			<?php endif; ?>
			<div>
				<div class="ah-rv-name"><?php echo esc_html( $r->reviewer_name ); ?></div>
				<?php if ( ! empty( $r->reviewer_title ) ) : ?>
					<div class="ah-rv-role"><?php echo esc_html( $r->reviewer_title ); ?></div>
				<?php endif; ?>
				<div class="ah-rv-stars"><?php echo esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) ); ?></div>
			</div>
		</div>
		<div class="ah-rv-flip-back">
			<p class="ah-rv-text">&ldquo;<?php echo esc_html( wp_strip_all_tags( (string) $r->review_text ) ); ?>&rdquo;</p>
			<div class="ah-rv-name"><?php echo esc_html( $r->reviewer_name ); ?></div>
		</div>
	</div>
</div>
	<?php
	return (string) ob_get_clean() . ah_review_render_flip_script_once();
}
