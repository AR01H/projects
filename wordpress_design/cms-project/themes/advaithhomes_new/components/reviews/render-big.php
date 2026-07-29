<?php
defined( 'ABSPATH' ) || exit;

/**
 * "Big Box" review card - a flip card. Front: avatar, name/title, stars,
 * mini description teaser. Back (hover on desktop, tap on mobile): the full
 * review text on a gold gradient. Used by AH_Reviews_Model::render_review()
 * for representing = 'big_box' (the default).
 *
 * Theme-level override of plugins/cms-plugin/models/reviews/render-big.php -
 * see render-big.php's own header comment for how the override resolves.
 */
function ah_review_render_big( object $r ): string {
	$uid     = 'ah_review_' . (int) $r->id;
	$img_url = ! empty( $r->reviewer_image_id ) ? wp_get_attachment_image_url( (int) $r->reviewer_image_id, 'thumbnail' ) : '';
	$rating  = max( 0, min( 5, (int) $r->rating ) );

	ob_start();
	?>
<div id="<?php echo esc_attr( $uid ); ?>" class="ah-review-card ah-review-card--big ah-rv-flip">
	<div class="ah-rv-flip-inner">
		<div class="ah-rv-flip-front">
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
			<?php if ( ! empty( $r->short_desc ) ) : ?>
				<p class="ah-rv-mini-desc"><?php echo esc_html( $r->short_desc ); ?></p>
			<?php endif; ?>
			<span class="ah-rv-flip-hint"><?php esc_html_e( 'Tap to read the full review →', 'ah-cms' ); ?></span>
		</div>
		<div class="ah-rv-flip-back">
			<p class="ah-rv-text"><?php echo esc_html( wp_strip_all_tags( (string) $r->review_text ) ); ?></p>
			<div class="ah-rv-name"><?php echo esc_html( $r->reviewer_name ); ?></div>
		</div>
	</div>
</div>
	<?php
	return (string) ob_get_clean() . ah_review_render_flip_script_once();
}
