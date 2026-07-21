<?php
/**
 * Floating Toolbar - sticky shortcuts shown on every page.
 *
 * Only real, working destinations - "Order Now" links to the actual Order
 * page (components/order-to-deliver.php) and WhatsApp opens a real chat
 * link sourced from admin/data/footer.json. No fake/dead buttons.
 */
defined( 'ABSPATH' ) || exit;

$footer_data = NT_Data_Provider::get( 'footer' );
$whatsapp    = $footer_data['socials']['whatsapp'] ?? '';
?>

<div class="nt-floating-toolbar">
	<a href="<?php echo esc_url( home_url( '/order/' ) ); ?>" class="nt-ftoolbar-btn" aria-label="<?php esc_attr_e( 'Order Now', NT_TEXT_DOMAIN ); ?>">
		<span class="nt-ftoolbar-icon" aria-hidden="true">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2h8l-1 6H9L8 2z"/><path d="M9 8l1.2 11a1 1 0 0 0 1 .9h1.6a1 1 0 0 0 1-.9L15 8"/><path d="M9.5 13.5h5"/></svg>
		</span>
		<span class="nt-ftoolbar-label"><?php esc_html_e( 'ORDER NOW', NT_TEXT_DOMAIN ); ?></span>
	</a>
	<?php if ( $whatsapp ) : ?>
	<a href="<?php echo esc_url( $whatsapp ); ?>" class="nt-ftoolbar-btn" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'WhatsApp Chat', NT_TEXT_DOMAIN ); ?>">
		<span class="nt-ftoolbar-icon" aria-hidden="true">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
		</span>
		<span class="nt-ftoolbar-label"><?php esc_html_e( 'WHATSAPP CHAT', NT_TEXT_DOMAIN ); ?></span>
	</a>
	<?php endif; ?>
</div>
