<?php
defined( 'ABSPATH' ) || exit;

/**
 * "With Photos" review card - static, no flip/interaction, full-width (one
 * per row). A flexible photo grid up top showing EVERY photo (no 4-photo
 * cap, no "+N" overflow badge - each tile opens the shared lightbox, which
 * itself has prev/next to step through the rest of this review's gallery).
 * Review text supports HTML (wp_kses_post - same trust model as Full Story;
 * already sanitized on save in admin/pages/reviews.php's wp_editor field).
 * Long text collapses to a short excerpt with a "Read more" toggle (native
 * <details>, no JS) so one long review doesn't dominate the card. Used by
 * AH_Reviews_Model::render_review() for representing = 'with_photos'.
 */
function ah_review_render_with_photos( object $r ): string {
	$uid          = 'ah_review_' . (int) $r->id;
	$rating       = max( 0, min( 5, (int) $r->rating ) );
	$gallery      = ( new AH_Reviews_Model() )->get_images( (int) $r->id );
	$plain_text   = wp_strip_all_tags( (string) $r->review_text );
	$needs_toggle = strlen( $plain_text ) > 320;

	ob_start();
	?>
<div id="<?php echo esc_attr( $uid ); ?>" class="ah-review-card ah-review-card--with-photos">
	<?php if ( ! empty( $gallery ) ) : ?>
		<div class="ah-rv-wp-grid">
			<?php foreach ( $gallery as $g ) :
				$g_url      = wp_get_attachment_image_url( (int) $g->image_id, 'medium' );
				$g_url_full = wp_get_attachment_image_url( (int) $g->image_id, 'large' );
				if ( ! $g_url ) continue;
			?>
				<div class="ah-rv-wp-tile">
					<img class="ah-rv-lightbox-trigger" src="<?php echo esc_url( $g_url ); ?>" data-full="<?php echo esc_url( $g_url_full ?: $g_url ); ?>" alt="" loading="lazy">
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<div class="ah-rv-wp-body">
		<div class="ah-rv-wp-head">
			<div class="ah-rv-name"><?php echo esc_html( $r->reviewer_name ); ?></div>
			<?php if ( ! empty( $r->reviewer_title ) ) : ?><div class="ah-rv-title"><?php echo esc_html( $r->reviewer_title ); ?></div><?php endif; ?>
		</div>
		<div class="ah-rv-wp-meta">
			<div class="ah-rv-stars"><?php echo esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) ); ?></div>
			<?php if ( ! empty( $gallery ) ) : ?>
				<span class="ah-rv-photo-badge">📷 <?php echo (int) count( $gallery ); ?></span>
			<?php endif; ?>
		</div>
		<?php if ( $needs_toggle ) : ?>
			<details class="ah-rv-wp-details">
				<summary class="ah-rv-wp-preview"><?php echo esc_html( wp_trim_words( $plain_text, 30 ) ); ?> <span class="ah-rv-wp-more"><?php esc_html_e( 'Read more →', 'ah-cms' ); ?></span></summary>
				<div class="ah-rv-text"><?php echo wp_kses_post( (string) $r->review_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- re-sanitized here, already wp_kses_post()'d at save time in admin/pages/reviews.php. ?></div>
			</details>
		<?php else : ?>
			<div class="ah-rv-text"><?php echo wp_kses_post( (string) $r->review_text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- re-sanitized here, already wp_kses_post()'d at save time in admin/pages/reviews.php. ?></div>
		<?php endif; ?>
	</div>
</div>
	<?php
	return (string) ob_get_clean() . ah_review_render_lightbox_once();
}
