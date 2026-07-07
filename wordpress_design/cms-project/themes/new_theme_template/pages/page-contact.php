<?php
/**
 * Template Name: Contact
 *
 * Registered as 'contact' in config/pages.php. The form posts through
 * NT.ajax('contact_submit') -> config/ajax.php -> handlers/ajax/contact.php.
 * No nonce code here - the dispatcher and the JS helper handle it.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<div class="nt-container nt-section">

	<?php
	nt_component( 'parts/page_header', array(
		'title'    => __( 'Contact Us', NT_TEXT_DOMAIN ),
		'subtitle' => __( 'Send us a message - we usually reply within one business day.', NT_TEXT_DOMAIN ),
	) );
	?>

	<div class="nt-contact-layout">

		<div class="nt-contact-info">
			<h3><?php esc_html_e( 'Reach us', NT_TEXT_DOMAIN ); ?></h3>
			<ul>
				<li><?php echo esc_html( nt_option( 'general', 'phone', NT_BRAND_PHONE ) ); ?></li>
				<li><?php echo esc_html( nt_option( 'general', 'email', NT_BRAND_EMAIL ) ); ?></li>
				<?php if ( '' !== (string) nt_option( 'general', 'address' ) ) : ?>
					<li><?php echo esc_html( nt_option( 'general', 'address' ) ); ?></li>
				<?php endif; ?>
			</ul>
		</div>

		<form class="nt-form" data-nt-contact-form novalidate>
			<div class="nt-form-row">
				<label for="nt-cf-name"><?php esc_html_e( 'Name *', NT_TEXT_DOMAIN ); ?></label>
				<input type="text" id="nt-cf-name" name="name" required>
			</div>
			<div class="nt-form-row">
				<label for="nt-cf-email"><?php esc_html_e( 'Email *', NT_TEXT_DOMAIN ); ?></label>
				<input type="email" id="nt-cf-email" name="email" required>
			</div>
			<div class="nt-form-row">
				<label for="nt-cf-phone"><?php esc_html_e( 'Phone', NT_TEXT_DOMAIN ); ?></label>
				<input type="tel" id="nt-cf-phone" name="phone">
			</div>
			<div class="nt-form-row">
				<label for="nt-cf-message"><?php esc_html_e( 'Message *', NT_TEXT_DOMAIN ); ?></label>
				<textarea id="nt-cf-message" name="message" rows="5" required></textarea>
			</div>
			<button type="submit" class="nt-btn"><?php esc_html_e( 'Send Message', NT_TEXT_DOMAIN ); ?></button>
			<p class="nt-form-status" data-nt-form-status role="status" aria-live="polite"></p>
		</form>

	</div>

</div>
<?php
get_footer();
