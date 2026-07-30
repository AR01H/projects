<?php
defined( 'ABSPATH' ) || exit;

/**
 * "Big Box" review card - a flip card. Front: a cover photo (the review's
 * first gallery image, or a gold gradient panel with the name centered if
 * there isn't one) + story title + mini description + a "Tap to see
 * results" hint. Back (hover on desktop, tap on mobile): "The Outcome" -
 * the full review text as an italic quote, then a stat breakdown parsed
 * from the admin's Highlight Stat(s) field (AH_Reviews_Model::parse_stat_lines()).
 * Used by AH_Reviews_Model::render_review() for representing = 'big_box'
 * (the default).
 *
 * Theme-level override of plugins/cms-plugin/models/reviews/render-big.php -
 * see this same file's header comment in the plugin for how the override resolves.
 */
function ah_review_render_big( object $r ): string {
	$uid       = 'ah_review_' . (int) $r->id;
	$gallery   = ( new AH_Reviews_Model() )->get_images( (int) $r->id );
	$cover_url = ! empty( $gallery ) ? wp_get_attachment_image_url( (int) $gallery[0]->image_id, 'medium' ) : '';
	$title     = ! empty( $r->story_title ) ? (string) $r->story_title : (string) $r->reviewer_name;
	$quote     = wp_strip_all_tags( (string) $r->review_text );
	$stats     = AH_Reviews_Model::parse_stat_lines( (string) ( $r->stat_line ?? '' ) );

	ob_start();
	?>
<div id="<?php echo esc_attr( $uid ); ?>" class="ah-review-card ah-review-card--big ah-rv-flip">
	<div class="ah-rv-flip-inner">
		<div class="ah-rv-flip-front<?php echo $cover_url ? '' : ' ah-rv-flip-front--no-photo'; ?>">
			<?php if ( $cover_url ) : ?>
				<img class="ah-rv-bb-cover" src="<?php echo esc_url( $cover_url ); ?>" alt="" loading="lazy">
			<?php else : ?>
				<div class="ah-rv-name-fallback" aria-hidden="true"><?php echo esc_html( $r->reviewer_name ); ?></div>
			<?php endif; ?>
			<div class="ah-rv-bb-body">
				<div class="ah-rv-bb-title"><?php echo esc_html( $title ); ?></div>
				<?php if ( ! empty( $r->short_desc ) ) : ?>
					<p class="ah-rv-mini-desc"><?php echo esc_html( $r->short_desc ); ?></p>
				<?php endif; ?>
				<span class="ah-rv-flip-hint"><?php esc_html_e( 'Tap to see results →', 'ah-cms' ); ?></span>
			</div>
		</div>
		<div class="ah-rv-flip-back">
			<div class="ah-rv-bb-outcome-title"><?php esc_html_e( 'The Outcome', 'ah-cms' ); ?></div>
			<p class="ah-rv-text">&ldquo;<?php echo esc_html( $quote ); ?>&rdquo;</p>
			<?php if ( ! empty( $stats ) ) : ?>
				<div class="ah-rv-bb-stats">
					<?php foreach ( $stats as $stat ) : ?>
						<div class="ah-rv-bb-stat-row">
							<?php if ( '' !== $stat['label'] ) : ?><div class="ah-rv-bb-stat-label"><?php echo esc_html( strtoupper( $stat['label'] ) ); ?></div><?php endif; ?>
							<div class="ah-rv-bb-stat-value"><?php echo esc_html( $stat['value'] ); ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
	<?php
	return (string) ob_get_clean() . ah_review_render_flip_script_once();
}
