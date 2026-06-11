<?php
/**
 * components/sections/contact_form.php
 * Props: $form { heading, description, enquiry_types[], submit_label }
 */
defined( 'ABSPATH' ) || exit;

$_f      = isset( $form ) ? (array) $form : array();
$_hdg    = esc_html( isset( $_f['heading'] )      ? (string) $_f['heading']      : 'Send us your enquiry' );
$_desc   = esc_html( isset( $_f['description'] )  ? (string) $_f['description']  : '' );
$_types  = isset( $_f['enquiry_types'] )           ? (array) $_f['enquiry_types'] : array();
$_submit = esc_html( isset( $_f['submit_label'] )  ? (string) $_f['submit_label'] : 'Submit Enquiry' );
?>
<div class="contact-form-card">
	<h2><?php echo $_hdg; ?></h2>
	<?php if ( '' !== $_desc ) : ?>
		<p class="contact-form-desc"><?php echo $_desc; ?></p>
	<?php endif; ?>

	<form class="contact-enquiry-form" id="contactEnquiryForm" onsubmit="return false;" novalidate>

		<?php if ( ! empty( $_types ) ) : ?>
		<div class="form-group">
			<label class="form-label"><?php esc_html_e( 'What best describes you?', ADN_TEXT_DOMAIN ); ?> <span class="form-required">*</span></label>
			<div class="enquiry-type-grid" id="enquiryTypeGrid">
				<?php foreach ( $_types as $_et ) :
					$_ek = esc_attr( sanitize_key( isset( $_et['key'] )   ? (string) $_et['key']   : '' ) );
					$_ei = esc_html( isset( $_et['icon'] )  ? (string) $_et['icon']  : '' );
					$_el = esc_html( isset( $_et['label'] ) ? (string) $_et['label'] : '' );
				?>
					<button type="button" class="enquiry-type-btn" data-type="<?php echo $_ek; ?>">
						<span class="et-icon" aria-hidden="true"><?php echo $_ei; ?></span>
						<span class="et-label"><?php echo $_el; ?></span>
					</button>
				<?php endforeach; ?>
			</div>
			<input type="hidden" id="selectedEnquiryType" name="enquiry_type" value="" />
		</div>
		<?php endif; ?>

		<div class="form-row">
			<div class="form-group">
				<label class="form-label" for="contactName"><?php esc_html_e( 'Your Name', ADN_TEXT_DOMAIN ); ?> <span class="form-required">*</span></label>
				<input type="text"  id="contactName"  name="name"  placeholder="<?php esc_attr_e( 'Enter your full name', ADN_TEXT_DOMAIN ); ?>" required />
			</div>
			<div class="form-group">
				<label class="form-label" for="contactEmail"><?php esc_html_e( 'Your Email', ADN_TEXT_DOMAIN ); ?> <span class="form-required">*</span></label>
				<input type="email" id="contactEmail" name="email" placeholder="<?php esc_attr_e( 'Enter your email address', ADN_TEXT_DOMAIN ); ?>" required />
			</div>
		</div>

		<div class="form-row">
			<div class="form-group">
				<label class="form-label" for="contactWhatsApp"><?php esc_html_e( 'WhatsApp Number', ADN_TEXT_DOMAIN ); ?> <span class="form-optional">(<?php esc_html_e( 'Optional', ADN_TEXT_DOMAIN ); ?>)</span></label>
				<div class="phone-input-wrap">
					<span class="phone-prefix">🇬🇧 +44</span>
					<input type="tel" id="contactWhatsApp" name="whatsapp" placeholder="e.g. 7747 223 762" />
				</div>
			</div>
			<div class="form-group">
				<label class="form-label" for="contactPostcode"><?php esc_html_e( 'Property Postcode', ADN_TEXT_DOMAIN ); ?> <span class="form-optional">(<?php esc_html_e( 'Optional', ADN_TEXT_DOMAIN ); ?>)</span></label>
				<input type="text" id="contactPostcode" name="postcode" placeholder="e.g. SW1A 1AA" maxlength="10" />
			</div>
		</div>

		<div class="form-group">
			<label class="form-label" for="contactMessage"><?php esc_html_e( 'Tell us how we can help', ADN_TEXT_DOMAIN ); ?> <span class="form-required">*</span></label>
			<textarea id="contactMessage" name="message" rows="5" placeholder="<?php esc_attr_e( 'Please provide as much detail as possible so we can assist you better…', ADN_TEXT_DOMAIN ); ?>" required></textarea>
		</div>

		<label class="consent-row">
			<input type="checkbox" name="consent" required id="contactConsent" />
			<span>
				<?php printf(
					/* translators: %1$s opens link, %2$s closes, %3$s opens link, %4$s closes */
					esc_html__( 'I agree to the %1$sPrivacy Policy%2$s and %3$sTerms of Use%4$s.', ADN_TEXT_DOMAIN ),
					'<a href="' . esc_url( home_url( '/privacy-policy/' ) ) . '" target="_blank">',
					'</a>',
					'<a href="' . esc_url( home_url( '/terms/' ) ) . '" target="_blank">',
					'</a>'
				); ?>
			</span>
		</label>

		<button type="submit" class="btn btn-primary contact-submit-btn">
			<?php echo $_submit; ?> <span aria-hidden="true">→</span>
		</button>

		<p class="contact-form-note">
			🔒 <?php esc_html_e( 'Your information is secure and will only be used to respond to your enquiry.', ADN_TEXT_DOMAIN ); ?>
		</p>

	</form>
</div>
