<?php
/**
 * Floating Toolbar - the sticky shortcut(s) pinned to the side of the screen.
 *
 * WHICH buttons show, and WHICH SIDE they sit on, are data - see
 * admin/data/ui.json -> "floating_toolbar":
 *
 *   "side": "left" | "right"
 *   "buttons": [ { "key", "label", "aria", "url" | "social", "icon" } ]
 *
 * `social` names a key in footer.json -> socials, so a WhatsApp button picks
 * up the real chat link rather than repeating the number in a second file. A
 * button whose destination resolves to nothing is skipped, so the toolbar can
 * never render a dead shortcut.
 *
 * It was two labelled buttons on the right, which on a phone covered a fifth
 * of the reading width. The list is now whatever the JSON says.
 */
defined( 'ABSPATH' ) || exit;

$ui      = App_Data_Provider::get( 'ui' )['floating_toolbar'] ?? array();
$socials = App_Data_Provider::get( 'footer' )['socials'] ?? array();

$side = ( 'left' === ( $ui['side'] ?? 'right' ) ) ? 'left' : 'right';

$buttons = array();
foreach ( (array) ( $ui['buttons'] ?? array() ) as $btn ) {
	$btn = (array) $btn;

	// A button either points at a page, or at one of the site's social links.
	$url = (string) ( $btn['url'] ?? '' );
	if ( '' === $url && ! empty( $btn['social'] ) ) {
		$url = (string) ( $socials[ $btn['social'] ] ?? '' );
	}
	if ( '' === trim( $url ) ) {
		continue;                       // no destination - no button
	}

	$buttons[] = array(
		'url'      => $url,
		'label'    => (string) ( $btn['label'] ?? '' ),
		'aria'     => (string) ( $btn['aria'] ?? $btn['label'] ?? '' ),
		'icon'     => (string) ( $btn['icon'] ?? 'chat' ),
		'external' => ! empty( $btn['social'] ) || 0 === strpos( $url, 'http' ),
	);
}

if ( empty( $buttons ) ) {
	return;
}
?>
<div class="app-floating-toolbar app-floating-toolbar--<?php echo esc_attr( $side ); ?>">
	<?php foreach ( $buttons as $nt_btn ) : ?>
		<a href="<?php echo esc_url( App_Helpers::link( $nt_btn['url'] ) ); ?>"
		   class="app-ftoolbar-btn"
		   aria-label="<?php echo esc_attr( $nt_btn['aria'] ); ?>"
			<?php if ( $nt_btn['external'] ) : ?>target="_blank" rel="noopener"<?php endif; ?>>
			<span class="app-ftoolbar-icon" aria-hidden="true">
				<?php echo NT_Icons::get_or_text( $nt_btn['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside. ?>
			</span>
			<?php if ( '' !== $nt_btn['label'] ) : ?>
				<span class="app-ftoolbar-label"><?php echo esc_html( $nt_btn['label'] ); ?></span>
			<?php endif; ?>
		</a>
	<?php endforeach; ?>
</div>
