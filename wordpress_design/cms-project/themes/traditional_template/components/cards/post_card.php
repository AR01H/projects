<?php
/**
 * Post card. Context: 'post_id' (int, required).
 *
 *   nt_component( 'cards/post_card', array( 'post_id' => get_the_ID() ) );
 *
 * Keep the markup in sync with the JS renderer in assets/js/pages/news.js
 * (both produce .nt-card items).
 */

defined( 'ABSPATH' ) || exit;

$post_id = isset( $post_id ) ? (int) $post_id : get_the_ID();
if ( ! $post_id ) {
	return;
}
$nt_thumb = get_the_post_thumbnail_url( $post_id, 'nt-card' );
?>
<article class="nt-card">
	<a class="nt-card-link" href="<?php echo esc_url( get_permalink( $post_id ) ); ?>">
		<div class="nt-card-media">
			<?php if ( $nt_thumb ) : ?>
				<img src="<?php echo esc_url( $nt_thumb ); ?>" alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" loading="lazy">
			<?php else : ?>
				<span class="nt-card-media-empty" aria-hidden="true"></span>
			<?php endif; ?>
		</div>
		<div class="nt-card-body">
			<h3 class="nt-card-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></h3>
			<p class="nt-card-excerpt"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $post_id ) ), 18, '…' ) ); ?></p>
			<span class="nt-card-meta"><?php echo esc_html( get_the_date( '', $post_id ) ); ?></span>
		</div>
	</a>
</article>
