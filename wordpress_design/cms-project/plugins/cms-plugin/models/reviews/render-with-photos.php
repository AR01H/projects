<?php
defined( 'ABSPATH' ) || exit;

/**
 * "With Photos" review card - like the big card, plus a small strip of occasion
 * photos (capped, with a "+N" overflow tile). Used by AH_Reviews_Model::render_review()
 * for representing = 'with_photos'.
 */
function ah_review_render_with_photos( object $r ): string {
	$uid       = 'ah_review_' . (int) $r->id;
	$img_url   = ! empty( $r->reviewer_image_id ) ? wp_get_attachment_image_url( (int) $r->reviewer_image_id, 'thumbnail' ) : '';
	$rating    = max( 0, min( 5, (int) $r->rating ) );
	$gallery   = ( new AH_Reviews_Model() )->get_images( (int) $r->id );
	$max_strip = 4;
	$strip     = array_slice( $gallery, 0, $max_strip );
	$overflow  = max( 0, count( $gallery ) - $max_strip );

	ob_start();
	?>
<style>
#<?php echo esc_attr( $uid ); ?>{--rv-accent:var(--client-color,#1a3c5e);--rv-border:rgba(28,22,15,.10);--rv-ink:#1c1917;--rv-muted:#6b6259;max-width:560px;padding:24px 26px;border:1px solid var(--rv-border);border-radius:16px;background:#fff;font-family:inherit;box-sizing:border-box;box-shadow:0 4px 20px rgba(28,22,15,.05);transition:box-shadow .2s ease,transform .2s ease}
#<?php echo esc_attr( $uid ); ?>:hover{box-shadow:0 16px 36px rgba(28,22,15,.10);transform:translateY(-2px)}
#<?php echo esc_attr( $uid ); ?> .ah-rv-head{display:flex;align-items:center;gap:14px;margin-bottom:14px}
#<?php echo esc_attr( $uid ); ?> .ah-rv-avatar{width:78px;aspect-ratio:16/9;height:auto;border-radius:8px;object-fit:cover;flex-shrink:0;box-shadow:0 0 0 3px #fff,0 0 0 5px rgba(245,158,11,.28)}
#<?php echo esc_attr( $uid ); ?> .ah-rv-name{font-weight:700;font-size:15.5px;color:var(--rv-accent);line-height:1.3}
#<?php echo esc_attr( $uid ); ?> .ah-rv-title{font-size:12.5px;color:var(--rv-muted)}
#<?php echo esc_attr( $uid ); ?> .ah-rv-stars{color:#f59e0b;font-size:14px;letter-spacing:1px;margin-bottom:10px}
#<?php echo esc_attr( $uid ); ?> .ah-rv-text{font-size:14px;line-height:1.7;color:var(--rv-ink);margin:0 0 14px}
#<?php echo esc_attr( $uid ); ?> .ah-rv-strip{display:grid;grid-template-columns:repeat(<?php echo (int) max( 1, count( $strip ) ); ?>,1fr);gap:7px}
#<?php echo esc_attr( $uid ); ?> .ah-rv-strip img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:9px;display:block;transition:transform .2s ease}
#<?php echo esc_attr( $uid ); ?> .ah-rv-strip img:hover{transform:scale(1.05)}
#<?php echo esc_attr( $uid ); ?> .ah-rv-strip-more{position:relative}
#<?php echo esc_attr( $uid ); ?> .ah-rv-strip-more::after{content:attr(data-more);position:absolute;inset:0;background:rgba(28,22,15,.55);color:#fff;font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;border-radius:9px}
</style>
<div id="<?php echo esc_attr( $uid ); ?>" class="ah-review-card ah-review-card--with-photos">
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
	<?php if ( ! empty( $strip ) ) : ?>
		<div class="ah-rv-strip">
			<?php foreach ( $strip as $i => $g ) :
				$g_url      = wp_get_attachment_image_url( (int) $g->image_id, 'thumbnail' );
				$g_url_full = wp_get_attachment_image_url( (int) $g->image_id, 'large' );
				if ( ! $g_url ) continue;
				$is_last = ( $i === count( $strip ) - 1 && $overflow > 0 );
			?>
				<span<?php echo $is_last ? ' class="ah-rv-strip-more" data-more="+' . (int) $overflow . '"' : ''; ?>>
					<img class="ah-rv-lightbox-trigger" src="<?php echo esc_url( $g_url ); ?>" data-full="<?php echo esc_url( $g_url_full ?: $g_url ); ?>" alt="" loading="lazy">
				</span>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
	<?php
	return (string) ob_get_clean() . ah_review_render_lightbox_once();
}
