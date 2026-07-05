<?php
/**
 * components/parts/floating_contact.php
 *
 * Floating WhatsApp + Call buttons, fixed to the bottom-left corner.
 * Numbers come straight from the plugin site settings DB via the
 * COMPANY_WHATSAPP_NO / COMPANY_PHONE_NO constants (ah_site_settings).
 * Renders nothing when neither number is configured.
 *
 * Rendered site-wide from a wp_footer hook (see functions.php).
 */

defined( 'ABSPATH' ) || exit;

/*
 * Numbers come from the same source the Contact page uses:
 * adn_service_contact_data()['contact_sidebar'] (merges JSON + DB), with the
 * global COMPANY_* constants as a fallback.
 */
$_wa_src = '';
$_ph_src = '';
if ( function_exists( 'adn_service_contact_data' ) ) {
	$_cd = adn_service_contact_data();
	$_cs = isset( $_cd['contact_sidebar'] ) && is_array( $_cd['contact_sidebar'] ) ? $_cd['contact_sidebar'] : array();
	if ( isset( $_cs['whatsapp']['number'] ) ) { $_wa_src = (string) $_cs['whatsapp']['number']; }
	if ( isset( $_cs['phone']['number'] ) )    { $_ph_src = (string) $_cs['phone']['number']; }
}
if ( '' === $_wa_src && defined( 'COMPANY_WHATSAPP_NO' ) ) { $_wa_src = (string) COMPANY_WHATSAPP_NO; }
if ( '' === $_ph_src && defined( 'COMPANY_PHONE_NO' ) )    { $_ph_src = (string) COMPANY_PHONE_NO; }

// WhatsApp: wa.me needs digits only (no +, spaces or punctuation).
$_wa = preg_replace( '/\D/', '', $_wa_src );

// Tel: keep a leading + and digits.
$_ph = preg_replace( '/[^\d+]/', '', $_ph_src );

if ( '' === $_wa && '' === $_ph ) {
	return;
}

$_wa_msg = rawurlencode( sprintf(
	/* translators: %s: company name */
	__( 'Hi %s, I have a question about buying/selling a home.', ADN_TEXT_DOMAIN ),
	defined( 'COMPANY_NAME' ) ? COMPANY_NAME : get_bloginfo( 'name' )
) );
?>
<div class="adn-fab" role="complementary" aria-label="<?php esc_attr_e( 'Contact us', ADN_TEXT_DOMAIN ); ?>">

	<?php if ( '' !== $_wa ) : ?>
		<a class="adn-fab-btn adn-fab-wa"
		   href="https://wa.me/<?php echo esc_attr( $_wa ); ?>?text=<?php echo esc_attr( $_wa_msg ); ?>"
		   target="_blank" rel="noopener noreferrer"
		   aria-label="<?php esc_attr_e( 'Chat with us on WhatsApp', ADN_TEXT_DOMAIN ); ?>">
			<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
				<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/>
			</svg>
			<span class="adn-fab-label"><?php esc_html_e( 'WhatsApp', ADN_TEXT_DOMAIN ); ?></span>
		</a>
	<?php endif; ?>

	<?php if ( '' !== $_ph ) : ?>
		<a class="adn-fab-btn adn-fab-call"
		   href="tel:<?php echo esc_attr( $_ph ); ?>"
		   aria-label="<?php esc_attr_e( 'Call us', ADN_TEXT_DOMAIN ); ?>">
			<i class="fa-solid fa-phone" aria-hidden="true"></i>
			<span class="adn-fab-label"><?php esc_html_e( 'Call us', ADN_TEXT_DOMAIN ); ?></span>
		</a>
	<?php endif; ?>

</div>
