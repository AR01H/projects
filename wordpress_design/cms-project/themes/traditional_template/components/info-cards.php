<?php
/**
 * components/info-cards.php - a row of "at a glance" tiles.
 *
 * GENERIC: phone / email / address / hours on a contact page, opening times
 * and parking on a visit page, or any short fact list. Each tile is an icon,
 * a label, a value and an optional action.
 *
 * A tile's action can be a link, a `tel:`/`mailto:` (set "type"), a dialog
 * from dialogs.json, or a copy-to-clipboard button - all declarative.
 *
 * Data: admin/data/<source>.json (default `info_cards`)
 *   { tag, title, sub, items[] {
 *       icon, label, value, note,
 *       type: "link" | "tel" | "mail" | "map" | "dialog" | "copy",
 *       url, dialog, link_label
 *   } }
 *
 * Args:
 *   source string  Which JSON file to read.
 *   block  string  ALSO append one shared block from blocks.json as a tile.
 */

defined( 'ABSPATH' ) || exit;

$nt_src   = ( isset( $source ) && $source ) ? (string) $source : 'info_cards';
$nt_data  = App_Helpers::data( $nt_src );
$nt_items = ( is_array( $nt_data ) && ! empty( $nt_data['items'] ) ) ? (array) $nt_data['items'] : array();
if ( empty( $nt_items ) ) {
	return;
}

$nt_tag   = (string) ( $nt_data['tag'] ?? '' );
$nt_title = (string) ( $nt_data['title'] ?? '' );
$nt_sub   = (string) ( $nt_data['sub'] ?? '' );

/**
 * Turn a tile's `type` + `value` into a real href, so the JSON can just say
 * "tel" and the template builds `tel:+441234567890` correctly.
 */
$nt_href_for = static function ( array $item ) {
	$type  = strtolower( (string) ( $item['type'] ?? 'link' ) );
	$value = (string) ( $item['value'] ?? '' );
	$url   = (string) ( $item['url'] ?? '' );

	switch ( $type ) {
		case 'tel':
			return 'tel:' . preg_replace( '/[^0-9+]/', '', $value );
		case 'mail':
			return 'mailto:' . sanitize_email( $value );
		case 'map':
		case 'link':
		default:
			return '' !== $url ? App_Helpers::link( $url ) : '';
	}
};
?>
<section class="app-infocards" id="<?php echo esc_attr( sanitize_html_class( $nt_src ) ); ?>">
	<div class="container">

		<?php if ( $nt_tag || $nt_title || $nt_sub ) : ?>
			<div class="app-section-center">
				<?php if ( $nt_tag ) : ?><div class="app-section-tag"><?php echo esc_html( $nt_tag ); ?></div><?php endif; ?>
				<?php if ( $nt_title ) : ?><h2 class="section-title"><?php echo wp_kses( $nt_title, array( 'em' => array() ) ); ?></h2><?php endif; ?>
				<?php if ( $nt_sub ) : ?><p class="section-body"><?php echo esc_html( $nt_sub ); ?></p><?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="app-infocards__grid">
			<?php
			foreach ( $nt_items as $nt_item ) :
				$nt_item  = (array) $nt_item;
				$nt_value = (string) ( $nt_item['value'] ?? '' );
				$nt_label = (string) ( $nt_item['label'] ?? '' );
				if ( '' === trim( $nt_value ) && '' === trim( $nt_label ) ) {
					continue;
				}
				$nt_type   = strtolower( (string) ( $nt_item['type'] ?? 'link' ) );
				$nt_href   = $nt_href_for( $nt_item );
				$nt_dialog = (string) ( $nt_item['dialog'] ?? '' );
				?>
				<article class="app-infocard">
					<?php if ( ! empty( $nt_item['icon'] ) ) : ?>
						<span class="app-infocard__icon" aria-hidden="true">
							<?php echo NT_Icons::get_or_text( (string) $nt_item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?>
						</span>
					<?php endif; ?>

					<?php if ( '' !== $nt_label ) : ?>
						<span class="app-infocard__label"><?php echo esc_html( $nt_label ); ?></span>
					<?php endif; ?>

					<?php if ( '' !== $nt_value ) : ?>
						<?php if ( '' !== $nt_href && 'dialog' !== $nt_type && 'copy' !== $nt_type ) : ?>
							<a class="app-infocard__value" href="<?php echo esc_url( $nt_href ); ?>"
								<?php if ( 'map' === $nt_type ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
								<?php echo esc_html( $nt_value ); ?>
							</a>
						<?php else : ?>
							<span class="app-infocard__value"><?php echo esc_html( $nt_value ); ?></span>
						<?php endif; ?>
					<?php endif; ?>

					<?php if ( ! empty( $nt_item['note'] ) ) : ?>
						<span class="app-infocard__note"><?php echo esc_html( $nt_item['note'] ); ?></span>
					<?php endif; ?>

					<?php if ( 'copy' === $nt_type && '' !== $nt_value ) : ?>
						<button type="button" class="app-infocard__action" data-nt-copy="<?php echo esc_attr( $nt_value ); ?>">
							<?php NT_Icons::render( 'copy' ); ?>
							<?php echo esc_html( $nt_item['link_label'] ?? NT_Ui::label( 'copy', 'Copy' ) ); ?>
						</button>
					<?php elseif ( 'dialog' === $nt_type && '' !== $nt_dialog && NT_Dialog::exists( $nt_dialog ) ) : ?>
						<button class="app-infocard__action" <?php app_dialog_trigger( $nt_dialog ); ?>>
							<?php echo esc_html( $nt_item['link_label'] ?? '' ); ?>
							<?php NT_Icons::render( 'arrow-right' ); ?>
						</button>
					<?php elseif ( ! empty( $nt_item['link_label'] ) && '' !== $nt_href ) : ?>
						<a class="app-infocard__action" href="<?php echo esc_url( $nt_href ); ?>">
							<?php echo esc_html( $nt_item['link_label'] ); ?>
							<?php NT_Icons::render( 'arrow-right' ); ?>
						</a>
					<?php endif; ?>
				</article>
			<?php endforeach; ?>
		</div>

	</div>
</section>
