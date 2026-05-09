<?php
// Footer — The Cane House
?>
<footer id="footer">
  <div class="footer-inner">
    <div class="footer-brand">
      <span class="footer-logo">THE CANE <span>HOUSE</span></span>
      <p><?php echo esc_html( get_customizer_val('tch_tagline', TCH_TAGLINE) ); ?></p>
    </div>
    <div class="footer-links">
      <a href="#how-to-order">How to Order</a>
      <a href="#reviews">Reviews</a>
      <a href="#build">Our Juices</a>
      <a href="#hire">Events</a>
      <a href="#franchise">Franchise</a>
      <a href="#contact">Contact</a>
    </div>
    <div class="footer-contact">
      <a href="tel:<?php echo esc_attr( get_customizer_val('tch_phone', TCH_PHONE) ); ?>">
        <?php echo esc_html( get_customizer_val('tch_phone', TCH_PHONE) ); ?>
      </a>
      <a href="mailto:<?php echo esc_attr( get_customizer_val('tch_email', TCH_EMAIL) ); ?>">
        <?php echo esc_html( get_customizer_val('tch_email', TCH_EMAIL) ); ?>
      </a>
    </div>
    <div class="footer-social">
      <a href="<?php echo esc_url( TCH_INSTAGRAM ); ?>" target="_blank" rel="noopener" aria-label="Instagram">Instagram</a>
      <a href="<?php echo esc_url( TCH_FACEBOOK ); ?>" target="_blank" rel="noopener" aria-label="Facebook">Facebook</a>
      <a href="<?php echo esc_url( TCH_TIKTOK ); ?>" target="_blank" rel="noopener" aria-label="TikTok">TikTok</a>
    </div>
    <p class="footer-copy">© <?php echo date('Y'); ?> <?php echo esc_html( TCH_SITE_NAME ); ?>. All rights reserved.</p>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
