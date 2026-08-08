<?php
/**
 * components/split-feature.php - alternating image / text rows.
 *
 * GENERIC: the workhorse "explain three things properly" band. Each row is a
 * photo on one side and copy on the other, flipping sides as you go down so
 * the page has a rhythm instead of a column of identical cards.
 *
 * Data: admin/data/<source>.json (default `split_feature`)
 *   { tag, title, sub, rows[] {
 *       kicker, title, text (string or array), points[],
 *       image, image_alt, caption, flip (force this row's side),
 *       link_label, link_url, dialog
 *   } }
 *
 * Args:
 *   source string  Which JSON file to read.
 */

defined( 'ABSPATH' ) || exit;

$nt_src  = ( isset( $source ) && $source ) ? (string) $source : 'split_feature';
$nt_data = app_data( $nt_src );
$nt_rows = ( is_array( $nt_data ) && ! empty( $nt_data['rows'] ) ) ? (array) $nt_data['rows'] : array();
if ( empty( $nt_rows ) ) {
	return;
}

$nt_tag   = (string) ( $nt_data['tag'] ?? '' );
$nt_title = (string) ( $nt_data['title'] ?? '' );
$nt_sub   = (string) ( $nt_data['sub'] ?? '' );
?>
<section class="app-split" id="<?php echo esc_attr( sanitize_html_class( $nt_src ) ); ?>">
	<div class="container">

		<?php if ( $nt_tag || $nt_title || $nt_sub ) : ?>
			<div class="app-section-center">
				<?php if ( $nt_tag ) : ?><div class="app-section-tag"><?php echo esc_html( $nt_tag ); ?></div><?php endif; ?>
				<?php if ( $nt_title ) : ?><h2 class="section-title"><?php echo wp_kses( $nt_title, array( 'em' => array() ) ); ?></h2><?php endif; ?>
				<?php if ( $nt_sub ) : ?><p class="section-body"><?php echo esc_html( $nt_sub ); ?></p><?php endif; ?>
			</div>
		<?php endif; ?>

		<?php
		foreach ( $nt_rows as $nt_i => $nt_row ) :
			$nt_row      = (array) $nt_row;
			$nt_row_name = trim( (string) ( $nt_row['title'] ?? '' ) );
			if ( '' === $nt_row_name ) {
				continue;
			}
			// Odd rows flip automatically; `flip` in the JSON overrides that.
			$nt_flip   = isset( $nt_row['flip'] ) ? ! empty( $nt_row['flip'] ) : ( 1 === $nt_i % 2 );
			$nt_dialog = (string) ( $nt_row['dialog'] ?? '' );
			?>
			<article class="app-split__row<?php echo $nt_flip ? ' is-flipped' : ''; ?> fade-up">

				<?php if ( ! empty( $nt_row['image'] ) ) : ?>
					<figure class="app-split__figure">
						<img src="<?php echo esc_url( app_link( (string) $nt_row['image'] ) ); ?>"
						     alt="<?php echo esc_attr( $nt_row['image_alt'] ?? '' ); ?>"
						     loading="lazy" decoding="async">
						<?php if ( ! empty( $nt_row['caption'] ) ) : ?>
							<figcaption class="app-split__caption"><?php echo esc_html( $nt_row['caption'] ); ?></figcaption>
						<?php endif; ?>
					</figure>
				<?php endif; ?>

				<div class="app-split__copy">
					<?php if ( ! empty( $nt_row['kicker'] ) ) : ?>
						<span class="app-split__kicker"><?php echo esc_html( $nt_row['kicker'] ); ?></span>
					<?php endif; ?>

					<h3 class="app-split__title"><?php echo wp_kses( $nt_row_name, array( 'em' => array() ) ); ?></h3>

					<?php foreach ( (array) ( $nt_row['text'] ?? array() ) as $nt_para ) : ?>
						<p class="app-split__text"><?php echo esc_html( $nt_para ); ?></p>
					<?php endforeach; ?>

					<?php if ( ! empty( $nt_row['points'] ) ) : ?>
						<ul class="app-split__points">
							<?php foreach ( (array) $nt_row['points'] as $nt_point ) : ?>
								<li><?php NT_Icons::render( 'check' ); ?><span><?php echo esc_html( $nt_point ); ?></span></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>

					<?php if ( ! empty( $nt_row['link_label'] ) ) : ?>
						<?php if ( '' !== $nt_dialog && NT_Dialog::exists( $nt_dialog ) ) : ?>
							<button class="app-split__cta" <?php app_dialog_trigger( $nt_dialog ); ?>>
								<?php echo esc_html( $nt_row['link_label'] ); ?>
								<?php NT_Icons::render( 'arrow-right' ); ?>
							</button>
						<?php elseif ( ! empty( $nt_row['link_url'] ) ) : ?>
							<a class="app-split__cta" href="<?php echo esc_url( app_link( (string) $nt_row['link_url'] ) ); ?>">
								<?php echo esc_html( $nt_row['link_label'] ); ?>
								<?php NT_Icons::render( 'arrow-right' ); ?>
							</a>
						<?php endif; ?>
					<?php endif; ?>
				</div>

			</article>
		<?php endforeach; ?>

	</div>
</section>
