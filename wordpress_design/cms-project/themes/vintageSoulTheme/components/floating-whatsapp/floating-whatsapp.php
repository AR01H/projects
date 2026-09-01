<?php
/**
 * VintageSoulTheme - Floating WhatsApp Action Widget (Icon Only Edition)
 */

use VintageSoul\Services\SettingsService;
use VintageSoul\Support\IconHelper;

defined( 'ABSPATH' ) || exit;

$whatsapp_url = SettingsService::whatsapp_url();
if ( empty( $whatsapp_url ) ) {
	$whatsapp_url = 'https://wa.me/447770461999';
}
?>
<div class="floating-whatsapp-widget" id="floating-whatsapp-widget">
	<a href="<?php echo esc_url( $whatsapp_url ); ?>" 
	   class="floating-whatsapp-btn" 
	   target="_blank" 
	   rel="noopener" 
	   title="<?php esc_attr_e( 'Chat with The Cane House on WhatsApp', 'vintagesoul' ); ?>"
	   aria-label="<?php esc_attr_e( 'Chat with The Cane House on WhatsApp', 'vintagesoul' ); ?>">
		
		<!-- Vintage Gold Pulse Ripple -->
		<span class="floating-whatsapp-pulse" aria-hidden="true"></span>
		
		<!-- WhatsApp SVG Icon -->
		<span class="floating-whatsapp-icon">
			<?php echo IconHelper::render( 'whatsapp', '#f6d599', 24 ); // phpcs:ignore ?>
		</span>

		<!-- Live Online Status Dot Badge -->
		<span class="floating-whatsapp-online-dot" aria-hidden="true"></span>
	</a>
</div>
