<?php
/**
 * components/parts/post_sidebar_newsletter.php
 *
 * Sidebar — Stay Informed newsletter signup form.
 *
 * Props (via extract):
 *   $newsletter = [
 *       'icon'         => string,
 *       'heading'      => string,
 *       'description'  => string,
 *       'placeholder'  => string,
 *       'button_label' => string,
 *       'note'         => string,
 *   ]
 */

defined( 'ABSPATH' ) || exit;

$_nl  = isset( $newsletter ) ? (array) $newsletter : array();
$_ico = adn_icon( isset( $_nl['icon'] )         ? (string) $_nl['icon']         : '✉️' );
$_hdg = esc_html( isset( $_nl['heading'] )      ? (string) $_nl['heading']      : 'Stay Informed' );
$_dsc = esc_html( isset( $_nl['description'] )  ? (string) $_nl['description']  : '' );
$_ph  = esc_attr( isset( $_nl['placeholder'] )  ? (string) $_nl['placeholder']  : 'Enter your email address' );
$_btn = esc_html( isset( $_nl['button_label'] ) ? (string) $_nl['button_label'] : 'Subscribe' );
$_nte = esc_html( isset( $_nl['note'] )         ? (string) $_nl['note']         : '' );
?>
<div class="sidebar-box sidebar-newsletter">
	<div class="sidebar-newsletter-icon" aria-hidden="true"><?php echo $_ico; ?></div>
	<h3><?php echo $_hdg; ?></h3>
	<?php if ( '' !== $_dsc ) : ?>
		<p><?php echo $_dsc; ?></p>
	<?php endif; ?>
	<form class="sidebar-newsletter-form" onsubmit="return false;" novalidate>
		<label for="sidebarNewsletterEmail" class="screen-reader-text"><?php esc_html_e( 'Email address', ADN_TEXT_DOMAIN ); ?></label>
		<input
			type="email"
			id="sidebarNewsletterEmail"
			name="email"
			placeholder="<?php echo $_ph; ?>"
			autocomplete="email"
			required
		/>
		<button type="submit" class="btn btn-primary sidebar-nl-btn"><?php echo $_btn; ?></button>
	</form>
	<?php if ( '' !== $_nte ) : ?>
		<p class="sidebar-nl-note"><?php echo $_nte; ?></p>
	<?php endif; ?>
</div>
