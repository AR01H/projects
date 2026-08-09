<?php
/**
 * components/downloads.php - a shelf of documents.
 *
 * GENERIC: brochures, price lists, allergen sheets, franchise packs, menus,
 * certificates, terms. Anything a visitor should be able to take away.
 *
 * Each row shows a file-type badge, a title, a short line, the size and a
 * download button. A file can also be gated behind a dialog (e.g. "give us
 * your email first") by naming a dialogs.json key instead of a URL.
 *
 * Data: admin/data/<source>.json (default `downloads`)
 *   { tag, title, sub, items[] {
 *       title, text, file, kind ("PDF"), size ("1.2 MB"),
 *       icon, dialog, updated, new_tab
 *   } }
 *
 * Args:
 *   source string  Which JSON file to read.
 */

defined( 'ABSPATH' ) || exit;

$nt_src   = ( isset( $source ) && $source ) ? (string) $source : 'downloads';
$nt_data  = App_Data_Provider::get( $nt_src );
$nt_items = ( is_array( $nt_data ) && ! empty( $nt_data['items'] ) ) ? (array) $nt_data['items'] : array();
if ( empty( $nt_items ) ) {
	return;
}

$nt_tag   = (string) ( $nt_data['tag'] ?? '' );
$nt_title = (string) ( $nt_data['title'] ?? '' );
$nt_sub   = (string) ( $nt_data['sub'] ?? '' );
?>
<section class="app-downloads" id="<?php echo esc_attr( sanitize_html_class( $nt_src ) ); ?>">
	<div class="container">

		<?php if ( $nt_tag || $nt_title || $nt_sub ) : ?>
			<div class="app-section-center">
				<?php if ( $nt_tag ) : ?><div class="app-section-tag"><?php echo esc_html( $nt_tag ); ?></div><?php endif; ?>
				<?php if ( $nt_title ) : ?><h2 class="section-title"><?php echo wp_kses( $nt_title, array( 'em' => array() ) ); ?></h2><?php endif; ?>
				<?php if ( $nt_sub ) : ?><p class="section-body"><?php echo esc_html( $nt_sub ); ?></p><?php endif; ?>
			</div>
		<?php endif; ?>

		<ul class="app-downloads__list">
			<?php
			foreach ( $nt_items as $nt_item ) :
				$nt_item      = (array) $nt_item;
				$nt_item_name = trim( (string) ( $nt_item['title'] ?? '' ) );
				if ( '' === $nt_item_name ) {
					continue;
				}
				$nt_file   = (string) ( $nt_item['file'] ?? '' );
				$nt_dialog = (string) ( $nt_item['dialog'] ?? '' );
				$nt_kind   = strtoupper( (string) ( $nt_item['kind'] ?? '' ) );
				?>
				<li class="app-download">
					<span class="app-download__badge" aria-hidden="true">
						<?php echo NT_Icons::get_or_text( (string) ( $nt_item['icon'] ?? 'file' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?>
						<?php if ( '' !== $nt_kind ) : ?>
							<span class="app-download__kind"><?php echo esc_html( $nt_kind ); ?></span>
						<?php endif; ?>
					</span>

					<div class="app-download__copy">
						<h3 class="app-download__title"><?php echo esc_html( $nt_item_name ); ?></h3>
						<?php if ( ! empty( $nt_item['text'] ) ) : ?>
							<p class="app-download__text"><?php echo esc_html( $nt_item['text'] ); ?></p>
						<?php endif; ?>

						<?php if ( ! empty( $nt_item['size'] ) || ! empty( $nt_item['updated'] ) ) : ?>
							<p class="app-download__meta">
								<?php if ( ! empty( $nt_item['size'] ) ) : ?>
									<span><?php echo esc_html( $nt_item['size'] ); ?></span>
								<?php endif; ?>
								<?php if ( ! empty( $nt_item['updated'] ) ) : ?>
									<span><?php echo esc_html( $nt_item['updated'] ); ?></span>
								<?php endif; ?>
							</p>
						<?php endif; ?>
					</div>

					<?php if ( '' !== $nt_dialog && NT_Dialog::exists( $nt_dialog ) ) : ?>
						<button class="app-download__btn" <?php app_dialog_trigger( $nt_dialog ); ?>>
							<?php NT_Icons::render( 'download' ); ?>
							<span><?php echo esc_html( NT_Ui::label( 'download' ) ); ?></span>
						</button>
					<?php elseif ( '' !== $nt_file ) : ?>
						<a class="app-download__btn" href="<?php echo esc_url( App_Helpers::link( $nt_file ) ); ?>" download
							<?php if ( ! empty( $nt_item['new_tab'] ) ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
							<?php NT_Icons::render( 'download' ); ?>
							<span><?php echo esc_html( NT_Ui::label( 'download' ) ); ?></span>
						</a>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>

	</div>
</section>
