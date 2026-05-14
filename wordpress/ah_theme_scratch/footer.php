<?php
/**
 * The template for displaying the footer
 */

$company_name = 'Advaith ';
$company_name_part2 = 'Homes';
$company_desc = "The UK's dedicated buyer's agent — we work exclusively for you, not the seller. Saving you time, stress, and thousands of pounds on your most important purchase.";
$badge_text = "🇬🇧 Proudly serving UK home buyers";
$socials = ['📘', '🐦', '📸', '▶️', '💼'];

$buying_guides = [
    ['title' => 'Property Research Report', 'url' => home_url('/property-research')],
    ['title' => 'Legal Search Packs', 'url' => home_url('/legal-search')],
    ['title' => 'Buyer\'s Guide', 'url' => home_url('/buyers-guide')],
    ['title' => 'Deposit Guide', 'url' => home_url('/deposit-guide')],
    ['title' => 'Mortgage Guide', 'url' => home_url('/mortgage-guide')],
    ['title' => 'Moving Guide', 'url' => home_url('/moving-guide')],
    ['title' => 'Price Calculator', 'url' => home_url('/price-calculator'), 'highlight' => true],
    ['title' => 'Free Consultation Guide', 'url' => home_url('/free-consultation')],
];

$company_links = [
    ['title' => 'Home', 'url' => home_url('/')],
    ['title' => 'Services', 'url' => home_url('/services')],
    ['title' => 'About Us', 'url' => home_url('/about')],
    ['title' => 'Client Stories', 'url' => home_url('/previous-clients')],
    ['title' => 'Contact', 'url' => home_url('/contact')],
    ['title' => 'Privacy Policy', 'url' => home_url('/privacy-policy')],
    ['title' => 'Terms & Conditions', 'url' => home_url('/terms')],
    ['title' => 'Refund Policy', 'url' => home_url('/refund-policy')],
];

$contacts = [
    ['icon' => '📞', 'text' => '+44 774 722 3762', 'sub' => 'Mon–Sat, 9am–6pm'],
    ['icon' => '✉️', 'text' => 'contact@advaithhomes.co.uk', 'sub' => 'We reply within 2 hours'],
    ['icon' => '📍', 'text' => 'London & Nationwide', 'sub' => 'Covering all of England & Wales'],
];
?>
<footer class="footer">
  <div class="container">
    <div class="footer__grid">
      <div>
        <a href="<?php echo esc_url(home_url('/')); ?>" class="nav__logo" style="margin-bottom:0">
          <div class="nav__logo-mark">AH</div>
          <span style="color:white;font-size:1.4rem"><?php echo esc_html($company_name); ?> <em style="font-style:italic;font-family:var(--font-accent)"><?php echo esc_html($company_name_part2); ?></em></span>
        </a>
        <p class="footer__brand-desc">
          <?php echo esc_html($company_desc); ?>
        </p>
        <div class="footer__badge"><?php echo esc_html($badge_text); ?></div>
        <div class="footer__socials" style="margin-top:16px">
          <?php foreach ($socials as $icon): ?>
            <div class="footer__social"><?php echo $icon; ?></div>
          <?php endforeach; ?>
        </div>
      </div>

      <div>
        <div class="footer__col-title">Buying Guides</div>
        <div class="footer__links">
          <?php foreach ($buying_guides as $link): ?>
            <a href="<?php echo esc_url($link['url']); ?>" class="footer__link" <?php echo !empty($link['highlight']) ? 'style="color: var(--client-color-100); font-weight: 700;"' : ''; ?>><?php echo esc_html($link['title']); ?></a>
          <?php endforeach; ?>
        </div>
      </div>

      <div>
        <div class="footer__col-title">Company</div>
        <div class="footer__links">
          <?php foreach ($company_links as $link): ?>
            <a href="<?php echo esc_url($link['url']); ?>" class="footer__link"><?php echo esc_html($link['title']); ?></a>
          <?php endforeach; ?>
        </div>
      </div>

      <div>
        <div class="footer__col-title">Get In Touch</div>
        <?php foreach ($contacts as $contact): ?>
          <div class="footer__contact-item">
            <span class="footer__contact-icon"><?php echo $contact['icon']; ?></span>
            <div>
              <div style="color:white;font-weight:600"><?php echo esc_html($contact['text']); ?></div>
              <div style="font-size:.78rem;margin-top:2px"><?php echo esc_html($contact['sub']); ?></div>
            </div>
          </div>
        <?php endforeach; ?>
        <div style="margin-top:20px">
          <a href="<?php echo esc_url(home_url('/free-consultation')); ?>" class="btn btn-gold btn-sm" style="width:100%;justify-content:center">
            Book Free Consultation →
          </a>
        </div>
      </div>
    </div>

    <div class="footer__bottom">
      <div style="font-size:.8rem">© <?php echo date('Y'); ?> Advaith Homes. All rights reserved.</div>
    </div>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
