<?php
defined( 'ABSPATH' ) || exit;

/**
 * Carousel slide card - a fixed width so several sit side by side in a
 * horizontal track. Review text is clamped to 4 lines with an ellipsis
 * (CSS line-clamp, backed by a server-side word-trim fallback for browsers
 * that don't support it). Used by AH_Reviews_Model::render_carousel_card().
 */
function ah_review_render_carousel_card( object $r ): string {
	$uid     = 'ah_review_car_' . (int) $r->id;
	$img_url = ! empty( $r->reviewer_image_id ) ? wp_get_attachment_image_url( (int) $r->reviewer_image_id, 'thumbnail' ) : '';
	$rating   = max( 0, min( 5, (int) $r->rating ) );
	$full_text = wp_strip_all_tags( (string) $r->review_text );
	// Belt-and-braces cap for browsers without line-clamp support - the CSS
	// clamp below is what actually enforces "4 lines" visually.
	$text = wp_trim_words( $full_text, 40 );

	ob_start();
	?>
<style>
#<?php echo esc_attr( $uid ); ?>{--rv-accent:var(--client-color,#1a3c5e);--rv-border:rgba(28,22,15,.10);--rv-ink:#1c1917;--rv-muted:#6b6259;width:300px;flex-shrink:0;box-sizing:border-box;padding:22px 24px;border:1px solid var(--rv-border);border-radius:14px;background:#fff;font-family:inherit;box-shadow:0 4px 18px rgba(28,22,15,.05);display:flex;flex-direction:column;height:100%}
#<?php echo esc_attr( $uid ); ?> .ah-rv-head{display:flex;align-items:center;gap:12px;margin-bottom:12px}
#<?php echo esc_attr( $uid ); ?> .ah-rv-avatar{width:64px;aspect-ratio:16/9;height:auto;border-radius:7px;object-fit:cover;flex-shrink:0;box-shadow:0 0 0 3px #fff,0 0 0 5px rgba(245,158,11,.28)}
#<?php echo esc_attr( $uid ); ?> .ah-rv-name{font-weight:700;font-size:14.5px;color:var(--rv-accent);line-height:1.3}
#<?php echo esc_attr( $uid ); ?> .ah-rv-title{font-size:11.5px;color:var(--rv-muted)}
#<?php echo esc_attr( $uid ); ?> .ah-rv-stars{color:#f59e0b;font-size:13px;letter-spacing:1px;margin-bottom:8px}
#<?php echo esc_attr( $uid ); ?> .ah-rv-text{font-size:13px;line-height:1.6;color:var(--rv-ink);margin:0;display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;overflow:hidden}
#<?php echo esc_attr( $uid ); ?> .ah-rv-text::before{content:"\201C";font-family:Georgia,"Times New Roman",serif;font-size:1.8em;line-height:0;color:rgba(245,158,11,.4);vertical-align:-.28em;margin-right:2px}
</style>
<div id="<?php echo esc_attr( $uid ); ?>" class="ah-review-card ah-review-card--carousel">
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
	<p class="ah-rv-text" title="<?php echo esc_attr( $full_text ); ?>"><?php echo esc_html( $text ); ?></p>
</div>
	<?php
	return (string) ob_get_clean();
}
