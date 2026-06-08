<?php
defined( 'ABSPATH' ) || exit;

$settings   = $args['settings']   ?? [];
$preset_enq = $args['preset_enq'] ?? '';
$phone      = $settings['phone']           ?? '';
$email      = $settings['email']           ?? '';
$address    = $settings['address']         ?? '';
$map_url    = $settings['google_maps_url'] ?? '';

$enq_options = [
	'general'   => 'General',
	'complaint' => 'Complaint',
	'sales'     => 'Sales',
	'support'   => 'Support',
	'media'     => 'Media / Press',
	'other'     => 'Other',
];
?>
<section class="section" aria-label="<?php echo esc_attr( TXT_CONTACT_FORM_AND_DETAILS ); ?>">
  <div class="container">
    <div class="contact-layout">

      <!-- Form panel -->
      <div class="contact-layout__form" data-aos="fade-right">
        <div class="form-card">
          <h2 class="form-card__title">Book a Free Consultation</h2>
          <p class="form-card__desc">Fill in the form and we'll confirm a time within 24 hours.</p>

          <form data-ah-form="contact" class="ah-form" novalidate>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="cf-name">Full Name <span aria-hidden="true">*</span></label>
                <input id="cf-name" name="name" type="text" class="form-input" placeholder="<?php echo esc_attr( TXT_JANE_SMITH ); ?>" required>
                <span class="form-error"></span>
              </div>
              <div class="form-group"<?php if ( $preset_enq ) echo ' style="display:none"'; ?>>
                <label class="form-label" for="cf-type">Enquiry Type</label>
                <select id="cf-type" name="enquiry_type" class="form-input form-select">
                  <option value="">Select type…</option>
                  <?php foreach ( $enq_options as $val => $label ) : ?>
                  <option value="<?php echo esc_attr( $val ); ?>"<?php selected( $preset_enq, $val ); ?>><?php echo esc_html( $label ); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="cf-email">Email Address <span aria-hidden="true">*</span></label>
                <input id="cf-email" name="email" type="email" class="form-input" placeholder="" required>
                <span class="form-error"></span>
              </div>
              <div class="form-group">
                <label class="form-label" for="cf-phone">Phone Number</label>
                <input id="cf-phone" name="phone" type="tel" class="form-input" placeholder="Enter your Phonenumber">
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="cf-quote">In one sentence, what do you need?</label>
              <input id="cf-quote" name="short_quote" type="text" class="form-input" placeholder="" maxlength="300">
            </div>

            <div class="form-group">
              <label class="form-label" for="cf-message">Message <span aria-hidden="true">*</span></label>
              <textarea id="cf-message" name="message" class="form-input form-textarea" rows="4"
                        placeholder="<?php echo esc_attr( TXT_TELL_US_MORE_TIMELINE_REQUIREMENTS_ANYTHING_HELPFUL ); ?>" required></textarea>
              <span class="form-error"></span>
            </div>

            <div class="form-group" style="display:flex;align-items:flex-start;gap:10px;font-size:.875rem">
              <input type="checkbox" id="cf-consent" name="consent" required style="margin-top:3px;flex-shrink:0">
              <label for="cf-consent" style="color:var(--text-secondary)">
                I agree to be contacted by <?php echo esc_html( CLIENT_FULL_TITLE ); ?> about my enquiry.
                We never share your data. See our <a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>" style="color:var(--accent)">privacy policy</a>.
              </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Send Enquiry →</button>
            <div class="ah-form__status" aria-live="polite"></div>
          </form>
        </div>
      </div>

      <!-- Info panel -->
      <div class="contact-layout__info" data-aos="fade-left" data-delay="150">
        <div class="contact-info-block">
          <h3 class="contact-info-block__title">Contact Details</h3>
          <?php if ( $phone ) : ?>
          <a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $phone ) ); ?>" class="contact-info-item">
            <span class="contact-info-item__icon">📞</span>
            <div>
              <div class="contact-info-item__label">Phone</div>
              <div class="contact-info-item__value"><?php echo esc_html( $phone ); ?></div>
            </div>
          </a>
          <?php endif; ?>
          <?php if ( $email ) : ?>
          <a href="mailto:<?php echo esc_attr( $email ); ?>" class="contact-info-item">
            <span class="contact-info-item__icon">✉️</span>
            <div>
              <div class="contact-info-item__label">Email</div>
              <div class="contact-info-item__value"><?php echo esc_html( $email ); ?></div>
            </div>
          </a>
          <?php endif; ?>
          <?php if ( $address ) : ?>
          <div class="contact-info-item">
            <span class="contact-info-item__icon">📍</span>
            <div>
              <div class="contact-info-item__label">Coverage</div>
              <div class="contact-info-item__value"><?php echo esc_html( $address ); ?></div>
            </div>
          </div>
          <?php endif; ?>
          <div class="contact-info-item">
            <span class="contact-info-item__icon">🕐</span>
            <div>
              <div class="contact-info-item__label">Response Time</div>
              <div class="contact-info-item__value">Within 24 hours, Mon–Fri</div>
            </div>
          </div>
        </div>

        <?php if ( $map_url ) : ?>
        <div class="contact-map" data-aos="fade-up" data-delay="200">
          <iframe src="<?php echo esc_url( $map_url ); ?>" width="100%" height="100%"
                  style="border:0;" allowfullscreen="" loading="lazy"
                  referrerpolicy="no-referrer-when-downgrade"
                  title="<?php echo esc_attr( TXT_OUR_LOCATION_ON_GOOGLE_MAPS ); ?>">
          </iframe>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</section>
