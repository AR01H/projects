<?php
/**
 * components/cards/post_card.php - one article in a listing.
 *
 * A pressed-paper journal card: a sepia photo up top, then the date, the
 * title, a couple of lines of the piece and how long it takes to read.
 *
 * It fills whatever grid cell it is dropped into, which is what the previous
 * version did not - it carried a fixed width from the old theme and sat in
 * the corner of its column looking broken.
 *
 * A post with no featured image gets a drawn placeholder in the palette
 * rather than an emoji, so an unillustrated blog still looks deliberate.
 *
 * Context:
 *   post_id (int)    Defaults to the current post in the loop.
 *   compact (bool)   Tighter card for sidebars and related-post rows.
 *
 * If a future page renders cards client-side (e.g. a live-search results
 * list), keep that JS output producing the same .app-card markup as this file.
 */

defined( 'ABSPATH' ) || exit;

$nt_pid = isset( $post_id ) ? (int) $post_id : get_the_ID();
if ( ! $nt_pid ) {
	return;
}

$nt_compact = ! empty( $compact );
$nt_thumb   = get_the_post_thumbnail_url( $nt_pid, 'large' );
$nt_terms   = get_the_category( $nt_pid );
$nt_cat     = ( ! empty( $nt_terms ) && ! is_wp_error( $nt_terms ) ) ? $nt_terms[0]->name : '';

// Reading time from the real word count, rounded up, minimum one minute.
$nt_words   = str_word_count( wp_strip_all_tags( (string) get_post_field( 'post_content', $nt_pid ) ) );
$nt_minutes = max( 1, (int) ceil( $nt_words / 200 ) );
$nt_read    = sprintf( NT_Ui::label( 'minutes_read', '%s min read' ), (string) $nt_minutes );
?>
<article class="app-card<?php echo $nt_compact ? ' app-card--compact' : ''; ?>">
	<a class="app-card-link" href="<?php echo esc_url( get_permalink( $nt_pid ) ); ?>">

		<div class="app-card-media">
			<?php if ( $nt_thumb ) : ?>
				<img class="app-card-img" src="<?php echo esc_url( $nt_thumb ); ?>"
				     alt="" loading="lazy" decoding="async">
			<?php else : ?>
				<span class="app-card-media-empty" aria-hidden="true">
					<?php NT_Icons::render( 'wheat' ); ?>
				</span>
			<?php endif; ?>

			<?php if ( '' !== $nt_cat ) : ?>
				<span class="app-card-cat"><?php echo esc_html( $nt_cat ); ?></span>
			<?php endif; ?>
		</div>

		<div class="app-card-body">
			<p class="app-card-meta">
				<span class="app-card-meta__item">
					<?php NT_Icons::render( 'calendar' ); ?>
					<?php echo esc_html( get_the_date( '', $nt_pid ) ); ?>
				</span>
				<span class="app-card-meta__item">
					<?php NT_Icons::render( 'clock' ); ?>
					<?php echo esc_html( $nt_read ); ?>
				</span>
			</p>

			<h3 class="app-card-title"><?php echo esc_html( get_the_title( $nt_pid ) ); ?></h3>

			<p class="app-card-excerpt">
				<?php echo esc_html( wp_trim_words( wp_strip_all_tags( get_the_excerpt( $nt_pid ) ), $nt_compact ? 12 : 22, '…' ) ); ?>
			</p>

			<span class="app-card-more">
				<?php echo esc_html( NT_Ui::label( 'read_more' ) ); ?>
				<?php NT_Icons::render( 'arrow-right' ); ?>
			</span>
		</div>
	</a>
</article>
