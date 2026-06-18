<?php
/**
 * components/sections/guidance_form.php
 * Props: $form { heading, help_label, help_placeholder, help_options[],
 *                iam_label, iam_placeholder, iam_options[],
 *                time_label, time_placeholder, time_options[],
 *                contact_methods[] { key, label }, submit_label }
 */
defined( 'ABSPATH' ) || exit;

$_f       = isset( $form ) ? (array) $form : array();
$_hdg     = esc_html( isset( $_f['heading'] )          ? (string) $_f['heading']          : SITE_SECTION_GUIDANCE_FORM );
$_hl      = esc_html( isset( $_f['help_label'] )       ? (string) $_f['help_label']       : SITE_FORM_HELP_LABEL );
$_hp      = esc_attr( isset( $_f['help_placeholder'] ) ? (string) $_f['help_placeholder'] : SITE_PLACEHOLDER_SELECT );
$_hopts   = isset( $_f['help_options'] )                ? (array) $_f['help_options']      : array();
$_il      = esc_html( isset( $_f['iam_label'] )        ? (string) $_f['iam_label']        : SITE_FORM_IAM_LABEL );
$_ip      = esc_attr( isset( $_f['iam_placeholder'] )  ? (string) $_f['iam_placeholder']  : SITE_PLACEHOLDER_SELECT );
$_iopts   = isset( $_f['iam_options'] )                 ? (array) $_f['iam_options']       : array();
$_tl      = esc_html( isset( $_f['time_label'] )       ? (string) $_f['time_label']       : SITE_FORM_TIME_LABEL );
$_tp      = esc_attr( isset( $_f['time_placeholder'] ) ? (string) $_f['time_placeholder'] : SITE_PLACEHOLDER_TIME );
$_topts   = isset( $_f['time_options'] )                ? (array) $_f['time_options']      : array();
$_methods = isset( $_f['contact_methods'] )             ? (array) $_f['contact_methods']   : array();
$_submit  = esc_html( isset( $_f['submit_label'] )     ? (string) $_f['submit_label']     : SITE_BTN_SUBMIT_REQUEST );
?>
<div class="guidance-form-card">
	<h2><?php echo $_hdg; ?></h2>

	<form class="guidance-request-form" id="guidanceRequestForm" onsubmit="return false;" novalidate>

		<div class="form-group">
			<label class="form-label" for="guidanceHelpWith"><?php echo $_hl; ?> <span class="form-required">*</span></label>
			<select id="guidanceHelpWith" name="help_with" required>
				<option value=""><?php echo $_hp; ?></option>
				<?php foreach ( $_hopts as $_o ) : ?>
					<option value="<?php echo esc_attr( sanitize_text_field( (string) $_o ) ); ?>"><?php echo esc_html( (string) $_o ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="form-row">
			<div class="form-group">
				<label class="form-label" for="guidanceName"><?php esc_html_e( 'Your Name', ADN_TEXT_DOMAIN ); ?> <span class="form-required">*</span></label>
				<input type="text"  id="guidanceName"  name="name"  placeholder="<?php esc_attr_e( 'Enter your full name', ADN_TEXT_DOMAIN ); ?>" required />
			</div>
			<div class="form-group">
				<label class="form-label" for="guidanceEmail"><?php esc_html_e( 'Email Address', ADN_TEXT_DOMAIN ); ?> <span class="form-required">*</span></label>
				<input type="email" id="guidanceEmail" name="email" placeholder="<?php esc_attr_e( 'Enter your email', ADN_TEXT_DOMAIN ); ?>" required />
			</div>
		</div>

		<div class="form-row">
			<div class="form-group">
				<label class="form-label" for="guidancePhone"><?php esc_html_e( 'Phone Number', ADN_TEXT_DOMAIN ); ?></label>
				<input type="tel" id="guidancePhone" name="phone" placeholder="+44 7700 000000" />
			</div>
			<div class="form-group">
				<label class="form-label" for="guidanceIAm"><?php echo $_il; ?> <span class="form-required">*</span></label>
				<select id="guidanceIAm" name="i_am" required>
					<option value=""><?php echo $_ip; ?></option>
					<?php foreach ( $_iopts as $_o ) : ?>
						<option value="<?php echo esc_attr( sanitize_text_field( (string) $_o ) ); ?>"><?php echo esc_html( (string) $_o ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="form-group">
			<label class="form-label" for="guidanceRequirement"><?php esc_html_e( 'Your Requirement', ADN_TEXT_DOMAIN ); ?> <span class="form-required">*</span></label>
			<textarea id="guidanceRequirement" name="requirement" rows="4" placeholder="<?php esc_attr_e( 'Tell us more about your situation…', ADN_TEXT_DOMAIN ); ?>" required></textarea>
		</div>

		<div class="form-row">
			<div class="form-group">
				<label class="form-label" for="guidanceTimeFrame"><?php echo $_tl; ?></label>
				<select id="guidanceTimeFrame" name="time_frame">
					<option value=""><?php echo $_tp; ?></option>
					<?php foreach ( $_topts as $_o ) : ?>
						<option value="<?php echo esc_attr( sanitize_text_field( (string) $_o ) ); ?>"><?php echo esc_html( (string) $_o ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
			<?php if ( ! empty( $_methods ) ) : ?>
			<div class="form-group">
				<label class="form-label"><?php esc_html_e( 'Preferred Contact Method', ADN_TEXT_DOMAIN ); ?></label>
				<div class="contact-method-checks">
					<?php foreach ( $_methods as $_m ) :
						$_mk = esc_attr( sanitize_key( isset( $_m['key'] )   ? (string) $_m['key']   : '' ) );
						$_ml = esc_html( isset( $_m['label'] ) ? (string) $_m['label'] : '' );
					?>
						<label class="method-check-label">
							<input type="checkbox" name="contact_method[]" value="<?php echo $_mk; ?>" />
							<?php echo $_ml; ?>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>
		</div>

		<button type="submit" class="btn btn-primary guidance-submit-btn">
			<?php echo $_submit; ?> <span aria-hidden="true">→</span>
		</button>

		<p class="contact-form-note">
			🔒 <?php esc_html_e( 'Your information is secure and will only be shared with suitable experts.', ADN_TEXT_DOMAIN ); ?>
		</p>

	</form>
</div>
