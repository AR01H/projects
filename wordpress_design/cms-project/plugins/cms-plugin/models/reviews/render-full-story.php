<?php
defined( 'ABSPATH' ) || exit;

/**
 * "Full Story" review card - larger spotlight layout: full rich review text +
 * the complete occasion image gallery. Used by AH_Reviews_Model::render_review()
 * for representing = 'full_story'.
 */
function ah_review_render_full_story( object $r ): string {
	$uid     = 'ah_review_' . (int) $r->id;
	$img_url = ! empty( $r->reviewer_image_id ) ? wp_get_attachment_image_url( (int) $r->reviewer_image_id, 'thumbnail' ) : '';
	$rating  = max( 0, min( 5, (int) $r->rating ) );
	$gallery = ( new AH_Reviews_Model() )->get_images( (int) $r->id );

	ob_start();
	?>
<style>
#<?php echo esc_attr( $uid ); ?>{--rv-accent:var(--client-color,#1a3c5e);--rv-border:rgba(28,22,15,.10);--rv-ink:#1c1917;--rv-muted:#6b6259;width:100%;padding:32px 34px;border:1px solid var(--rv-border);border-radius:18px;background:#fff;font-family:inherit;box-sizing:border-box;box-shadow:0 6px 26px rgba(28,22,15,.06);transition:box-shadow .2s ease}
#<?php echo esc_attr( $uid ); ?>:hover{box-shadow:0 18px 40px rgba(28,22,15,.11)}
#<?php echo esc_attr( $uid ); ?> .ah-rv-head{display:flex;align-items:center;gap:16px;margin-bottom:14px}
#<?php echo esc_attr( $uid ); ?> .ah-rv-avatar{width:96px;aspect-ratio:16/9;height:auto;border-radius:9px;object-fit:cover;flex-shrink:0;box-shadow:0 0 0 3px #fff,0 0 0 5px rgba(245,158,11,.3)}
#<?php echo esc_attr( $uid ); ?> .ah-rv-name{font-weight:700;font-size:19px;color:var(--rv-accent);line-height:1.3}
#<?php echo esc_attr( $uid ); ?> .ah-rv-title{font-size:13px;color:var(--rv-muted)}
#<?php echo esc_attr( $uid ); ?> .ah-rv-stars{color:#f59e0b;font-size:17px;letter-spacing:2px;padding-bottom:14px;margin-bottom:16px;border-bottom:1px solid var(--rv-border)}
#<?php echo esc_attr( $uid ); ?> .ah-rv-text{font-size:15.5px;line-height:1.85;color:var(--rv-ink);margin:0 0 20px;position:relative}
#<?php echo esc_attr( $uid ); ?> .ah-rv-text::before{content:"\201C";position:absolute;top:-.28em;left:-.06em;font-family:Georgia,"Times New Roman",serif;font-size:3em;color:rgba(245,158,11,.35)}
#<?php echo esc_attr( $uid ); ?> .ah-rv-text-inner{display:block;padding-left:1.3em}
#<?php echo esc_attr( $uid ); ?> .ah-rv-text p{margin:0 0 12px}
#<?php echo esc_attr( $uid ); ?> .ah-rv-gallery{display:grid;grid-template-columns:repeat(auto-fill,110px);gap:9px}
#<?php echo esc_attr( $uid ); ?> .ah-rv-gallery img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:9px;transition:transform .2s ease}
#<?php echo esc_attr( $uid ); ?> .ah-rv-gallery img:hover{transform:scale(1.05)}
</style>
<div id="<?php echo esc_attr( $uid ); ?>" class="ah-review-card ah-review-card--full-story">
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
