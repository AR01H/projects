<?php
defined( 'ABSPATH' ) || exit;

/**
 * "With Photos" review card - static, no flip/interaction. A photo grid
 * collage up top (up to 4 tiles - a tall left tile + 2 stacked for 3
 * photos, a 2x2 grid for 4+ with a "+N" overflow badge on the last tile,
 * each tile opens the shared lightbox) followed by the full review text
 * underneath - nothing hidden behind a click/hover. Used by
 * AH_Reviews_Model::render_review() for representing = 'with_photos'.
 *
 * Theme-level override of plugins/cms-plugin/models/reviews/render-with-photos.php -
 * see render-big.php's own header comment for how the override resolves.
 */
function ah_review_render_with_photos( object $r ): string {
	$uid        = 'ah_review_' . (int) $r->id;
	$rating     = max( 0, min( 5, (int) $r->rating ) );
	$gallery    = ( new AH_Reviews_Model() )->get_images( (int) $r->id );
	$grid_count = min( 4, count( $gallery ) );
	$overflow   = max( 0, count( $gallery ) - 4 );

	ob_start();
	?>
<div id="<?php echo esc_attr( $uid ); ?>" class="ah-review-card ah-review-card--with-photos">
	<?php if ( $grid_count > 0 ) : ?>
		<div class="ah-rv-wp-grid ah-rv-wp-grid--<?php echo (int) $grid_count; ?>">
			<?php for ( $i = 0; $i < $grid_count; $i++ ) :
				$g          = $gallery[ $i ];
				$g_url      = wp_get_attachment_image_url( (int) $g->image_id, 'medium' );
				$g_url_full = wp_get_attachment_image_url( (int) $g->image_id, 'large' );
				if ( ! $g_url ) continue;
				$is_last_with_more = ( $i === $grid_count - 1 && $overflow > 0 );
			?>
				<div class="ah-rv-wp-tile<?php echo $is_last_with_more ? ' ah-rv-wp-tile--more' : ''; ?>"<?php echo $is_last_with_more ? ' data-more="+' . (int) $overflow . '"' : ''; ?>>
					<img class="ah-rv-lightbox-trigger" src="<?php echo esc_url( $g_url ); ?>" data-full="<?php echo esc_url( $g_url_full ?: $g_url ); ?>" alt="" loading="lazy">
				</div>
			<?php endfor; ?>
		</div>
	<?php endif; ?>
	<div class="ah-rv-wp-body">
		<div class="ah-rv-stars"><?php echo esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) ); ?></div>
		<div class="ah-rv-name"><?php echo esc_html( $r->reviewer_name ); ?></div>
		<?php if ( ! empty( $r->reviewer_title ) ) : ?><div class="ah-rv-title"><?php echo esc_html( $r->reviewer_title ); ?></div><?php endif; ?>
		<?php if ( ! empty( $gallery ) ) : ?>
			<span class="ah-rv-photo-badge">📷 <?php echo (int) count( $gallery ); ?></span>
		<?php endif; ?>
		<p class="ah-rv-text"><?php echo esc_html( wp_strip_all_tags( (string) $r->review_text ) ); ?></p>
	</div>
</div>
	<?php
	return (string) ob_get_clean() . ah_review_render_lightbox_once();
}
