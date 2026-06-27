<?php
/**
 * components/sections/contact_form.php
 * Props: $form { heading, description, enquiry_types[], submit_label }
 */
defined( 'ABSPATH' ) || exit;

$_f      = isset( $form ) ? (array) $form : array();
$_hdg    = esc_html( isset( $_f['heading'] )      ? (string) $_f['heading']      : SITE_SECTION_CONTACT_FORM );
$_desc   = esc_html( isset( $_f['description'] )  ? (string) $_f['description']  : '' );
$_types  = isset( $_f['enquiry_types'] )           ? (array) $_f['enquiry_types'] : array();
$_submit = esc_html( isset( $_f['submit_label'] )  ? (string) $_f['submit_label'] : SITE_BTN_CONTACT_SUBMIT );
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
					$_ei = adn_icon( isset( $_et['icon'] )  ? (string) $_et['icon']  : '' );
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
				<label class="form-label" for="contactName"><?php echo esc_html( FORM_NAME_LABEL ); ?> <span class="form-required"><?php echo esc_html( FORM_REQUIRED_SUFFIX ); ?></span></label>
				<input type="text"  id="contactName"  name="name"  placeholder="<?php echo esc_attr( SITE_PLACEHOLDER_NAME ); ?>" required />
			</div>
			<div class="form-group">
				<label class="form-label" for="contactEmail"><?php echo esc_html( FORM_EMAIL_LABEL ); ?> <span class="form-required"><?php echo esc_html( FORM_REQUIRED_SUFFIX ); ?></span></label>
				<input type="email" id="contactEmail" name="email" placeholder="<?php echo esc_attr( SITE_PLACEHOLDER_EMAIL ); ?>" required />
			</div>
		</div>

		<div class="form-row">
			<div class="form-group">
				<label class="form-label" for="contactWhatsApp"><?php echo esc_html( FORM_WHATSAPP_LABEL ); ?> <span class="form-optional"><?php echo esc_html( FORM_OPTIONAL_SUFFIX ); ?></span></label>
				<input type="tel" id="contactWhatsApp" name="whatsapp" placeholder="<?php echo esc_attr( SITE_PLACEHOLDER_WHATSAPP ); ?>" />
			</div>
			<div class="form-group">
				<label class="form-label" for="contactPostcode"><?php echo esc_html( FORM_POSTCODE_LABEL ); ?> <span class="form-optional"><?php echo esc_html( FORM_OPTIONAL_SUFFIX ); ?></span></label>
				<input type="text" id="contactPostcode" name="postcode" placeholder="" maxlength="10" />
			</div>
		</div>

		<div class="form-group">
			<label class="form-label" for="contactMessage"><?php echo esc_html( FORM_MESSAGE_LABEL ); ?> <span class="form-required"><?php echo esc_html( FORM_REQUIRED_SUFFIX ); ?></span></label>
			<textarea id="contactMessage" name="message" rows="5" placeholder="<?php echo esc_attr( SITE_PLACEHOLDER_MESSAGE ); ?>" required></textarea>
		</div>

		<label class="consent-row">
			<input type="checkbox" name="consent" required id="contactConsent" />
			<span>
				<?php
				$_pp_url = esc_url( home_url( FORM_CONSENT_PRIVACY_URL ) );
				$_tc_url = esc_url( home_url( FORM_CONSENT_TERMS_URL ) );
				printf(
					/* translators: 1: opening <a> for Privacy Policy, 2: Privacy Policy label, 3: closing </a>, 4: opening <a> for Terms of Use, 5: Terms of Use label, 6: closing </a> */
					esc_html__( 'I agree to the %1$s%2$s%3$s and %4$s%5$s%6$s', ADN_TEXT_DOMAIN ),
					'<a href="' . $_pp_url . '" target="_blank" rel="noopener">',
					esc_html( FORM_CONSENT_PRIVACY_LABEL ),
					'</a>',
					'<a href="' . $_tc_url . '" target="_blank" rel="noopener">',
					esc_html( FORM_CONSENT_TERMS_LABEL ),
					'</a>'
				);
				?>
			</span>
		</label>

		<button type="submit" class="btn btn-primary contact-submit-btn" id="contactSubmitBtn" disabled>
			<?php echo $_submit; ?> <span aria-hidden="true">→</span>
		</button>

	</form>
</div>
<script>
(function(){
	var cb  = document.getElementById('contactConsent');
	var btn = document.getElementById('contactSubmitBtn');
	if ( cb && btn ) {
		cb.addEventListener('change', function(){ btn.disabled = !cb.checked; });
	}
}());
</script>
