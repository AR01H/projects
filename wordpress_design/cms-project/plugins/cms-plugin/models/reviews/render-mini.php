<?php
defined( 'ABSPATH' ) || exit;

/**
 * "Mini Card" review card - compact, avatar, name, stars, truncated quote. No photos.
 * Used by AH_Reviews_Model::render_review() for representing = 'mini_card'.
 */
function ah_review_render_mini( object $r ): string {
	$uid     = 'ah_review_' . (int) $r->id;
	$img_url = ! empty( $r->reviewer_image_id ) ? wp_get_attachment_image_url( (int) $r->reviewer_image_id, 'thumbnail' ) : '';
	$rating  = max( 0, min( 5, (int) $r->rating ) );
	$excerpt = wp_trim_words( wp_strip_all_tags( (string) $r->review_text ), 14 );

	ob_start();
	?>
<style>
#<?php echo esc_attr( $uid ); ?>{--rv-accent:var(--client-color,#1a3c5e);--rv-border:rgba(28,22,15,.10);--rv-muted:#6b6259;display:flex;align-items:center;gap:11px;max-width:340px;padding:12px 16px;border:1px solid var(--rv-border);border-radius:12px;background:#fff;font-family:inherit;box-sizing:border-box;box-shadow:0 2px 10px rgba(28,22,15,.04);transition:box-shadow .2s ease}
#<?php echo esc_attr( $uid ); ?>:hover{box-shadow:0 8px 22px rgba(28,22,15,.08)}
#<?php echo esc_attr( $uid ); ?> .ah-rv-avatar{width:56px;aspect-ratio:16/9;height:auto;border-radius:6px;object-fit:cover;flex-shrink:0;box-shadow:0 0 0 2px #fff,0 0 0 4px rgba(245,158,11,.25)}
#<?php echo esc_attr( $uid ); ?> .ah-rv-name{font-weight:700;font-size:13px;color:var(--rv-accent);line-height:1.3}
#<?php echo esc_attr( $uid ); ?> .ah-rv-stars{color:#f59e0b;font-size:11px;letter-spacing:1px;margin:2px 0 4px}
#<?php echo esc_attr( $uid ); ?> .ah-rv-text{font-size:12px;line-height:1.5;color:var(--rv-muted);margin:0;font-style:italic}
</style>
<div id="<?php echo esc_attr( $uid ); ?>" class="ah-review-card ah-review-card--mini">
	<?php if ( $img_url ) : ?>
		<img class="ah-rv-avatar" src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $r->reviewer_name ); ?>">
	<?php endif; ?>
	<div>
		<div class="ah-rv-name"><?php echo esc_html( $r->reviewer_name ); ?></div>
		<div class="ah-rv-stars"><?php echo esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) ); ?></div>
		<p class="ah-rv-text">&ldquo;<?php echo esc_html( $excerpt ); ?>&rdquo;</p>
	</div>
</div>
	<?php
	return (string) ob_get_clean();
}
