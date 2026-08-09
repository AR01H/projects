<?php
/**
 * components/sections/contact_form.php
 * Props: $form { heading, description, enquiry_types[], contact_methods[], help_timing[], submit_label, validation{} }
 */
defined( 'ABSPATH' ) || exit;

$_f      = isset( $form ) ? (array) $form : array();
$_hdg    = esc_html( isset( $_f['heading'] )      ? (string) $_f['heading']      : SITE_SECTION_CONTACT_FORM );
$_desc   = esc_html( isset( $_f['description'] )  ? (string) $_f['description']  : '' );
$_types  = isset( $_f['enquiry_types'] )           ? (array) $_f['enquiry_types'] : array();
$_methods = isset( $_f['contact_methods'] )        ? (array) $_f['contact_methods'] : array();
$_timing = isset( $_f['help_timing'] )             ? (array) $_f['help_timing']   : array();
$_submit = esc_html( isset( $_f['submit_label'] )  ? (string) $_f['submit_label'] : SITE_BTN_CONTACT_SUBMIT );
$_rules  = isset( $_f['validation'] )              ? (array) $_f['validation']    : array();
?>
<div class="contact-form-card">
	<h2><?php echo $_hdg; ?></h2>
	<?php if ( '' !== $_desc ) : ?>
		<p class="contact-form-desc"><?php echo $_desc; ?></p>
	<?php endif; ?>

	<form class="contact-enquiry-form" id="contactEnquiryForm" onsubmit="return false;" data-validation="<?php echo esc_attr( wp_json_encode( $_rules ) ); ?>">

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
				<input type="tel" id="contactWhatsApp" name="whatsapp" placeholder="<?php echo esc_attr( defined( 'COMPANY_WHATSAPP_PLACEHOLDER' ) ? COMPANY_WHATSAPP_PLACEHOLDER : SITE_PLACEHOLDER_WHATSAPP ); ?>" />
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

		<?php if ( ! empty( $_timing ) || ! empty( $_methods ) ) : ?>
		<div class="form-row">
			<?php if ( ! empty( $_timing ) ) : ?>
			<div class="form-group">
				<label class="form-label" for="helpTiming"><?php esc_html_e( 'When do you need help?', ADN_TEXT_DOMAIN ); ?></label>
				<select id="helpTiming" name="help_timing">
					<option value=""><?php esc_html_e( 'Select...', ADN_TEXT_DOMAIN ); ?></option>
					<?php foreach ( $_timing as $_t ) : ?>
						<option value="<?php echo esc_attr( $_t ); ?>"><?php echo esc_html( $_t ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php endif; ?>
			<?php if ( ! empty( $_methods ) ) : ?>
			<div class="form-group">
				<label class="form-label" for="contactMethod"><?php esc_html_e( 'Preferred Contact Method', ADN_TEXT_DOMAIN ); ?></label>
				<select id="contactMethod" name="contact_method">
					<option value=""><?php esc_html_e( 'Select...', ADN_TEXT_DOMAIN ); ?></option>
					<?php foreach ( $_methods as $_m ) :
						$_mk = esc_attr( isset( $_m['key'] ) ? (string) $_m['key'] : '' );
						$_ml = esc_html( isset( $_m['label'] ) ? (string) $_m['label'] : '' );
					?>
						<option value="<?php echo $_mk; ?>"><?php echo $_ml; ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<label class="consent-toggle-row">
			<input type="checkbox" name="consent" required id="contactConsent" class="consent-toggle-input" />
			<span class="consent-toggle-switch"></span>
			<span class="consent-toggle-text">
				<?php
				$_pp_url = esc_url( home_url( FORM_CONSENT_PRIVACY_URL ) );
				$_tc_url = esc_url( home_url( FORM_CONSENT_TERMS_URL ) );
				printf(
					esc_html( FORM_CONTACT_CONSENT_TEXT_TEMPLATE ),
					'<a href="javascript:void(0)" onclick="adnOpenPageModal(\'' . esc_js( $_tc_url ) . '\', \'Terms &amp; Conditions\')">',
					esc_html( FORM_CONSENT_TERMS_LABEL ),
					'</a>',
					'<a href="javascript:void(0)" onclick="adnOpenPageModal(\'' . esc_js( $_pp_url ) . '\', \'Privacy Policy\')">',
					esc_html( FORM_CONSENT_PRIVACY_LABEL ),
					'</a>',
					esc_html( SITE_BRAND_NAME )
				);
				?>
			</span>
		</label>

		<button type="submit" class="btn btn-primary contact-submit-btn" id="contactSubmitBtn" disabled>
			<?php echo $_submit; ?> <span aria-hidden="true">→</span>
		</button>

	</form>
</div>
<?php /* Submit-button gating + red-border validation for all required fields
         (not just consent) now lives in assets/js/contact.js. */ ?>
