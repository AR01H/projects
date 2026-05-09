<?php
// Footer — Advaith Homes
?>
<footer class="footer">
  <div class="container footer__inner">
    <div class="footer__brand">
      <span class="footer__logo"><?php echo esc_html( AH_SITE_NAME ); ?></span>
      <p><?php echo esc_html( get_customizer_val('ah_tagline', AH_TAGLINE) ); ?></p>
    </div>
    <div class="footer__links">
      <a href="#services">Services</a>
      <a href="#why-us">Why Us</a>
      <a href="#properties">Properties</a>
      <a href="#testimonials">Reviews</a>
      <a href="<?php echo esc_url( home_url('/contact') ); ?>">Contact</a>
    </div>
    <div class="footer__contact">
      <a href="tel:<?php echo esc_attr( get_customizer_val('ah_phone', AH_PHONE) ); ?>">
        <?php echo esc_html( get_customizer_val('ah_phone', AH_PHONE) ); ?>
      </a>
      <a href="mailto:<?php echo esc_attr( get_customizer_val('ah_email', AH_EMAIL) ); ?>">
        <?php echo esc_html( get_customizer_val('ah_email', AH_EMAIL) ); ?>
      </a>
      <span><?php echo esc_html( get_customizer_val('ah_address', AH_ADDRESS) ); ?></span>
    </div>
    <div class="footer__social">
      <a href="<?php echo esc_url( AH_INSTAGRAM ); ?>" target="_blank" rel="noopener">Instagram</a>
      <a href="<?php echo esc_url( AH_FACEBOOK ); ?>" target="_blank" rel="noopener">Facebook</a>
      <a href="<?php echo esc_url( AH_LINKEDIN ); ?>" target="_blank" rel="noopener">LinkedIn</a>
    </div>
    <p class="footer__copy">© <?php echo date('Y'); ?> <?php echo esc_html( AH_SITE_NAME ); ?>. All rights reserved.</p>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
