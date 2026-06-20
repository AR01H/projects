<?php
/**
 * components/parts/post_sidebar_newsletter.php - Sidebar: Newsletter signup.
 *
 * Props: $newsletter { icon, heading, description, placeholder, button_label, note }
 */

defined( 'ABSPATH' ) || exit;

$_nl      = isset( $newsletter ) ? (array) $newsletter : array();
$_ico     = adn_icon( isset( $_nl['icon'] )         ? (string) $_nl['icon']         : '✉️' );
$_hdg     = isset( $_nl['heading'] )      ? (string) $_nl['heading']      : SITE_SIDEBAR_NEWSLETTER;
$_dsc     = isset( $_nl['description'] )  ? (string) $_nl['description']  : '';
$_ph      = isset( $_nl['placeholder'] )  ? (string) $_nl['placeholder']  : SITE_PLACEHOLDER_NEWSLETTER;
$_btn     = isset( $_nl['button_label'] ) ? (string) $_nl['button_label'] : adn_term( 'sidebar.newsletter_btn', 'Subscribe' );
$_note    = isset( $_nl['note'] )         ? (string) $_nl['note']         : '';
$_nonce   = wp_create_nonce( 'ah_newsletter_nonce' );
?>
<div class="sw-panel">
	<div class="sw-header">
		<h3 class="sw-title"><span aria-hidden="true"><?php echo $_ico; ?></span> <?php echo esc_html( $_hdg ); ?></h3>
	</div>

	<?php if ( '' !== $_dsc ) : ?>
		<p class="sw-nl-desc"><?php echo esc_html( $_dsc ); ?></p>
	<?php endif; ?>

	<form class="sw-nl-form adn-nl-form" onsubmit="return false;" novalidate
	      data-nonce="<?php echo esc_attr( $_nonce ); ?>"
	      data-ajaxurl="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
		<label for="swNlEmail-<?php echo esc_attr( uniqid( 'nl' ) ); ?>" class="screen-reader-text"><?php esc_html_e( 'Email address', ADN_TEXT_DOMAIN ); ?></label>
		<input type="email" name="nl_email" class="sw-nl-input adn-nl-email"
		       placeholder="<?php echo esc_attr( $_ph ); ?>"
		       autocomplete="email" required>
		<button type="submit" class="sw-cta-btn adn-nl-btn"><?php echo esc_html( $_btn ); ?></button>
	</form>
	<div class="adn-nl-msg" style="display:none;margin-top:8px;font-size:13px;font-weight:500"></div>
</div>
