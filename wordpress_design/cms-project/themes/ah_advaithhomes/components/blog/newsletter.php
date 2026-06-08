<?php
defined( 'ABSPATH' ) || exit;
?>
<section class="section section--pattern" aria-label="<?php echo esc_attr( TXT_NEWSLETTER ); ?>">
  <div class="container container--sm">
    <div class="newsletter-block text-center" data-aos="fade-up">
      <span class="section__eyebrow">Stay Informed</span>
      <h2 class="section__title" style="font-size:1.75rem;margin-bottom:12px">Do you need more information?</h2>
      <p style="color:var(--text-secondary);margin-bottom:28px">
        Market updates, new guides, and buyer tips - once a week. No spam, ever.
      </p>
      <button class="btn btn-primary">
        <a href="<?php echo esc_url( home_url( AH_LINK_CONTACT ) ); ?>"><?php echo esc_html( AH_LABEL_CONTACT_US ); ?></a>
      </button>
    </div>
  </div>
</section>
