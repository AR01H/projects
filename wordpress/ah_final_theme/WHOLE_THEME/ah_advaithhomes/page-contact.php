<?php
/**
 * Template Name: Contact Page
 */
get_header();

$settings = ah_get_settings();
$phone    = $settings['phone']   ?? '';
$email    = $settings['email']   ?? '';
$address  = $settings['address'] ?? '';
$faqs     = ah_get_faqs( 'contact', 6 );
?>

<!-- ── Contact Hero ──────────────────────────────────────────────────────── -->
<section class="page-hero page-hero--sm" aria-label="Contact us">
  <div class="container">
    <div class="page-hero__copy" data-aos="fade-up">
      <span class="section__eyebrow">Get in Touch</span>
      <h1 class="page-hero__title">Talk to a<br><em>Buyer's Agent Today</em></h1>
      <p class="page-hero__desc">
        Book a free, no-obligation 30-minute consultation. We'll listen to your brief,
        explain how we work, and tell you honestly whether we're the right fit.
      </p>
    </div>
  </div>
</section>

<!-- ── Contact Layout ────────────────────────────────────────────────────── -->
<section class="section" aria-label="Contact form and details">
  <div class="container">
    <div class="contact-layout">

      <!-- Form panel -->
      <div class="contact-layout__form" data-aos="fade-right">
        <div class="form-card">
          <h2 class="form-card__title">Book a Free Consultation</h2>
          <p class="form-card__desc">
            Fill in the form and we'll confirm a time within 24 hours.
          </p>

          <form data-ah-form="consultation" class="ah-form" novalidate>
            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="cf-name">Full Name <span aria-hidden="true">*</span></label>
                <input id="cf-name" name="name" type="text" class="form-input" placeholder="Jane Smith" required>
              </div>
              <div class="form-group">
                <label class="form-label" for="cf-email">Email Address <span aria-hidden="true">*</span></label>
                <input id="cf-email" name="email" type="email" class="form-input" placeholder="jane@example.com" required>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label" for="cf-phone">Phone Number</label>
                <input id="cf-phone" name="phone" type="tel" class="form-input" placeholder="+44 7700 000000">
              </div>
              <div class="form-group">
                <label class="form-label" for="cf-budget">Budget Range</label>
                <select id="cf-budget" name="budget" class="form-input form-select">
                  <option value="">Select budget…</option>
                  <option>Under £250,000</option>
                  <option>£250,000 – £500,000</option>
                  <option>£500,000 – £750,000</option>
                  <option>£750,000 – £1M</option>
                  <option>£1M – £2M</option>
                  <option>Over £2M</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label" for="cf-location">Preferred Location(s)</label>
              <input id="cf-location" name="location" type="text" class="form-input" placeholder="e.g. North London, Bristol, Manchester">
            </div>

            <div class="form-group">
              <label class="form-label" for="cf-message">Tell Us About Your Search</label>
              <textarea id="cf-message" name="message" class="form-input form-textarea" rows="4"
                        placeholder="What are you looking for? How soon are you hoping to buy? Any must-haves?"></textarea>
            </div>

            <div class="form-group" style="display:flex;align-items:flex-start;gap:10px;font-size:.875rem">
              <input type="checkbox" id="cf-consent" name="consent" required style="margin-top:3px;flex-shrink:0">
              <label for="cf-consent" style="color:var(--text-secondary)">
                I agree to be contacted by Advaith Homes about my property search.
                We never share your data. See our <a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>" style="color:var(--accent)">privacy policy</a>.
              </label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
              Book My Free Consultation →
            </button>

            <div class="ah-form__status" aria-live="polite"></div>
          </form>
        </div>
      </div>

      <!-- Info panel -->
      <div class="contact-layout__info" data-aos="fade-left" data-delay="150">

        <!-- Contact details -->
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

        <!-- What to expect -->
        <div class="contact-info-block">
          <h3 class="contact-info-block__title">What Happens Next</h3>
          <ol class="contact-steps">
            <li class="contact-step">
              <span class="contact-step__num">1</span>
              <div>
                <strong>We'll confirm your slot</strong><br>
                <span style="font-size:.875rem;color:var(--text-secondary)">Usually within a few hours during business days.</span>
              </div>
            </li>
            <li class="contact-step">
              <span class="contact-step__num">2</span>
              <div>
                <strong>30-minute call</strong><br>
                <span style="font-size:.875rem;color:var(--text-secondary)">We listen, answer your questions, and explain how we work.</span>
              </div>
            </li>
            <li class="contact-step">
              <span class="contact-step__num">3</span>
              <div>
                <strong>No obligation</strong><br>
                <span style="font-size:.875rem;color:var(--text-secondary)">If we're not the right fit, we'll say so — and point you in the right direction.</span>
              </div>
            </li>
          </ol>
        </div>

        <!-- Alternative: General Enquiry form -->
        <div class="contact-info-block">
          <h3 class="contact-info-block__title">Send a Quick Message</h3>
          <form data-ah-form="contact" class="ah-form" novalidate>
            <div class="form-group">
              <input name="name" type="text" class="form-input" placeholder="Your name" required>
            </div>
            <div class="form-group">
              <input name="email" type="email" class="form-input" placeholder="Your email" required>
            </div>
            <div class="form-group">
              <textarea name="message" class="form-input form-textarea" rows="3" placeholder="Your message…" required></textarea>
            </div>
            <button type="submit" class="btn btn-outline btn-block">Send Message →</button>
            <div class="ah-form__status" aria-live="polite"></div>
          </form>
        </div>

      </div>
    </div>
  </div>
</section>

<!-- ── Contact FAQ ────────────────────────────────────────────────────────── -->
<?php if ( $faqs ) : ?>
<section class="section section--alt" aria-label="Contact FAQ">
  <div class="container container--md">
    <div class="section__header text-center">
      <span class="section__eyebrow">FAQ</span>
      <h2 class="section__title">Before You Call</h2>
    </div>
    <div>
      <?php foreach ( $faqs as $i => $faq ) : ?>
      <div class="faq" data-aos="fade-up" data-delay="<?php echo min( $i * 50, 300 ); ?>">
        <button class="faq__q" aria-expanded="false">
          <?php echo esc_html( $faq->question ); ?>
          <span class="faq__icon" aria-hidden="true">+</span>
        </button>
        <div class="faq__a" role="region">
          <div class="faq__a-inner"><?php echo wp_kses_post( $faq->answer ); ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center" style="margin-top:24px">
      <a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>" class="btn btn-ghost">View all FAQs →</a>
    </div>
  </div>
</section>
<?php endif; ?>

<?php get_footer(); ?>
