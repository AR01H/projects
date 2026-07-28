<?php
defined( 'ABSPATH' ) || exit;

/**
 * "Big Box" review card - full width, avatar, name/title, stars, review text. No photos.
 * Used by AH_Reviews_Model::render_review() for representing = 'big_box' (the default).
 */
function ah_review_render_big( object $r ): string {
	$uid     = 'ah_review_' . (int) $r->id;
	$img_url = ! empty( $r->reviewer_image_id ) ? wp_get_attachment_image_url( (int) $r->reviewer_image_id, 'thumbnail' ) : '';
	$rating  = max( 0, min( 5, (int) $r->rating ) );

	ob_start();
	?>
<style>
#<?php echo esc_attr( $uid ); ?>{--rv-accent:var(--client-color,#1a3c5e);--rv-border:rgba(28,22,15,.10);--rv-ink:#1c1917;--rv-muted:#6b6259;width:100%;box-sizing:border-box;padding:28px 30px;border:1px solid var(--rv-border);border-radius:16px;background:#fff;font-family:inherit;box-shadow:0 4px 20px rgba(28,22,15,.05);transition:box-shadow .2s ease,transform .2s ease}
#<?php echo esc_attr( $uid ); ?>:hover{box-shadow:0 16px 36px rgba(28,22,15,.10);transform:translateY(-2px)}
#<?php echo esc_attr( $uid ); ?> .ah-rv-head{display:flex;align-items:center;gap:14px;margin-bottom:14px}
#<?php echo esc_attr( $uid ); ?> .ah-rv-avatar{width:84px;aspect-ratio:16/9;height:auto;border-radius:8px;object-fit:cover;flex-shrink:0;box-shadow:0 0 0 3px #fff,0 0 0 5px rgba(245,158,11,.28)}
#<?php echo esc_attr( $uid ); ?> .ah-rv-name{font-weight:700;font-size:16.5px;color:var(--rv-accent);line-height:1.3}
#<?php echo esc_attr( $uid ); ?> .ah-rv-title{font-size:12.5px;color:var(--rv-muted)}
#<?php echo esc_attr( $uid ); ?> .ah-rv-stars{color:#f59e0b;font-size:14px;letter-spacing:1px;margin-bottom:10px}
#<?php echo esc_attr( $uid ); ?> .ah-rv-text{font-size:14.5px;line-height:1.75;color:var(--rv-ink);margin:0}
#<?php echo esc_attr( $uid ); ?> .ah-rv-text::before{content:"\201C";font-family:Georgia,"Times New Roman",serif;font-size:2.2em;line-height:0;color:rgba(245,158,11,.4);vertical-align:-.32em;margin-right:3px}
</style>
<div id="<?php echo esc_attr( $uid ); ?>" class="ah-review-card ah-review-card--big">
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
	<p class="ah-rv-text"><?php echo esc_html( wp_strip_all_tags( (string) $r->review_text ) ); ?></p>
</div>
	<?php
	return (string) ob_get_clean();
}
