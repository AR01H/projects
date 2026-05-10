<?php
global $post;
$pid   = isset($post->ID) ? $post->ID : 0;
$copy  = ch_meta($pid, '_footer_copy', '© 2025 The Cane House. Pressed Fresh. Served Cool.');
$ig    = ch_meta($pid, '_footer_ig',   '#');
$fb    = ch_meta($pid, '_footer_fb',   '#');
$tt    = ch_meta($pid, '_footer_tt',   '#');
$yt    = ch_meta($pid, '_footer_yt',   '#');
$wa    = ch_meta($pid, '_contact_whatsapp', '447887699208');
?>

<!-- FOOTER -->
<footer>
  <div class="footer-grid">
    <div class="footer-brand">
      <div class="f-logo">The Cane <span>House</span> 🌿</div>
      <p>Fresh sugarcane juice pressed live, served cool. No added sugar, no preservatives — just pure natural refreshment wherever you are.</p>
      <div class="footer-social">
        <a class="f-social-btn" href="<?php echo esc_url($ig); ?>" target="_blank">📸</a>
        <a class="f-social-btn" href="<?php echo esc_url($fb); ?>" target="_blank">📘</a>
        <a class="f-social-btn" href="<?php echo esc_url($tt); ?>" target="_blank">🎵</a>
        <a class="f-social-btn" href="<?php echo esc_url($yt); ?>" target="_blank">▶️</a>
      </div>
    </div>
    <div class="footer-col">
      <h4>Our Juice</h4>
      <ul>
        <li><a href="#build">Build Your Juice</a></li>
        <li><a href="#build">Sizes &amp; Pricing</a></li>
        <li><a href="#build">Cane Types</a></li>
        <li><a href="#build">Flavour Blends</a></li>
        <li><a href="#benefits">Health Benefits</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Services</h4>
      <ul>
        <li><a href="#hire">Event Hire</a></li>
        <li><a href="#hire">Weddings</a></li>
        <li><a href="#hire">Parties &amp; Gatherings</a></li>
        <li><a href="#franchise">Franchise</a></li>
        <li><a href="#contact">Contact Us</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom fade-up" style="display:flex;justify-content:center;">
    <span><?php echo esc_html($copy); ?></span>
  </div>
</footer>

<!-- WHATSAPP FLOAT -->
<a href="https://wa.me/<?php echo esc_attr($wa); ?>" class="whatsapp-float" target="_blank">
  <div class="wa-tooltip">Chat with us! 🌿</div>
  <svg viewBox="0 0 32 32">
    <path fill="currentColor" d="M16 2a13.79 13.79 0 0 0-11.85 20.8L2 30l7.45-1.95A13.73 13.73 0 0 0 16 30c7.61 0 13.8-6.19 13.8-13.8A13.84 13.84 0 0 0 16 2zm6.65 19.38c-.3.84-1.49 1.54-2.45 1.76-.66.15-1.53.27-4.43-.93-3.71-1.54-6.1-5.32-6.29-5.57s-1.52-2.02-1.52-3.85 1-2.73 1.35-3.1.7-.46.93-.46.45 0 .64.01c.19 0 .45-.07.7.53.26.63.89 2.16.96 2.31s.12.33.02.53-.15.3-.3.47-.3.34-.45.51-.33.36-.14.68c.19.32.83 1.36 1.78 2.21 1.23 1.09 2.26 1.43 2.58 1.59s.53.13.73-.09.85-.99 1.08-1.33.45-.28.76-.17 1.95.92 2.28 1.08.55.25.63.38.12.78-.18 1.62z"/>
  </svg>
</a>

<?php wp_footer(); ?>
</body>
</html>
