<?php
/**
 * Template Name: Contact
 */
defined( 'ABSPATH' ) || exit;

get_template_part( 'parts/header' );

$contact  = ah_get_contact();
$settings = ah_get_settings();
$phone    = $settings['phone'] ?? '+44 7747 223762';
$email    = $settings['email'] ?? 'hello@advaithhomes.co.uk';
$whatsapp = $settings['whatsapp'] ?? '+447747223762';
$address  = $settings['address'] ?? 'London, United Kingdom';
$hours    = $settings['hours'] ?? 'Mon – Fri: 9am – 6pm';

$nonce = wp_create_nonce( 'ah_contact_form' );
?>
<main id="main-content">

  <!-- Page Hero -->
  <section class="page-hero">
    <div class="container">
      <div class="eyebrow reveal"><?php esc_html_e( 'Get In Touch', 'ah-theme' ); ?></div>
      <h1 class="reveal reveal-delay-1"><?php esc_html_e( 'Start Your Property Journey', 'ah-theme' ); ?></h1>
      <p class="reveal reveal-delay-2">
        <?php esc_html_e( 'Book a free, no-obligation discovery call and tell us exactly what you\'re looking for.', 'ah-theme' ); ?>
      </p>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="contact-layout">

        <!-- Contact Info -->
        <div class="contact-info reveal">
          <h3><?php esc_html_e( 'How to Reach Us', 'ah-theme' ); ?></h3>

          <div class="contact-item">
            <div class="contact-item__icon">📞</div>
            <div>
              <div class="contact-item__label"><?php esc_html_e( 'Phone', 'ah-theme' ); ?></div>
              <a href="tel:<?php echo esc_attr( preg_replace( '/\s+/', '', $phone ) ); ?>" class="contact-item__value">
                <?php echo esc_html( $phone ); ?>
              </a>
            </div>
          </div>

          <div class="contact-item">
            <div class="contact-item__icon">✉️</div>
            <div>
              <div class="contact-item__label"><?php esc_html_e( 'Email', 'ah-theme' ); ?></div>
              <a href="mailto:<?php echo esc_attr( $email ); ?>" class="contact-item__value">
                <?php echo esc_html( $email ); ?>
              </a>
            </div>
          </div>

          <?php if ( $whatsapp ) : ?>
          <div class="contact-item">
            <div class="contact-item__icon">💬</div>
            <div>
              <div class="contact-item__label"><?php esc_html_e( 'WhatsApp', 'ah-theme' ); ?></div>
              <a href="https://wa.me/<?php echo esc_attr( preg_replace( '/[^0-9]/', '', $whatsapp ) ); ?>"
                 class="contact-item__value" target="_blank" rel="noopener noreferrer">
                <?php esc_html_e( 'Message us on WhatsApp', 'ah-theme' ); ?>
              </a>
            </div>
          </div>
          <?php endif; ?>

          <div class="contact-item">
            <div class="contact-item__icon">📍</div>
            <div>
              <div class="contact-item__label"><?php esc_html_e( 'Location', 'ah-theme' ); ?></div>
              <div class="contact-item__value"><?php echo esc_html( $address ); ?></div>
            </div>
          </div>

          <div class="contact-item">
            <div class="contact-item__icon">🕐</div>
            <div>
              <div class="contact-item__label"><?php esc_html_e( 'Office Hours', 'ah-theme' ); ?></div>
              <div class="contact-item__value"><?php echo esc_html( $hours ); ?></div>
            </div>
          </div>
        </div>

        <!-- Contact Form -->
        <div class="contact-form-wrap reveal reveal-delay-1">
          <h3><?php esc_html_e( 'Send Us a Message', 'ah-theme' ); ?></h3>
          <form class="contact-form" id="ahContactForm" novalidate>
            <?php wp_nonce_field( 'ah_contact_form', 'ah_contact_nonce' ); ?>

            <div class="form-row">
              <div class="form-group">
                <label for="cf-name"><?php esc_html_e( 'Full Name *', 'ah-theme' ); ?></label>
                <input type="text" id="cf-name" name="name" class="form-control" required
                       placeholder="<?php esc_attr_e( 'Your name', 'ah-theme' ); ?>">
              </div>
              <div class="form-group">
                <label for="cf-email"><?php esc_html_e( 'Email Address *', 'ah-theme' ); ?></label>
                <input type="email" id="cf-email" name="email" class="form-control" required
                       placeholder="<?php esc_attr_e( 'your@email.com', 'ah-theme' ); ?>">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="cf-phone"><?php esc_html_e( 'Phone Number', 'ah-theme' ); ?></label>
                <input type="tel" id="cf-phone" name="phone" class="form-control"
                       placeholder="<?php esc_attr_e( '+44 7700 000000', 'ah-theme' ); ?>">
              </div>
              <div class="form-group">
                <label for="cf-budget"><?php esc_html_e( 'Approximate Budget', 'ah-theme' ); ?></label>
                <select id="cf-budget" name="budget" class="form-control">
                  <option value=""><?php esc_html_e( 'Select a range', 'ah-theme' ); ?></option>
                  <option value="under-300k"><?php esc_html_e( 'Under £300,000', 'ah-theme' ); ?></option>
                  <option value="300k-500k"><?php esc_html_e( '£300,000 – £500,000', 'ah-theme' ); ?></option>
                  <option value="500k-750k"><?php esc_html_e( '£500,000 – £750,000', 'ah-theme' ); ?></option>
                  <option value="750k-1m"><?php esc_html_e( '£750,000 – £1,000,000', 'ah-theme' ); ?></option>
                  <option value="over-1m"><?php esc_html_e( 'Over £1,000,000', 'ah-theme' ); ?></option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="cf-type"><?php esc_html_e( 'Buyer Type', 'ah-theme' ); ?></label>
              <select id="cf-type" name="buyer_type" class="form-control">
                <option value=""><?php esc_html_e( 'Please select…', 'ah-theme' ); ?></option>
                <option value="first-time"><?php esc_html_e( 'First-Time Buyer', 'ah-theme' ); ?></option>
                <option value="home-mover"><?php esc_html_e( 'Home Mover', 'ah-theme' ); ?></option>
                <option value="investor"><?php esc_html_e( 'Property Investor', 'ah-theme' ); ?></option>
                <option value="relocating"><?php esc_html_e( 'Relocating', 'ah-theme' ); ?></option>
              </select>
            </div>

            <div class="form-group">
              <label for="cf-message"><?php esc_html_e( 'Your Message *', 'ah-theme' ); ?></label>
              <textarea id="cf-message" name="message" class="form-control" rows="5" required
                        placeholder="<?php esc_attr_e( 'Tell us what you\'re looking for — location, property type, timeline, any specific requirements…', 'ah-theme' ); ?>"></textarea>
            </div>

            <div id="ahContactStatus" class="form-status" role="alert" aria-live="polite"></div>

            <button type="submit" class="btn btn-primary btn-lg" id="ahContactSubmit">
              <?php esc_html_e( 'Send Message', 'ah-theme' ); ?>
            </button>
          </form>
        </div>

      </div>
    </div>
  </section>

</main>
<?php get_template_part( 'parts/footer' ); ?>
