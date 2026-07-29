<?php
defined( 'ABSPATH' ) || exit;

/**
 * "Full Story" review card - larger spotlight layout: full rich review text +
 * the complete occasion image gallery. Used by AH_Reviews_Model::render_review()
 * for representing = 'full_story'.
 *
 * Theme-level override of plugins/cms-plugin/models/reviews/render-full-story.php -
 * see render-big.php in this folder for how the override resolves.
 */
function ah_review_render_full_story( object $r ): string {
	$uid     = 'ah_review_' . (int) $r->id;
	$img_url = ! empty( $r->reviewer_image_id ) ? wp_get_attachment_image_url( (int) $r->reviewer_image_id, 'thumbnail' ) : '';
	$rating  = max( 0, min( 5, (int) $r->rating ) );
	$gallery = ( new AH_Reviews_Model() )->get_images( (int) $r->id );

	ob_start();
	?>
<div id="<?php echo esc_attr( $uid ); ?>" class="ah-review-card ah-review-card--full-story">
	<span class="ah-rv-tag"><?php esc_html_e( 'Full Story', 'ah-cms' ); ?></span>
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
	<div class="ah-rv-text"><span class="ah-rv-text-inner"><?php echo wp_kses_post( (string) $r->review_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- re-sanitized here, already wp_kses_post()'d at save time in admin/pages/reviews.php. ?></span></div>
	<?php if ( ! empty( $gallery ) ) : ?>
		<div class="ah-rv-gallery">
			<?php foreach ( $gallery as $g ) :
				$g_url      = wp_get_attachment_image_url( (int) $g->image_id, 'medium' );
				$g_url_full = wp_get_attachment_image_url( (int) $g->image_id, 'large' );
				if ( ! $g_url ) continue;
			?>
				<img class="ah-rv-lightbox-trigger" src="<?php echo esc_url( $g_url ); ?>" data-full="<?php echo esc_url( $g_url_full ?: $g_url ); ?>" alt="" loading="lazy">
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
	<?php
	return (string) ob_get_clean() . ah_review_render_lightbox_once();
}
