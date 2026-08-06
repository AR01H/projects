<?php
/**
 * components/quick-links.php - the portal tile grid.
 *
 * GENERIC: the "what do you want to do?" board - big, obvious destinations
 * for the handful of things most visitors came for. Good directly under a
 * hero, and good as a site index on a help or 404 page.
 *
 * Tiles can be written inline in this section's own JSON, OR pulled straight
 * from the SHARED library in admin/data/blocks.json so the same wording is
 * reused elsewhere (see src/Content/class-blocks.php):
 *
 *   { "component": "quick-links",
 *     "args": { "blocks": ["order_online","book_event","read_blogs"] } }
 *
 * Data: admin/data/<source>.json (default `quick_links`)
 *   { tag, title, sub, columns (2|3|4), items[] { icon, title, text, label,
 *     url, dialog, new_tab } }
 *
 * Args:
 *   source  string  Which JSON file to read.
 *   blocks  array   Library keys to render instead of / as well as `items`.
 *   columns int      Force the column count.
 */

defined( 'ABSPATH' ) || exit;

$nt_src  = ( isset( $source ) && $source ) ? (string) $source : 'quick_links';
$nt_data = nt_data( $nt_src );

// Inline items first, then anything pulled from the shared block library.
$nt_tiles = array();
foreach ( (array) ( $nt_data['items'] ?? array() ) as $nt_item ) {
	$nt_item = (array) $nt_item;
	if ( '' !== trim( (string) ( $nt_item['title'] ?? '' ) ) ) {
		$nt_tiles[] = array(
			'icon'    => (string) ( $nt_item['icon'] ?? '' ),
			'title'   => (string) $nt_item['title'],
			'text'    => (string) ( $nt_item['text'] ?? '' ),
			'label'   => (string) ( $nt_item['label'] ?? '' ),
			'url'     => (string) ( $nt_item['url'] ?? '' ),
			'dialog'  => (string) ( $nt_item['dialog'] ?? '' ),
			'new_tab' => ! empty( $nt_item['new_tab'] ),
		);
	}
}

$nt_from_library = NT_Blocks::resolve( null, $blocks ?? ( $nt_data['blocks'] ?? null ) );
foreach ( $nt_from_library as $nt_block ) {
	$nt_tiles[] = array(
		'icon'    => $nt_block['icon'],
		'title'   => $nt_block['title'],
		'text'    => $nt_block['text'],
		'label'   => $nt_block['label'],
		'url'     => $nt_block['url'],
		'dialog'  => $nt_block['dialog'],
		'new_tab' => $nt_block['new_tab'],
	);
}

if ( empty( $nt_tiles ) ) {
	return;
}

$nt_columns = (int) ( $columns ?? ( $nt_data['columns'] ?? 4 ) );
$nt_columns = in_array( $nt_columns, array( 2, 3, 4 ), true ) ? $nt_columns : 4;
$nt_tag     = (string) ( $nt_data['tag'] ?? '' );
$nt_title   = (string) ( $nt_data['title'] ?? '' );
$nt_sub     = (string) ( $nt_data['sub'] ?? '' );
?>
<section class="nt-quicklinks nt-quicklinks--<?php echo esc_attr( (string) $nt_columns ); ?>" id="<?php echo esc_attr( sanitize_html_class( $nt_src ) ); ?>">
	<div class="container">

		<?php if ( $nt_tag || $nt_title || $nt_sub ) : ?>
			<div class="nt-section-center">
				<?php if ( $nt_tag ) : ?><div class="nt-section-tag"><?php echo esc_html( $nt_tag ); ?></div><?php endif; ?>
				<?php if ( $nt_title ) : ?><h2 class="section-title"><?php echo wp_kses( $nt_title, array( 'em' => array() ) ); ?></h2><?php endif; ?>
				<?php if ( $nt_sub ) : ?><p class="section-body"><?php echo esc_html( $nt_sub ); ?></p><?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="nt-quicklinks__grid">
			<?php
			foreach ( $nt_tiles as $nt_tile ) :
				$nt_is_dialog = ( '' !== $nt_tile['dialog'] && NT_Dialog::exists( $nt_tile['dialog'] ) );
				$nt_has_link  = ( '' !== $nt_tile['url'] );
				if ( ! $nt_is_dialog && ! $nt_has_link ) {
					continue;                     // a tile with nowhere to go is noise
				}
				// The WHOLE tile is the control - a big touch target beats a
				// small "click here" on a phone.
				$nt_el = $nt_is_dialog ? 'button' : 'a';
				?>
				<<?php echo $nt_el; ?> class="nt-quicklink"
					<?php if ( $nt_is_dialog ) : ?>
						<?php nt_dialog_trigger( $nt_tile['dialog'] ); ?>
					<?php else : ?>
						href="<?php echo esc_url( nt_link( $nt_tile['url'] ) ); ?>"
						<?php if ( $nt_tile['new_tab'] ) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
					<?php endif; ?>>

					<?php if ( '' !== $nt_tile['icon'] ) : ?>
						<span class="nt-quicklink__icon" aria-hidden="true">
							<?php echo NT_Icons::get_or_text( $nt_tile['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?>
						</span>
					<?php endif; ?>

					<span class="nt-quicklink__title"><?php echo esc_html( $nt_tile['title'] ); ?></span>

					<?php if ( '' !== $nt_tile['text'] ) : ?>
						<span class="nt-quicklink__text"><?php echo esc_html( $nt_tile['text'] ); ?></span>
					<?php endif; ?>

					<span class="nt-quicklink__go">
						<?php echo esc_html( '' !== $nt_tile['label'] ? $nt_tile['label'] : NT_Ui::label( 'read_more' ) ); ?>
						<?php NT_Icons::render( 'arrow-right' ); ?>
					</span>
				</<?php echo $nt_el; ?>>
			<?php endforeach; ?>
		</div>

	</div>
</section>
